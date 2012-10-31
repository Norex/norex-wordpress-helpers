<?php

/**
 * Plugin Name: Norex Wordpress Helpers
 * Author: Norex
 * Author URI: http://norex.ca/
 * Version: 1.0.0
 * Description: A group of helpful functions when developing a wordpress site.
 */

define('NOREX_HELPERS', true);

class NorexWordpressHelpers {
   
    public function settings_init() {
        add_settings_section('norex_wordpress_helpers_settings_section', 'Norex Wordpress Helpers Options', 'norex_wordpress_helpers_settings_callback', 'norex_wordpress_helpers_settings');
        add_settings_field('norex_wordpress_helpers_enable_cms', 'Enable CMS', 'norex_wordpress_helpers_checkbox_callback', 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings_section', array('norex_wordpress_helpers_enable_cms'));
        add_settings_field('norex_wordpress_helpers_enable_core', 'Enable Core', 'norex_wordpress_helpers_checkbox_callback', 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings_section', array('norex_wordpress_helpers_enable_core'));

        register_setting('norex_wordpress_helpers_settings_section', 'norex_wordpress_helpers_enable_cms');
        register_setting('norex_wordpress_helpers_settings_section', 'norex_wordpress_helpers_enable_core');
    }

    public function settings_display() {
        ?>

        <div class="wrap">
            <h2>Norex Wordpress Helpers Settings</h2>

            <form method="post" action="options.php"> 
                <?php if (current_user_can('norex_wordpress_helpers_settings_admin')) { ?>
                    <?php settings_fields('norex_wordpress_helpers_settings_section'); ?>
                    <?php do_settings_sections('norex_wordpress_helpers_settings'); ?>
                    <?php submit_button(); ?>
                <?php } ?>
            </form>
        </div>

        <?php
    }

    public function on_activate() {
        $role = get_role('administrator');
        $role->add_cap("norex_wordpress_helpers_settings_admin");
    }
}

add_action('admin_menu', 'norex_wordpress_helpers_settings');
function norex_wordpress_helpers_settings() {
    $norex_wordpress_helpers = new NorexWordpressHelpers();

    add_menu_page('Norex Wordpress Helpers Settings', 'Norex Wordpress Helpers Settings', 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings', array(&$norex_wordpress_helpers, 'settings_display'));
}

add_action('admin_init', 'norex_wordpress_helpers_init_settings');
function norex_wordpress_helpers_init_settings() {
    $norex_wordpress_helpers = new NorexWordpressHelpers();
    $norex_wordpress_helpers->settings_init();
}

add_action('init', 'norex_wordpress_helpers_init');
function norex_wordpress_helpers_init() {
    //if (get_option('norex_wordpress_helpers_enable_core'))
        //echo 'Core Enabled';
   // if (get_option('norex_wordpress_helpers_enable_cms'))
        //echo 'CMS Enabled';
}


function norex_wordpress_helpers_settings_callback() {  
    echo '<p>Select which areas of content you wish to display.</p>';  
}

function norex_wordpress_helpers_checkbox_callback($args) {    
    $html = '<input type="checkbox" id="' . $args[0] . '" name="' . $args[0] . '" value="1" ' . checked(1, get_option($args[0]), false) . '/>';  
   
    if (isset($args[1]))
        $html .= '<label for="' . $args[0] . '"> '  . $args[0] . '</label>';  
    echo $html;  
} 

register_activation_hook(__FILE__, array('NorexWordpressHelpers', 'on_activate'));

