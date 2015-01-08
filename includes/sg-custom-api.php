<?php

class sg_custom_api {
	
	function __construct() {
		add_filter( 'json_prepare_post', array( $this, 'post_additions' ), 10, 3 );
	}
	
	function post_additions( $data, $post, $context ) {
		if( $post['post_type'] === 'style-guides' ){
			$data['sg_meta'] = get_post_meta( $post['ID'] );
		}
		return $data;
	}
	
}
	
?>