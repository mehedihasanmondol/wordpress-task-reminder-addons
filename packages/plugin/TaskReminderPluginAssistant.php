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


}