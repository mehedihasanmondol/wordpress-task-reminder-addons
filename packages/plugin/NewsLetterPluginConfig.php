<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 10:50 AM
 */

class NewsLetterPluginConfig
{
    public $plugin_name = "Random product newsletter";
    public $plugin_short_name = "Newsletter";
    public $post_type = "product-newsletter";
    public $setup_menu_slug = "newsletter_settings";
    public $label = "Product newsletter";
    public $setup_page_title = "Random product newsletter settings";
    public $setup_menu_title = "Newsletter";

    public $unsubscriber_page_title = "Unsubscribe";
    public $unsubscriber_menu_title = "Unsubscriber";
    public $unsubscriber_menu_menu_slug = "unsubscribe";
    public $unsubscribe_message = "Successfully UnSubscribed";

    public $send_grid_api_key = "";

    public $from_email = "connection.mahadihasan@gmail.com";
    public $from_email_name = "Developer Mehedi hasan";

    public $send_grid_api_option = "send_grid_api_key";
    public $send_grid_api_from_email_option = "send_grid_from_email";
    public $send_grid_api_from_email_name_option = "send_grid_from_email_name";
    public $send_grid_api_message_option = "send_grid_api_message";

    public $unsubscriber_table_name = "unsubscribers";


    public $server_cron_commands = "";

    public $post_meta_keys = array(
        "api_key" => "newsletter_api_key",
        "subject" => "newsletter_subject",
        "body" => "newsletter_body_content",
        "user_categories" => "newsletter_user_categories",
        "sending_frequency" => "newsletter_sending_frequency",
        "week_day" => "week_day",
        "month_date" => "month_date",
        "hour" => "hour",
        "cron" => 'cron',
        "cron_time" => 'cron_time',
        "cron_status" => 'cron_status',
        "test_mode" => 'test_mode',
        "test_email_1" => 'test_email_1',
        "test_email_2" => 'test_email_2',
        "from_email" => 'from_email',
        "from_email_name" => 'from_email_name',
        "cron_running" => 'cron_running',
    );

    public $post_meta_api_key;
    public $post_meta_body;
    public $post_meta_subject;
    public $post_meta_user_categories;
    public $post_meta_sending_frequency;
    public $post_meta_week_day;
    public $post_meta_month_date;
    public $post_meta_hour;
    public $post_meta_cron;
    public $post_meta_cron_time;
    public $post_meta_cron_status;
    public $post_meta_test_mode;
    public $post_meta_test_email_1;
    public $post_meta_test_email_2;
    public $post_meta_from_email;
    public $post_meta_from_email_name;
    public $post_meta_cron_running;
    public function __construct()
    {

        $this->server_cron_commands = "wget -q -O - ".home_url()."/wp-cron.php?doing_wp_cron >/dev/null 2>&1";
        $key = get_option($this->send_grid_api_option);
        $from_email = get_option($this->send_grid_api_from_email_option);
        $from_email_name = get_option($this->send_grid_api_from_email_name_option);

        $this->send_grid_api_key =  $key ? $key : $this->send_grid_api_key;
        $this->from_email =  $from_email ? $from_email : $this->from_email;
        $this->from_email_name =  $from_email_name ? $from_email_name : $this->from_email_name;




        $this->post_meta_api_key = $this->post_meta_keys['api_key'];
        $this->post_meta_subject = $this->post_meta_keys['subject'];
        $this->post_meta_body = $this->post_meta_keys['body'];
        $this->post_meta_user_categories = $this->post_meta_keys['user_categories'];
        $this->post_meta_sending_frequency = $this->post_meta_keys['sending_frequency'];
        $this->post_meta_week_day = $this->post_meta_keys['week_day'];
        $this->post_meta_month_date = $this->post_meta_keys['month_date'];
        $this->post_meta_hour = $this->post_meta_keys['hour'];
        $this->post_meta_cron = $this->post_meta_keys['cron'];
        $this->post_meta_cron_time = $this->post_meta_keys['cron_time'];
        $this->post_meta_cron_status = $this->post_meta_keys['cron_status'];
        $this->post_meta_test_mode = $this->post_meta_keys['test_mode'];
        $this->post_meta_test_email_1 = $this->post_meta_keys['test_email_1'];
        $this->post_meta_test_email_2 = $this->post_meta_keys['test_email_2'];
        $this->post_meta_from_email = $this->post_meta_keys['from_email'];
        $this->post_meta_from_email_name = $this->post_meta_keys['from_email_name'];
        $this->post_meta_cron_running = $this->post_meta_keys['cron_running'];
    }

}
