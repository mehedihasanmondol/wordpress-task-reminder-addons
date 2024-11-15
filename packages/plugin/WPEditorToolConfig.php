<?php
/**
 * Created by PhpStorm.
 * User: mhnoy
 * Date: 8/26/2023
 * Time: 5:09 PM
 */

class WPEditorToolConfig
{

    public function __construct()
    {

        function custom_add_tinymce_plugin($plugins) {
            $plugins['custom_tinymce_plugin'] = NEWS_LETTER_PLUGIN_DIR_URL . 'assets/custom-tinymce-plugin.js';
            return $plugins;
        }
        add_filter('mce_external_plugins', 'custom_add_tinymce_plugin');

    }
}