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
 require_once( 'includes/sg-api-routes.php' );
 require_once( 'includes/sg-custom-api.php' );
 
 
 class WPAPP_MAIN {
	 
	 function __construct() {
		 new WPAPP_CPT_TAX();
		 new sg_routes();
		 new sg_custom_api();		 
	 }
	 
 }
 
 new WPAPP_MAIN();
 
 function ensure_loaded_first( array $plugins ) {
    $key = array_search( plugin_basename( __FILE__ ), $plugins);
    if (false !== $key) {
        array_splice($plugins, $key, 1);
        array_push($plugins, plugin_basename( __FILE__ ));
    }
    return $plugins;
}

add_filter('pre_update_option_active_plugins', 'ensure_loaded_first');
 
 ?>