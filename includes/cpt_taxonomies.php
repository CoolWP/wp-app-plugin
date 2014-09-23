<?php

class WPAPP_CPT_TAX {
	function __construct() {
		// CPT - Style Guides
		add_action( 'init', array( $this, 'styleGuideCPT' ) );
	}
	
	function styleGuideCPT() {
		$labels = array(
			'name'               => _x( 'Style Guide', 'post type general name', 'your-plugin-textdomain' ),
			'singular_name'      => _x( 'Style Guide', 'post type singular name', 'your-plugin-textdomain' ),
			'menu_name'          => _x( 'Style Guides', 'admin menu', 'your-plugin-textdomain' ),
			'name_admin_bar'     => _x( 'Style Guide', 'add new on admin bar', 'your-plugin-textdomain' ),
			'add_new'            => _x( 'Add New', 'style-guide', 'your-plugin-textdomain' ),
			'add_new_item'       => __( 'Add New Style Guide', 'your-plugin-textdomain' ),
			'new_item'           => __( 'New Style', 'your-plugin-textdomain' ),
			'edit_item'          => __( 'Edit Style', 'your-plugin-textdomain' ),
			'view_item'          => __( 'View Style', 'your-plugin-textdomain' ),
			'all_items'          => __( 'All Styles', 'your-plugin-textdomain' ),
			'search_items'       => __( 'Search Styles', 'your-plugin-textdomain' ),
			'parent_item_colon'  => __( 'Parent Styles:', 'your-plugin-textdomain' ),
			'not_found'          => __( 'No styles found.', 'your-plugin-textdomain' ),
			'not_found_in_trash' => __( 'No styles found in Trash.', 'your-plugin-textdomain' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'style' ),
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => null,
			'supports'           => array( 'title', 'comments', 'custom-fields' )
		);
	
		register_post_type( 'style-guides', $args );
	}
}

?>