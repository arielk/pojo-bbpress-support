<?php
/*
Plugin Name: EDD bbPress Support Dashboard
Description: Support dashboard for sites running EDD and bbPress
Author: Pippin Williamson and Sunny Ratilal and Yakir Sitbon
Author URI: https://easydigitaldownloads.com/
Version: 2.0
Text Domain: pojo-bbpress-support
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function edd_bbp_d_activate() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/core-options.php';
	do_action( 'edd_bbp_d_activation' );
}
register_activation_hook( __FILE__ , 'edd_bbp_d_activate' );

/**
 * Setup the plugin
 */
function edd_bbp_d_setup() {
	load_plugin_textdomain( 'pojo-bbpress-support', false, basename( dirname( __FILE__ ) ) . '/languages' );
	
	edd_bbp_d_define_constants();
	edd_bbp_d_includes();
}
add_action( 'plugins_loaded', 'edd_bbp_d_setup' );

/**
 * Setup the globals
 */
function edd_bbp_d_define_constants() {
	define( 'EDD_BBP_D_PATH',          plugin_dir_path( __FILE__ )   );
	define( 'EDD_BBP_D_ADMIN_PATH',    EDD_BBP_D_PATH . 'admin/'     );
	define( 'EDD_BBP_D_TEMPLATE_PATH', EDD_BBP_D_PATH . 'templates/' );
	define( 'EDD_BBP_D_INCLUDES_PATH', EDD_BBP_D_PATH . 'includes/'  );
	define( 'EDD_BBP_D_WIDGETS_PATH',  EDD_BBP_D_PATH . 'widgets/'   );
	define( 'EDD_BBP_D_URL',           plugin_dir_url( dirname( __FILE__ ) ) . basename( dirname( __FILE__ ) ) . '/' );
	define( 'EDD_BBP_D_WIDGETS_URL',   plugins_url( 'widgets', __FILE__ ) );
}

/**
 * Includes all the files required for the plugin to run
 */
function edd_bbp_d_includes() {
	// Load backend fles
	if ( is_admin() ) {
		$admin_files = array(
			'bbps-admin',
			'bbps-settings',
		);

		foreach ( $admin_files as $file ) {
			include EDD_BBP_D_ADMIN_PATH . $file . '.php';
		}
	}

	$include_files = array(
		'actions',
		'filters',
		'common-functions',
		'support-functions',
		'core-options',
		'shortcodes',
		'widget-hours',
	);

	foreach ( $include_files as $file ) {
		include_once EDD_BBP_D_INCLUDES_PATH . $file . '.php';
	}
}

/**
 * Register any widgets
 */
function edd_bbp_d_register_widgets() {
	register_widget( 'BBPS_Forum_Support_Hours' );
}
add_action( 'widgets_init', 'edd_bbp_d_register_widgets' );

function edd_bbp_d_register_settings() {
	include( 'includes/class-pojo-settings-supports.php' );
	new Pojo_Settings_Supports( 110 );
}
add_action( 'pojo_framework_base_settings_included', 'edd_bbp_d_register_settings' );