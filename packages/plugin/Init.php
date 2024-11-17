<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 10:35 AM
 */

class Init extends TaskReminderAddOnsPluginConfig
{
    public function __construct() {
        parent::__construct();
        add_action("admin_menu",[$this,"add_setup_menu"]);
        add_filter( 'acf/load_value/name=task_detail', [ $this, 'add_button_after_acf_field' ], get_the_ID(), 3 );
        add_action( 'wp_ajax_submit_work_action', [$this,'handle_submit_work'] );
        // add_action('transition_post_status', [$this, 'execute_after_task_publish'], 10, 3);

        add_action('acf/save_post', [$this,'my_acf_save_post']);

    }

    function my_acf_save_post( $post_id ) {

        ///crate work submission status
        $assistant = new TaskReminderPluginAssistant();
        $assistant->set_submission_status($post_id,"noOne",0);

        $this->send_email_to_subscriber($post_id);
        
    }


    // public function execute_after_task_publish($new_status, $old_status, $post) {
    //     // Check if the post type is 'task' and the status transitioned to 'publish' from another status
    //     if ($post->post_type === 'task' && $new_status === 'publish' && $old_status !== 'publish') {
    //         // Perform your custom action here
    //         $post_id = $post->ID;

    //         echo "post_id ".$post_id."<br>";
    //         $this->send_email_to_subscriber($post_id);

    //     }
    // }

    function add_button_after_acf_field( $value, $post_id, $field ) {

        $this->load_task_reminder_script();

        $staff_work_status = get_post_meta($post_id,"staff_work_status",true);
        $staff = get_post_meta($post_id,"staff",true);
        $user_ID = get_current_user_id(); 
        
        foreach($staff as $index => $staff_id){
            $work_status = $staff_work_status[$index] ?? 0;

            
            if($staff_id == $user_ID and $work_status){
                
                $mark = '<span style="border: 1px solid;padding: 2px;border-left: 5px solid;display:inline-block">Work submitted.</span><div></div>';
            $value = $mark . $value;
            }

            
        }


        return $value;
    }
    
    function custom_text_editor_meta_box_callback($field_name="",$content="") {
        // Output the HTML for the meta box
        new WPEditorToolConfig();
        $settings = array(
            'tinymce' => array(
                'toolbar1' => 'bold,italic,underline,separator,numlist,bullist,forecolor,backcolor,image,hr,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo,blockquote,spellchecker,fullscreen,custom_button', // Add your custom button here
                'plugins' => 'custom_tinymce_plugin,lists,link,fullscreen,textcolor,image,hr', // Add your custom TinyMCE plugin name here
            ),
        );

        $settings = array_merge($settings,array(
            'textarea_name' => $field_name, // Name of the textarea field
            'media_buttons' => true, // Show media buttons
            'textarea_rows' => 15, // Number of rows in the editor
        ));



        wp_editor($content, 'newsletter_body', $settings);

    }

    function load_task_reminder_script(){
        
    
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
        
            // if(!$this->send_email_to_subscriber()){
            //     // Return a response
            //     $is_error = 1;
            // }
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

        $assistant = new TaskReminderPluginAssistant();

        $post_id = intval( $_POST['post_id'] );

        ///update work submission status for this logge staff
        $assistant->set_submission_status($post_id,get_current_user_id());

        // Get the admin email
        $admin_email = get_option( 'admin_email' );

        $post_data = $assistant->get_post_data($post_id);
        $subject = $assistant->template_render($this->subject_of_admin,$post_data);
        $message = $assistant->template_render($this->message_of_admin,$post_data);

    
        // Send the email
        return $this->send_custom_email( $admin_email, $subject, $message );
    }
                          
    function send_email_to_subscriber($post_id) {
        // Get the subscriber emails
       $is_error = 0;
        $meta_values = get_post_meta( $post_id, 'staff', true );

        $assistant = new TaskReminderPluginAssistant();
        $post_data = $assistant->get_post_data($post_id);
        $subject = $assistant->template_render($this->subject_of_subscriber,$post_data);
        $message = $assistant->template_render($this->message_of_subscriber,$post_data);

        // print_r($meta_values);
        // print_r($post_data);
        // die();
        foreach ($meta_values as $user_id){
            // Get user data
            $user_info = get_userdata( $user_id );
            if($user_info){
                $user_email = $user_info->user_email;
                // Send the email
                if(!$this->send_custom_email( $user_email, $subject, $message )){
                    $is_error = 1;
                }
            }
        }
        
    
        return $is_error ? 0 : 1;
    
        
    }
    
    function setup_html(){
        $html_form = file_get_contents(TASK_REMINDER_PLUGIN_DIR."/assets/html/setup.html");
        $template_maker = new Mustache_Engine(array(
            'escape' => function($value) {
                return $value;
            }
        ));


        $message = "";
        if (isset($_REQUEST['save'])){
            $update_1 = update_option($this->subject_of_admin_key,$_REQUEST['subject_of_admin']);
            $update_2 = update_option($this->message_of_admin_key,$_REQUEST['message_of_admin']);
            $update_3 = update_option($this->subject_of_subscriber_key,$_REQUEST['subject_of_subscriber']);
            $update_4 = update_option($this->message_of_subscriber_key,$_REQUEST['message_of_subscriber']);
            

            $this->subject_of_admin = $_REQUEST['subject_of_admin'];
            $this->message_of_admin = $_REQUEST['message_of_admin'];
            $this->subject_of_subscriber = $_REQUEST['subject_of_subscriber'];
            $this->message_of_subscriber = $_REQUEST['message_of_subscriber'];
            if ($update_1 or $update_2 or $update_3 or $update_4){
        
                $message = (new TaskReminderPluginAssistant())->message_html_generate(array(
                    "message" => "Changed has been saved."
                ));
            }
            else{
                $message = (new TaskReminderPluginAssistant())->message_html_generate(array(
                    "message" => "Nothing has been changed."
                ));
            }
        }

        



        echo $template_maker->render($html_form,array(
            "subject_of_admin" => $this->subject_of_admin,
            "message_of_admin" => $this->message_of_admin,
            "subject_of_subscriber" => $this->subject_of_subscriber,
            "message_of_subscriber" => $this->message_of_subscriber,
            "setup_hint" => $this->setup_hint,
            "message" => $message,
            "page_title" => $this->setup_page_title,
        ));

        return "";
    }

    function add_setup_menu(){
        // $this->load_jquery_script();
        $this->load_task_reminder_script();
        add_menu_page($this->setup_page_title,$this->setup_menu_title,'manage_options',$this->setup_menu_slug,[$this,"setup_html"],'',null);
    }



}