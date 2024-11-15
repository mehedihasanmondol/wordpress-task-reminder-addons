<?php
/*
 * Plugin Name: Task reminder addons
 * Description: A plugin to add a submit button when a post is viewed and calendar color update.
 * Version: 1.0
 * Author: Mehedi Hasan Mondol
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once __DIR__.'/packages/vendor/autoload.php';


define("TASK_REMINDER_PLUGIN_DIR",__DIR__);
define("TASK_REMINDER_PLUGIN_DIR_URL",plugin_dir_url(__FILE__));

new Init();





