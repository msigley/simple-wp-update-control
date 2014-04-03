<?php
/*
Plugin Name: Simple Wordpress Version Control
Plugin URI: http://github.com/msigley/
Description: Hides major core Wordpress, plugin, and theme update notifications for better version control. Also disables all auto-updates but minor/security Wordpress updates.
Version: 1.0.3
Author: Matthew Sigley
License: GPL2

Change Log:
- Added filter to prevent available major core updates from being saved in the db (1.0.3)
- Updates page is visable again (1.0.3)
- Updates page is now hidden in the admin menu (1.0.2)
- Update notices are now hidden in the admin area (1.0.1)
- Added auto-updater filters (1.0.0)
*/

//Accidental update prevention
//Hide all upgrade notices
function hide_admin_notices() {
    remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action('admin_menu','hide_admin_notices');

// Disable major core updates, but allow minor ones
function simple_vc_disable_major_updates($updates) {
		/* Test Cases
		$test_update = clone $updates->updates[0];
		$test_update->current = '3.8.2'; //Minor update
		$updates->updates[] = $test_update;
		$test_update = clone $updates->updates[0];
		$test_update->current = '3.9.0'; //Major update
		$updates->updates[] = $test_update;
		*/
		include ABSPATH . WPINC . '/version.php'; // $wp_version; // x.y.z
		$current_branch = implode( '.', array_slice( preg_split( '/[.-]/', $wp_version  ), 0, 2 ) ); // x.y
		foreach ( $updates->updates as $key => $update ) {
			if(version_compare( $update->current, $wp_version, '>' )) {
				$new_branch = implode( '.', array_slice( preg_split( '/[.-]/', $update->current ), 0, 2 ) ); // x.y
				if(version_compare( $new_branch, $current_branch, '>' )) {
					//Prevent major updates
					unset($updates->updates[$key]);
				}
			}
		}
	return $updates;
}
add_filter( 'pre_set_site_transient_update_core', 'simple_vc_disable_major_updates', 10, 1 );

// Disable theme updates
remove_action( 'load-update-core.php', 'wp_update_themes' );
add_filter( 'pre_site_transient_update_themes', create_function( '$a', "return null;" ) );

// Disable plugin updates
remove_action( 'load-update-core.php', 'wp_update_plugins' );
add_filter( 'pre_site_transient_update_plugins', create_function( '$a', "return null;" ) );

//Configure auto-updates for Wordpress 3.7+
//Disable development auto-updates
add_filter( 'allow_dev_auto_core_updates', '__return_false' );
//Disable core auto-updates
add_filter( 'allow_major_auto_core_updates', '__return_false' );
//Disable plugin auto-updates
add_filter( 'auto_update_plugin', '__return_false' );
//Disable theme auto-updates
add_filter( 'auto_update_theme', '__return_false' );

//Allow minor/security auto-updates
add_filter( 'allow_minor_auto_core_updates', '__return_true' );
//Allow translation/language auto-updates
add_filter( 'auto_update_translation', '__return_true' );