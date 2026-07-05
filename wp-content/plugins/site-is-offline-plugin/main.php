<?php
/*
  Plugin Name: Site Offline or Coming Soon
  Version: 1.6.6
  Plugin URI: https://wp-ecommerce.net/
  Author: wpecommerce
  Author URI: https://wp-ecommerce.net/
  Description: Make safe changes to your site by enabling site offline mode with this plugin you'll be able to navigate your site normally but your regular visitor will se a site offline or coming soon page
 */

function cp_siteoffline_activate() {
    $options = array(
        'enabled' => false,
        'content' => NULL,
        'version' => 1.0
    );
    if (get_option('sp_siteoffline_options') === false)
        add_option('sp_siteoffline_options', $options);
}

function cp_siteoffline_options_page() {
    add_submenu_page('options-general.php', 'Site Offline Mode Options', 'Site Offline Mode', 'manage_options', __FILE__, 'cp_siteoffline_options_page_content');
}

function cp_siteoffline_options_page_content() {  
    if (isset($_POST['cpso_save_settings'])) {
        $options = array();
        $site_offline_front_end_conent = htmlentities(stripslashes($_POST['cp_siteoffline_content']) , ENT_COMPAT, "UTF-8");
        $options['content'] = $site_offline_front_end_conent;//stripslashes($_POST['cp_siteoffline_content']);
        if ($_POST['cp_siteoffline_enabled'] == 'true')
            $options['enabled'] = true;
        else
            $options['enabled'] = false;
        update_option('sp_siteoffline_options', $options);
    }
    
    $options = get_option('sp_siteoffline_options');
    include_once('site-offline-options.php');
}

function cp_siteoffline_check() {
    
    if(is_admin()){//Don't show on admin side of the site
        return;
    }
    
    $options = get_option('sp_siteoffline_options');
    if ($options['enabled'] === false){
        return;
    }

    if (!current_user_can('edit_posts') && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ))) {
        $protocol = "HTTP/1.0";
        if ("HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"]) {
            $protocol = "HTTP/1.1";
        }
        header("$protocol 503 Service Unavailable", true, 503);
        header("Retry-After: 3600");
        $site_offline_front_end_conent = html_entity_decode($options['content'], ENT_COMPAT,"UTF-8");
        echo $site_offline_front_end_conent;
        //echo $options['content'];
        exit();
    }
}

function cpso_maintenance_mode_check_msg()
{
    $options = get_option('sp_siteoffline_options');
    $offline_mode_enabled = $options['enabled'];
    if($offline_mode_enabled){
        $msg = '<p>The Maintenance Mode is active. Please don\'t forget to <a href="options-general.php?page=site-is-offline-plugin/main.php">deactivate it</a> as soon as you are done.</p>';
        echo '<div class="updated fade">'.$msg.'</div>';
    }
}

function cpso_login_form_msg()
{
    $msg = '';
    $options = get_option('sp_siteoffline_options');
    $offline_mode_enabled = $options['enabled'];
    if($offline_mode_enabled){    
        $msg = '<div id="login_error"><p>The Site is Currently in Offline Mode.</p></div>';
    }
    return $msg;
}

/* The most effective would be init hook but then if the user logout, user may need to disable or 
 * delete the plugin for a succesful login again.
 * No effect would be on admin dashboard, so user can login by going to siturl.com/wp-admin
 * if the user have manage_options capabaility he can easily navigate,test,make changes without letting the general public know.
 * */
add_action('init', 'cp_siteoffline_activate');
add_action('init', 'cp_siteoffline_check');
add_action('admin_menu', 'cp_siteoffline_options_page');

if (is_admin()) {
    add_action('admin_notices', 'cpso_maintenance_mode_check_msg');
}

add_filter('login_message', 'cpso_login_form_msg');			
