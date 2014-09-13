<?php
/**
 * Plugin Name: Build A WordPress App Plugin
 * Plugin URI: https://github.com/Build-WordPress-Application/wp-app-plugin
 * Description: We are building a WordPress app together! Here is the plugin
 * Version: 0.1
 * Author: Roy Sivan
 * Author URI: http://www.roysivan.com/category/wordpress/wordpress-tutorials/#.VBPKQGRdVH0
 * License: GPL2
 */
 
 
 define( 'WP_APP_PLUGIN_URL', plugins_url( '', __FILE__ ) );
 define( 'WP_APP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
 
 require_once( 'includes/cpt_taxonomies.php' );
 
 class WPAPP_MAIN {
	 
	 function __construct() {
		 new WPAPP_CPT_TAX();
		 
	 }
	 
 }
 
 new WPAPP_MAIN();
 
 ?>