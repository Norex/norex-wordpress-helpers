<?php

/**
 * File: norex-wordpress-helpers-cms.php
 * Description: Add useful features to wordpress.
 */

if (!class_exists('NorexCMS')) {
    add_action('init', array('NorexCMS', 'kill_admin_edit'));
    add_action('pre_user_query', array('NorexCMS', 'hide_admins'));
    add_action('delete_user', array('NorexCMS', 'no_delete_admin'));
    add_action('wp_dashboard_setup', array('NorexCMS', 'create_widgets'),10);
    add_filter('editable_roles', array('NorexCMS', 'hide_admin_role'));
    
    class NorexCMS {
        public static function support_widget(){ 
            echo '<p style="font-size:12px;"><img style="margin:auto;display:block;" src="' . plugin_dir_url(__FILE__) . 'assets/images/assistance-graphic.png" alt="Need Help?" /><br />Need help? <abbr title="+1 (902) 444-3335">Call</abbr> or <a href="mailto:support@norex.ca">email</a> Norex, our support line is +1 (902) 444-3335.<br /><br />We\'re also available on the web at <a href="http://norex.ca">www.norex.ca</a></p>';
        }
      
        public static function create_widgets(){
            wp_add_dashboard_widget('support_dashboard_widget', 'Technical Support', array('NorexCMS', 'support_widget'));
        
            //Bring out widget to the top
            //Globalize the metaboxes array, this holds all the widgets for wp-admin
            global $wp_meta_boxes;
            $support_widget_backup = array('support_dashboard_widget' => $wp_meta_boxes['dashboard']['normal']['core']['support_dashboard_widget']);
            unset($wp_meta_boxes['dashboard']['normal']['core']['support_dashboard_widget']);
            $wp_meta_boxes['dashboard']['normal']['core'] = array_merge($support_widget_backup, $wp_meta_boxes['dashboard']['normal']['core']);
        }
            
        public static function kill_admin_edit(){
            global $menu, $current_user;
            get_currentuserinfo();
          
            if($_SERVER['SCRIPT_URL'] == '/wp-admin/user-edit.php'){
                $uid = $_REQUEST['user_id'];
                $user = get_userdata($uid);
                if(array_key_exists('administrator', $user->caps) && !in_array('administrator', $current_user->roles)){
                    wp_die('You Cannot Edit This User.');
                }
            }
        }
        
        public static function hide_admins($t){
            global $current_user;
            get_currentuserinfo();
            if (!in_array('administrator', $current_user->roles)) {
                $t->query_where .= ' AND user_login != "norex"';
            }
        }
        
        public static function hide_admin_role($roles){
            global $current_user;
            get_currentuserinfo();
            if(!in_array('administrator', $current_user->roles)){
                unset($roles['administrator']);
            }
            return $roles;
        }
            
        public static function no_delete_admin($id){
            global $current_user;
            get_currentuserinfo();
            $user = get_userdata($id);
            if(!in_array('administrator', $current_user->roles) && array_key_exists('administrator', $user->wp_capabilities) && $user->wp_capabilities['administrator'] == 1){
                wp_die('You don\'t have permission to delete this user!');
                exit();
            }
        }   
    }
}