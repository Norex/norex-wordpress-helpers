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
   
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function init() {
        if (get_option('norex_wordpress_helpers_enable_core'))
            require_once('norex-wordpress-helpers-core.php');
        if (get_option('norex_wordpress_helpers_enable_cms'))
            require_once('norex-wordpress-helpers-cms.php');
    }

    public function admin_init() {
        add_settings_section('norex_wordpress_helpers_settings_section', 'Norex Wordpress Helpers Options', array($this, 'settings_callback'),/* 'norex_wordpress_helpers_settings_callback',*/ 'norex_wordpress_helpers_settings');
        add_settings_field('norex_wordpress_helpers_enable_cms', 'Enable CMS', array($this, 'checkbox_callback'), 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings_section', array('norex_wordpress_helpers_enable_cms'));
        add_settings_field('norex_wordpress_helpers_enable_core', 'Enable Core', array($this, 'checkbox_callback'), 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings_section', array('norex_wordpress_helpers_enable_core'));

        register_setting('norex_wordpress_helpers_settings_section', 'norex_wordpress_helpers_enable_cms');
        register_setting('norex_wordpress_helpers_settings_section', 'norex_wordpress_helpers_enable_core');
    }

    public function admin_menu() {
         add_submenu_page( 'options-general.php', 'Norex Wordpress Helpers Settings', 'Norex Wordpress Helpers Settings', 'norex_wordpress_helpers_settings', 'norex_wordpress_helpers_settings',  array($this, 'settings_display'));
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

    public function settings_callback() {
         echo '<p>Select which areas of content you wish to display.</p>';  
    }

    public function checkbox_callback($args) {
        $html = '<input type="checkbox" id="' . $args[0] . '" name="' . $args[0] . '" value="1" ' . checked(1, get_option($args[0]), false) . '/>';  
   
        if (isset($args[1]))
            $html .= '<label for="' . $args[0] . '"> '  . $args[0] . '</label>';  
        echo $html;  
    }
}

new NorexWordpressHelpers();

register_activation_hook(__FILE__, array('NorexWordpressHelpers', 'on_activate'));


