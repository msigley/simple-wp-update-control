<?php
/*
Plugin Name: Simple Wordpress Update Control
Description: Hides core Wordpress, plugin, and theme update notifications for better version control. Also disables all auto-updates.
Version: 2.0.0
Author: Matthew Sigley
Author URI: http://github.com/msigley/
License: GPL2
Update URI: false
*/
function simple_vc_activation_deactivation() {
    wp_clean_update_cache();
    wp_clear_scheduled_hook( 'wp_version_check' );
    wp_clear_scheduled_hook( 'wp_update_plugins' );
    wp_clear_scheduled_hook( 'wp_update_themes' );
}
register_activation_hook( __FILE__, 'simple_vc_activation_deactivation' );
register_deactivation_hook( __FILE__, 'simple_vc_activation_deactivation' );

//Accidental update prevention
//Hide all upgrade notices
function hide_admin_notices() {
    remove_action( 'admin_notices', 'update_nag', 3 );
    remove_submenu_page( 'index.php', 'update-core.php' );
}
add_action( 'admin_menu','hide_admin_notices', 9999 );

add_filter( 'user_has_cap', function( $capabilities ) {
    $capabilities['update_core'] = false;
    $capabilities['update_themes'] = false;
    $capabilities['update_plugins'] = false;
    $capabilities['install_languages'] = false;
    $capabilities['update_languages'] = false;

    return $capabilities;
}, 9999 );

// Disable all core updates.
function simple_vc_disable_all_updates( $updates ) {
    if ( empty( $updates ) )
        return $updates;

    $updates->updates = array();
    
	return $updates;
}
remove_action( 'admin_init', '_maybe_update_core' );
add_filter( 'pre_set_site_transient_update_core', 'simple_vc_disable_all_updates', 9999, 1 );
remove_action( 'wp_version_check', 'wp_version_check' );

// Disable plugin updates
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'load-plugins.php', 'wp_update_plugins' );
remove_action( 'load-update.php', 'wp_update_plugins' );
remove_action( 'load-update-core.php', 'wp_update_plugins' );
remove_action( 'wp_update_plugins', 'wp_update_plugins' );
add_filter( 'pre_site_transient_update_plugins', function() { return new stdClass; }, 9999 );


// Disable theme updates
remove_action( 'admin_init', '_maybe_update_themes' );
remove_action( 'load-themes.php', 'wp_update_themes' );
remove_action( 'load-update.php', 'wp_update_themes' );
remove_action( 'load-update-core.php', 'wp_update_themes' );
remove_action( 'wp_update_plugins', 'wp_update_themes' );
add_filter( 'pre_site_transient_update_themes', function() { return new stdClass; }, 9999 );

// Disable automatic version checks
remove_action( 'init', 'wp_schedule_update_checks' );
add_filter( 'automatic_updater_disabled', '__return_true', 9999 );

//Disable development auto-updates
add_filter( 'allow_dev_auto_core_updates', '__return_false', 9999 );
//Disable major core auto-updates
add_filter( 'allow_major_auto_core_updates', '__return_false', 9999 );
//Allow minor/security core auto-updates
add_filter( 'allow_minor_auto_core_updates', '__return_false', 9999 );

//Disable plugin auto-updates
add_filter( 'auto_update_plugin', '__return_false', 9999 );
add_filter( 'plugins_auto_update_enabled', '__return_false', 9999 );

//Disable theme auto-updates
add_filter( 'auto_update_theme', '__return_false', 9999 );
add_filter( 'themes_auto_update_enabled', '__return_false', 9999 );

//Disable translation/language auto-updates
add_filter( 'auto_update_translation', '__return_false', 9999 );

//Disable Site Health Tests for auto-updates
add_filter( 'site_status_tests', function( $tests ) { 
    unset( $tests['direct']['plugin_theme_auto_updates'] );
    unset( $tests['async']['background_updates'] );
    return $tests;
}, 9999 );
