<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/27/2023
 * Time: 3:06 AM
 */

class NewsLetterUnSubscriber
{

    function add_unsubscriber($roll,$post_id,$user_id){
        global $wpdb;
        $table_name = $wpdb->prefix . (new NewsLetterPluginConfig())->unsubscriber_table_name;
        if (!$this->get_unsubscription($post_id,$user_id)){
            $wpdb->insert(
                $table_name,
                array(
                    'time' => (new NewsLetterPluginAssistant())->current_time_stamp(),
                    'roll' => $roll,
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                )
            );
        }

    }

    function get_unsubscription($post_id,$user_id){
        global $wpdb;
        $table_name = $wpdb->prefix . (new NewsLetterPluginConfig())->unsubscriber_table_name; // Replace with your custom table name

        $query = "SELECT id,roll FROM $table_name where post_id='$post_id' and user_id='$user_id' limit 1";
        return $wpdb->get_results($query)[0] ?? null;
    }
}