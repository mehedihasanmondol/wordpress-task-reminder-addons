<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 10:35 AM
 */

class Init
{
    public function __construct() {
        add_filter( 'acf/load_value/name=task_detail', [ $this, 'add_button_after_acf_field' ], get_the_ID(), 3 );
        add_action( 'wp_ajax_submit_work_action', [$this,'handle_submit_work'] );
    }

    function add_button_after_acf_field( $value, $post_id, $field ) {

        $this->load_newsletter_script();
        return $value;
    }
    
    function load_newsletter_script(){
        
    
        wp_enqueue_script( 'task-reminder-addons-plugin-script',TASK_REMINDER_PLUGIN_DIR_URL."assets/task-reminder-addons-plugin.js",['jquery']);
        // Localize data to pass PHP variables to JavaScript
        wp_localize_script( 'task-reminder-addons-plugin-script', 'submitWorkAjax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'post_id'  => get_the_ID() // Get the post ID to use in JavaScript
        ));
    }
    
    function handle_submit_work() {
        // Check if post_id is set and do something with it
        if ( isset( $_POST['post_id'] ) ) {
            // Perform your actions here (e.g., update post, add metadata)
    
            $is_error = 0;
    
            if(!$this->send_email_to_admin()){
                // Return a response
                $is_error = 1;
            }
        
            if(!$this->send_email_to_subscriber()){
                // Return a response
                $is_error = 1;
            }
            if($is_error){
                wp_send_json_error( 0 );
            }
            else{
                wp_send_json_success( 1);
            }
        } else {
            wp_send_json_error( 'Invalid post ID.' );
        }
    
        wp_die();
    }
    
    
    function send_custom_email($to,$subject,$message) {
    
        // Headers to send HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');
    
        // Use wp_mail() to send the email
        $sent = wp_mail( $to, $subject, $message, $headers );
    
        if ( $sent ) {
            return 1;
        } else {
            return 0;
        }
    }
    
    function send_email_to_admin() {
        // Get the admin email
        $admin_email = get_option( 'admin_email' );
    
        // Email subject and message
        $subject = 'Notification from Your Website';
        $message = 'This is a test email sent to the admin.';
    
    
        // Send the email
        return $this->send_custom_email( $admin_email, $subject, $message );
    }
    
    function send_email_to_subscriber() {
        // Get the subscriber emails
       $is_error = 0;
       $post_id = intval( $_POST['post_id'] );
        $meta_values = get_post_meta( $post_id, 'staff', true );
        foreach ($meta_values as $user_id){
            // Get user data
            $user_info = get_userdata( $user_id );
            if($user_info){
                $user_email = $user_info->user_email;
                // Email subject and message
                $subject = 'Notification from Your Website';
                $message = 'This is a test email sent to the subscriber.';
    
                // Send the email
                if(!$this->send_custom_email( $user_email, $subject, $message )){
                    $is_error = 1;
                }
            }
        }
        
    
        return $is_error ? 0 : 1;
    
        
    }
    


}