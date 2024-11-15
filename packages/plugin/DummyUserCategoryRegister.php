<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/25/2023
 * Time: 5:03 PM
 */

class DummyUserCategoryRegister
{
    public function __construct()
    {
        function custom_register_user_categories() {
            $labels = array(
                'name' => _x('User Categories', 'taxonomy general name'),
                'singular_name' => _x('User Category', 'taxonomy singular name'),
                'search_items' => __('Search User Categories'),
                'all_items' => __('All User Categories'),
                'parent_item' => __('Parent User Category'),
                'parent_item_colon' => __('Parent User Category:'),
                'edit_item' => __('Edit User Category'),
                'update_item' => __('Update User Category'),
                'add_new_item' => __('Add New User Category'),
                'new_item_name' => __('New User Category Name'),
                'menu_name' => __('User Categories'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'user-category'),
            );

            register_taxonomy('user-category', array('user'), $args);
        }
        add_action('init', 'custom_register_user_categories');
    }
}