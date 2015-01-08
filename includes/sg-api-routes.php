<?php
global $myplugin_api_mytype;

class sg_routes {

	function __construct() {
		global $myplugin_api_mytype;
		add_filter( 'json_endpoints', array( $this, 'register_routes' ) );
	}

	function register_routes( $routes ) {
		$routes['/sg_users'] = array(
			array( array( $this, 'get_users'), WP_JSON_Server::READABLE ),
			array( array( $this, 'add_user'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON )
		);
		$routes['/sg_users/(?P<id>\d+)'] = array(
			array( array( $this, 'get_single_user'), WP_JSON_Server::READABLE )
		);

		// Add more custom routes here
		
		$routes['/save_sg/(?P<id>\d+)'] = array(
			array( array( $this, 'save_style_guide'), WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON )
		);
		
		return $routes;
	}

	function get_users() {
		
		$return['users'] = get_users();
		
		$response = new WP_JSON_Response();
		$response->set_data( $return );
		return $response;

	}

	function get_single_user( $id ) {
		
		$return['user'] = get_user_by( 'id', intval( $id ) );

		$response = new WP_JSON_Response();
		$response->set_data( $return );
		return $response;

	}

	function add_user( $data ) {
		
		$response = new WP_JSON_Response();		
		
		if ( ! empty( $data['id'] ) ) {
			return new WP_Error( 'json_user_exists', __( 'Cannot create existing user.' ), array( 'status' => 400 ) );
		}

		if ( get_site_option('users_can_register') ) {
			
			$user_id = $this->insert_user( $data );
			
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
			
			$return['user_id'] = $user_id;			
			$return['users'] = $this->get_user( $user_id );

			if ( ! $return['users'] instanceof WP_JSON_ResponseInterface ) {
				$return['users'] = new WP_JSON_Response( $return['users'] );
			}
			
			$response = new WP_JSON_Response();
			$response->set_data( $return );
			return $response;
			
			
		} else {
			return new WP_Error( 'json_cannot_create', __( 'Sorry, user creation is not allowed.' ), array( 'status' => 403 ) );
		}
		
	}



	protected function insert_user( $data ) {
		$user = new stdClass;

		if ( ! empty( $data['ID'] ) ) {
			$existing = get_userdata( $data['ID'] );

			if ( ! $existing ) {
				return new WP_Error( 'json_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 404 ) );
			}

			if ( ! current_user_can( 'edit_user', $data['ID'] ) ) {
				return new WP_Error( 'json_user_cannot_edit', __( 'Sorry, you are not allowed to edit this user.' ), array( 'status' => 403 ) );
			}

			$user->ID = $existing->ID;
			$update = true;
		} else {
			if ( !get_site_option('users_can_register') ) {
				return new WP_Error( 'json_cannot_create', __( 'Sorry, you are not allowed to create users.' ), array( 'status' => 403 ) );
			}

			$required = array( 'username', 'password', 'email' );

			foreach ( $required as $arg ) {
				if ( empty( $data[ $arg ] ) ) {
					return new WP_Error( 'json_missing_callback_param', sprintf( __( 'Missing parameter %s' ), $arg ), array( 'status' => 400 ) );
				}
			}

			$update = false;
		}

		// Basic authentication details
		if ( isset( $data['username'] ) ) {
			$user->user_login = $data['username'];
		}

		if ( isset( $data['password'] ) ) {
			$user->user_pass = $data['password'];
		}

		// Names
		if ( isset( $data['name'] ) ) {
			$user->display_name = $data['name'];
		}

		if ( isset( $data['first_name'] ) ) {
			$user->first_name = $data['first_name'];
		}

		if ( isset( $data['last_name'] ) ) {
			$user->last_name = $data['last_name'];
		}

		if ( isset( $data['nickname'] ) ) {
			$user->nickname = $data['nickname'];
		}

		if ( ! empty( $data['slug'] ) ) {
			$user->user_nicename = $data['slug'];
		}

		// URL
		if ( ! empty( $data['URL'] ) ) {
			$escaped = esc_url_raw( $user->user_url );

			if ( $escaped !== $user->user_url ) {
				return new WP_Error( 'json_invalid_url', __( 'Invalid user URL.' ), array( 'status' => 400 ) );
			}

			$user->user_url = $data['URL'];
		}

		// Description
		if ( ! empty( $data['description'] ) ) {
			$user->description = $data['description'];
		}

		// Email
		if ( ! empty( $data['email'] ) ) {
			$user->user_email = $data['email'];
		}

		// Role
		if ( ! empty( $data['role'] ) ) {
			$user->role = $data['role'];
		}

		// Pre-flight check
		$user = apply_filters( 'json_pre_insert_user', $user, $data );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$user_id = $update ? wp_update_user( $user ) : wp_insert_user( $user );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$user->ID = $user_id;

		do_action( 'json_insert_user', $user, $data, $update );

		return $user_id;
	}
	
	public function get_user( $id, $context = 'view' ) {
		$id = (int) $id;
		$current_user_id = get_current_user_id();

		if ( $current_user_id !== $id && ! current_user_can( 'list_users' ) ) {
			return new WP_Error( 'json_user_cannot_list', __( 'Sorry, you are not allowed to view this user.' ), array( 'status' => 403 ) );
		}

		$user = get_userdata( $id );

		if ( empty( $user->ID ) ) {
			return new WP_Error( 'json_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 400 ) );
		}

		return $this->prepare_user( $user, $context );
	}
	
	protected function prepare_user( $user, $context = 'view' ) {
		$user_fields = array(
			'ID'          => $user->ID,
			'username'    => $user->user_login,
			'name'        => $user->display_name,
			'first_name'  => $user->first_name,
			'last_name'   => $user->last_name,
			'nickname'    => $user->nickname,
			'slug'        => $user->user_nicename,
			'URL'         => $user->user_url,
			'avatar'      => json_get_avatar_url( $user->user_email ),
			'description' => $user->description,
		);

		$user_fields['registered'] = date( 'c', strtotime( $user->user_registered ) );

		if ( $context === 'view' || $context === 'edit' ) {
			$user_fields['roles']        = $user->roles;
			$user_fields['capabilities'] = $user->allcaps;
			$user_fields['email']        = false;
		}

		if ( $context === 'edit' ) {
			// The user's specific caps should only be needed if you're editing
			// the user, as allcaps should handle most uses
			$user_fields['email']              = $user->user_email;
			$user_fields['extra_capabilities'] = $user->caps;
		}

		$user_fields['meta'] = array(
			'links' => array(
				'self' => json_url( '/users/' . $user->ID ),
				'archives' => json_url( '/users/' . $user->ID . '/posts' ),
			),
		);

		return apply_filters( 'json_prepare_user', $user_fields, $user, $context );
	}
	
	function save_style_guide( $data ) {
		
		$return['data'] = $data;
		
		$response = new WP_JSON_Response();
		$response->set_data( $return );
		return $response;	
	}

}

?>