<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 11:29 AM
 */

class TaskReminderPluginAssistant
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
        $html_form = file_get_contents(TASK_REMINDER_PLUGIN_DIR."/assets/html/save-message.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($html_form,$data);
    }

    public function template_render($template, $data=array(
        "message" => ""
    )){
       $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));
        return $template_maker->render($template,$data);
    }

    function get_post_data($post_id){
        $data = array(
            "post_title" => "",
            "post_name" => "",
            "time" => "",
            "task_date" => "",
            "task_detail" => "",
        );

        $get_post = get_post($post_id);
        if ($get_post){
            $data['post_title'] = $get_post->post_title;
            $data['post_name'] = $get_post->post_name;
        }


        foreach ((new TaskReminderAddOnsPluginConfig())->post_meta_keys as $key){
            $value = get_post_meta($post_id,$key,true);
            $data[$key] = $value ? $value : "";

            if ($key == 'task_date'){
                $data[$key] = $data[$key] ? date('Y-m-d', strtotime($data[$key])) : "";
            }
            
        }


        return $data;
    }

    
    function get_post_submission_status_array($post_id){
        $data = array(
           
        );
        $staff_array = get_post_meta($post_id,'staff', true);
        $before_submission_status_array = get_post_meta($post_id,'staff', true);
        foreach($staff_array as $index => $staff_id){
            $status = 0;
            if($before_submission_status_array){
                $status = $before_submission_status_array[$index];
            }
            $data[] = $status;

        }
        
        return $data;
    }

    
    function set_submission_status($post_id,$param_staff_id,$param_status = 1){
        $data = array(
           
        );
        $staff_array = get_post_meta($post_id,'staff', true);
        $before_submission_status_array = get_post_meta($post_id,'staff_work_status', true);
        foreach($staff_array as $index => $staff_id){
            $status = 0;
            if($before_submission_status_array){
                $status = $before_submission_status_array[$index] ?? 0;
            }

            if($staff_id == $param_staff_id){
                $status = $param_status;
            }

            $data[] = $status;

        }

        return update_post_meta($post_id, 'staff_work_status', $data);
        
    }
    

    




}