<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 11:29 AM
 */

class NewsLetterPluginAssistant
{
    public $days = [
        "Sunday",
        "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"
    ];

    function current_time_stamp(){
        return wp_date('Y-m-d H:i:s');
    }

    function time_convert_by_zone($time, $tz = 'UTC',$otz="")
    {
        // create a $dt object with the default timezone
        $dt = new DateTime($time, new DateTimeZone($otz ? $otz : date_default_timezone_get()));

        // change the timezone of the object without changing it's time
        $dt->setTimezone(new DateTimeZone($tz));

        // format the datetime
        return $dt->format('Y-m-d H:i:s');
    }

    function text_date_time($format="",$date_time=""){
        if (!$date_time){
            $date_time = wp_date("Y-m-d H:i:s");
        }
        if (!$format){
            $format = "j M, Y g:i A";
        }
        return date($format, strtotime($date_time));
    }
    public function message_html_generate($data=array(
        "message" => ""
    )){
        $html_form = file_get_contents(NEWS_LETTER_PLUGIN_DIR."/assets/html/save-message.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($html_form,$data);
    }

    public function frequency_html_generate($data=array(
        "days_selectize" => "",
        "week_selectize" => "",
        "date_selectize" => "",
    )){
        $html_form = file_get_contents(NEWS_LETTER_PLUGIN_DIR."/assets/html/frequency.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($html_form,$data);
    }

    public function test_mail_checkbox_html_generate($data=array(
//        "test_mode_checked" => "",
//        "line_mode_checked" => "",
    )){
        $html_form = file_get_contents(NEWS_LETTER_PLUGIN_DIR."/assets/html/test-mode.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($html_form,$data);
    }


    function save_post_meta($post_id){
        $config_instance = new NewsLetterPluginConfig();
        if (isset($_POST[$config_instance->post_meta_api_key])){
            $cron_time_meta = get_post_meta($post_id,$config_instance->post_meta_cron_time,true);
            if (!$cron_time_meta){
                $this->update_post_meta($post_id,$config_instance->post_meta_cron_time,$this->current_time_stamp());
                $this->update_post_meta($post_id,$config_instance->post_meta_cron_running,0);
            }

            $this->update_post_meta($post_id,$config_instance->post_meta_cron,0);

        }

        foreach ($config_instance->post_meta_keys as $index => $key){
            if (isset($_POST[$key])){
                $value = $_POST[$key];
                if ($key == $config_instance->post_meta_user_categories){
                    $value = join(",",$value);
                }

                $this->update_post_meta($post_id,$key,$value);

            }
        }

//        if (isset($_POST[$config_instance->post_meta_api_key])){
//            $test_mode = get_post_meta($post_id,$config_instance->post_meta_test_mode,true);
//            if ($test_mode == "test"){
//                $this->send_test_mail($post_id,get_post_meta($post_id,$config_instance->post_meta_test_email_1,true));
//                $this->send_test_mail($post_id,get_post_meta($post_id,$config_instance->post_meta_test_email_2,true));
//            }
//        }
    }

    function send_test_mail($post_id,$email_address){
        if ($email_address){
            $config = new NewsLetterPluginConfig();

            $post_data = $this->get_post_data($post_id);

            $message = $post_data[$config->post_meta_body];

            $message_params = array(
                "user_name" => "Test Email User",
                "item_name" => ""
            );

            $args     = array( 'post_type' => 'product', 'posts_per_page' => 1 ,'orderby' => 'rand');
            $products = get_posts( $args );

            if ($products){
                foreach ($products as $product){
                    $product_link = home_url()."/product/".$product->post_name."/?date=".wp_date("Y-m-d")."&email=".$post_data['post_title'];
                    $message_params['item_name'] = "<a href='".$product_link."'>".$product->post_title."</a>";
                }
            }
            $template_maker = new Mustache_Engine(array(
                'escape' => function($value) {
                    return $value;
                }
            ));

            $config = new NewsLetterPluginConfig();

            $message .= "<hr/>";
            $message .= $this->get_unsubscribe_button_html(array(
                "url" => home_url()."/product-newsletter/".$post_data['post_name']."/?roll=customer"."&post_id=".$post_id."&user_id=0"
            ));

            try{
                $email = new \SendGrid\Mail\Mail();
                $email->setFrom($config->from_email, $config->from_email_name);
                $email->setSubject($post_data[$config->post_meta_subject]);
                $email->addTo($email_address, $message_params['user_name']);
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

                    update_option($config->send_grid_api_message_option,$error_message." at ".$this->text_date_time(),'no');

                }
                else{
                    update_option($config->send_grid_api_message_option, "Mail send in ".$email_address." at ". $this->text_date_time(),'no');

                }
                
            }catch (Exception $exception){
                update_option($config->send_grid_api_message_option,$exception->getMessage()." at ".$this->text_date_time(),'no');
            }


        }



    }

    function update_post_meta($post_id,$key,$value){
        update_post_meta($post_id, $key, $value);
    }
    function get_post_data($post_id){
        $config = new NewsLetterPluginConfig();
        $meta_keys = $config->post_meta_keys;
        $data = array(
            "post_date" => "",
            "post_title" => "",
            "post_name" => "",
        );

        $get_post = get_post($post_id);
        if ($get_post){
            $data['post_date'] = $get_post->post_date;
            $data['post_title'] = $get_post->post_title;
            $data['post_name'] = $get_post->post_name;
        }


        foreach ($meta_keys as $index => $key){
            $value = get_post_meta($post_id,$key,true);
            $data[$key] = $value ? $value : "";

            if ($key == $config->post_meta_api_key){
                $data[$key] = $data[$key] ? $data[$key] : $config->send_grid_api_key;
            }
            elseif ($key == $config->post_meta_from_email){
                $data[$key] = $data[$key] ? $data[$key] : $config->from_email;
            }
            elseif ($key == $config->post_meta_from_email_name){
                $data[$key] = $data[$key] ? $data[$key] : $config->from_email_name;
            }


            elseif ($key == $config->post_meta_week_day){
                $data[$key] = $data[$key] ? $data[$key] : 0;
            }
            elseif ($key == $config->post_meta_cron){
                $data[$key] = $data[$key] ? $data[$key] : 0;
            }
            elseif ($key == $config->post_meta_hour){
                $data[$key] = $data[$key] ? $data[$key] : 0;
            }
            elseif ($key == $config->post_meta_test_mode){
                $data[$key] = $data[$key] ? $data[$key] : 'test';
            }




        }


        return $data;
    }

    function get_users_by_roll($role){
        $args = array(
            'role'      => $role, // Retrieve users with the 'customer' role
            'number'    => -1,         // Retrieve all customers (-1)
            'fields'    => array(
                "display_name",
                "user_email",
                "id",
            ),
        );

        if ($role == "customer"){
            global $wpdb;
            $table_name = $wpdb->prefix . 'wc_customer_lookup'; // Replace with your custom table name

            $query = "SELECT first_name,last_name,email,id FROM $table_name";
            $results = $wpdb->get_results($query);

            foreach ($results as $index => $result){
                $result->display_name = $result->first_name." ".$result->last_name;
                $result->user_email = $result->email;
            }
            return $results;
        }

        return get_users( $args );
    }

    function get_post_image($post_id){
        return "<img src='". get_the_post_thumbnail_url($post_id, 'post-thumbnail')."'/>";
    }
    function get_unsubscribe_button_html($data=array()){
        $html_form = file_get_contents(NEWS_LETTER_PLUGIN_DIR."/assets/html/unsubscribe-button.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($html_form,$data);

    }

    function get_default_email_html($data=array()){
        return file_get_contents(NEWS_LETTER_PLUGIN_DIR."/assets/html/default-email-template.html");


    }


}