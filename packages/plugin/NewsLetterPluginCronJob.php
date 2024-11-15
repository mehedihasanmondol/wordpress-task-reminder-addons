<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/26/2023
 * Time: 5:43 AM
 */

class NewsLetterPluginCronJob
{
    function set_cron_job($post_id){

        set_time_limit(0);
        $config = new NewsLetterPluginConfig();
        $assistant = new NewsLetterPluginAssistant();
        $unsubscriber = new NewsLetterUnSubscriber();
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));

        $assistant->update_post_meta($post_id,$config->post_meta_cron_running,1);


        $post_data = $assistant->get_post_data($post_id);
        $user_roles = explode(",",$post_data[(new NewsLetterPluginConfig())->post_meta_user_categories]);

        if ($post_data[$config->post_meta_test_mode] == 'test'){
            $user_roles = array('cron_test');

        }

        foreach($user_roles as $role){
            $users = array();
            if ($role == 'cron_test'){
                $users = array();
                foreach (array($post_data[$config->post_meta_test_email_1],$post_data[$config->post_meta_test_email_2]) as $email){
                    $test_user = new stdClass();
                    $test_user->id = 0;
                    $test_user->display_name = "Test user 1";
                    $test_user->user_email = $email;
                    $users[] = $test_user;
                }
            }
            else{
                $users = $assistant->get_users_by_roll($role);
            }

            foreach ($users as $user){

                if (!$unsubscriber->get_unsubscription($post_id,$user->id)){
                    $message = $post_data[$config->post_meta_body];
                    if ($user->user_email){
                        try {

                            $message_params = array(
                                "user_name" => $user->display_name,
                                "item_name" => "",
                                "item_image" => "",
//                            "item_link" => "",
                            );

                            $args     = array( 'post_type' => 'product', 'posts_per_page' => 1 ,'orderby' => 'rand');
                            $products = get_posts( $args );

                            if ($products){
                                foreach ($products as $product){
//                                $message_params['item_name'] = $product->post_title;
                                    $product_link = home_url()."/product/".$product->post_name."/?date=".wp_date("Y-m-d")."&email=".$post_data['post_title'];
                                    $message_params['item_name'] = "<a href='".$product_link."'>".$product->post_title."</a>";
                                    $message_params['item_image'] = "<a href='".$product_link."'>".$assistant->get_post_image($product->ID)."</a>";
                                }
                            }

                            $message .= "<hr/>";
                            $message .= $assistant->get_unsubscribe_button_html(array(
                                "url" => home_url()."/product-newsletter/".$post_data['post_name']."/?roll=".$role."&post_id=".$post_id."&user_id=".$user->id
                            ));


                            $email = new \SendGrid\Mail\Mail();
                            // $email->setFrom($config->from_email, $config->from_email_name);
                            
                            $email->setFrom($post_data[$config->post_meta_from_email], $post_data[$config->post_meta_from_email_name]);
                            $email->setSubject($post_data[$config->post_meta_subject]);
                            $email->addTo($user->user_email, $user->display_name);
                            $email->addContent(
                                "text/html", $template_maker->render($message,$message_params)
                            );
                            $sendgrid = new \SendGrid($post_data[$config->post_meta_api_key]);
                            $response = $sendgrid->send($email);

                            if ($response->statusCode() != 200 and $response->statusCode() != 202){
                                $error_message = "";
                                foreach (json_decode($response->body(),true)['errors'] as $error){
                                    $error_message .= $error['message'];
                                }

                                update_option($config->send_grid_api_message_option,$error_message." at ".$assistant->text_date_time(),'no');

                            }
                            else{
                                update_option($config->send_grid_api_message_option, "Mail send in ".$user->user_email." at ". $assistant->text_date_time(),'no');

                            }

                        } catch (Exception $e) {
                            update_option($config->send_grid_api_message_option,$e->getMessage()." at ".$assistant->text_date_time(),'no');
                        }
                    }
                }


            }
        }

        if($post_data[$config->post_meta_sending_frequency] == "one_time"){
            $assistant->update_post_meta($post_id,"cron",1);
            $hook_name = "one_time_newsletter_cron_job_of_".$post_id;
            wp_clear_scheduled_hook($hook_name,array($post_id));
        }
        $cron_time = $assistant->current_time_stamp();

        if($post_data[$config->post_meta_sending_frequency] == "monthly"){
            $month_time = $assistant->text_date_time("Y-m-",$post_data[$config->post_meta_cron_time])."01 ".wp_date("H:i:s");
            $cron_time = date("Y-m-d H:i:s",strtotime('+1 months',strtotime($month_time)));
        }

        $assistant->update_post_meta($post_id,$config->post_meta_cron_time,$cron_time);
        $assistant->update_post_meta($post_id,$config->post_meta_cron_running,0);

    }

    function register_cron_jobs(){
        $config = new NewsLetterPluginConfig();
        $posts = get_posts(array(
            "post_type" => $config->post_type,
            "numberposts" => -1,
            "meta_key" => 'cron',
            "meta_value" => 0,
        ));



        $assistant = new NewsLetterPluginAssistant();

        foreach ($posts as $post_object){

            $post = $assistant->get_post_data($post_object->ID);
            $post_id = $post_object->ID;

            if ($post[$config->post_meta_sending_frequency] == "one_time" and !$post[$config->post_meta_cron] and !$post[$config->post_meta_cron_running]){
                $hook_name = "one_time_newsletter_cron_job_of_".$post_id;
                $args = array($post_id);
                add_action($hook_name,[$this,"set_cron_job"],10,$args);
                if ( ! wp_next_scheduled( $hook_name ,$args) ) {
                    wp_schedule_single_event(time(), $hook_name,$args);
                }
            }
            else if ($post[$config->post_meta_sending_frequency] == "hourly" and !$post[$config->post_meta_cron] and !$post[$config->post_meta_cron_running]){
                $hook_name = "hourly_newsletter_cron_job_of_".$post_id;
                $args = array($post_id);
                add_action($hook_name,[$this,"set_cron_job"],10,$args);

                $utc_cron_time = $assistant->time_convert_by_zone($post[$config->post_meta_cron_time],"UTC",wp_timezone()->getName());
                $time = strtotime('+'.$post[$config->post_meta_hour]." hour",strtotime($utc_cron_time));

                if (!wp_next_scheduled($hook_name, $args)) {
                    wp_schedule_single_event($time, $hook_name, $args);

                }
            }
            else if ($post[$config->post_meta_sending_frequency] == "weekly" and !$post[$config->post_meta_cron] and !$post[$config->post_meta_cron_running]){
                $hook_name = "weekly_newsletter_cron_job_of_".$post_id;
                $args = array($post_id);
                add_action($hook_name,[$this,"set_cron_job"],10,$args);

                $utc_cron_time = $assistant->time_convert_by_zone($post[$config->post_meta_cron_time],"UTC",wp_timezone()->getName());
                $time = strtotime('next '.$assistant->days[$post[$config->post_meta_week_day]].' 01:00:00',strtotime($utc_cron_time));

                if (date("Y-m-d",$time) >= date("Y-m-d")) {
                    if (!wp_next_scheduled($hook_name, $args)) {
                        wp_schedule_single_event($time, $hook_name, $args);
                    }
                }
                else{
                    $cron_time = $assistant->current_time_stamp();
                    $assistant->update_post_meta($post_id,$config->post_meta_cron_time,$cron_time);

                }
            }

            else if ($post[$config->post_meta_sending_frequency] == "monthly" and !$post[$config->post_meta_cron] and !$post[$config->post_meta_cron_running]){
                $hook_name = "monthly_newsletter_cron_job_of_".$post_id;
                $args = array($post_id);
                add_action($hook_name,[$this,"set_cron_job"],10,$args);

                $utc_cron_time = $assistant->time_convert_by_zone($post[$config->post_meta_cron_time],"UTC",wp_timezone()->getName());
                $month_date = $assistant->text_date_time("Y-m-",$utc_cron_time).$post[$config->post_meta_month_date];
                $time = strtotime($month_date." 01:00:00");

                if ($month_date >= date("Y-m-d")){
                    if ( ! wp_next_scheduled( $hook_name ,$args) ) {
                        wp_schedule_single_event($time, $hook_name,$args);
                    }
                }
                else{
                    $cron_time = date("Y-m-d H:i:s",strtotime('+1 months',strtotime(date("Y-m-01 H:i:s"))));
                    $assistant->update_post_meta($post_id,$config->post_meta_cron_time,$cron_time);

                }


            }


        }
    }
    function un_register_cron_jobs(){
        $config = new NewsLetterPluginConfig();
        $posts = get_posts(array(
            "post_type" => $config->post_type,
            "numberposts" => -1,
            "meta_key" => 'cron',
            "meta_value" => 0,
        ));


        foreach ($posts as $post_object){
            $post_id = $post_object->ID;

            $freequency_type = get_post_meta($post_id,$config->post_meta_sending_frequency,true);

            if ($freequency_type){
                $hook_name = $freequency_type."_newsletter_cron_job_of_".$post_id;
                wp_clear_scheduled_hook($hook_name,array($post_id));
            }


        }
    }


    function add_schedule_intervals( $schedules ) {
        // add a 'everyminute' schedule to the existing set
        $schedules['every15seconds'] = array(
            'interval' => 15,
            'display'  => __( 'Every 15 seconds'),
        );
        $schedules['monthly'] = array(
            'interval' => 2635200,
            'display'  => __( 'Every month'),
        );

        return $schedules;
    }
}
