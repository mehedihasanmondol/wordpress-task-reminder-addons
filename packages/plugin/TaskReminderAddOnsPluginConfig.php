<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 10:50 AM
 */

class TaskReminderAddOnsPluginConfig
{
    public $setup_menu_slug = "task_reminder_addons_settings";
    public $setup_page_title = "Task reminder settings";
    public $setup_menu_title = "Task reminder";

    public $subject_of_admin = "subject of admin";
    public $message_of_admin = "message of admin";
    public $subject_of_subscriber = "subject of subscriber";
    public $message_of_subscriber = "message of subscriber";
    public $setup_hint = "You can add list of variables: {{post_title}},{{post_name}},{{task_date}},{{time}},{{task_detail}}";


    public $subject_of_admin_key = "subject_of_admin";
    public $message_of_admin_key = "message_of_admin";
    public $subject_of_subscriber_key = "subject_of_subscriber";
    public $message_of_subscriber_key = "message_of_subscriber";


    public $post_meta_keys = array(
        "task_date","time","task_detail"
    );


    public function __construct()
    {

        $this->subject_of_admin = get_option($this->subject_of_admin_key,$this->subject_of_admin);
        $this->message_of_admin = get_option($this->message_of_admin_key,$this->message_of_admin);
        $this->subject_of_subscriber = get_option($this->subject_of_subscriber_key,$this->subject_of_subscriber);
        $this->message_of_subscriber = get_option($this->message_of_subscriber_key,$this->message_of_subscriber);
   
    }

}
