<?php

namespace ArmadaPlugin\Admin;

/**
 * ArmadaPlugin API Settings Class
 */
class ApiSettings {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_settings' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_ajax_update_armada_api_key', array( $this, 'ajax_update_api_key' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		wp_localize_script(
			'armada-delivery-for-woocommerce',
			'armadaPluginSettings',
			array(
				'apiKey' => self::get_api_key(),
				'nonce'  => wp_create_nonce( 'armada_plugin_api_nonce' ),
			)
		);
	}

	/**
	 * Register settings for the API key.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		// Register the setting
		register_setting(
			'armada_plugin_api_settings',
			'armada_plugin_api_key',
			array(
				'type'              => 'string',
				'description'       => __( 'Armada API Key', 'armada-delivery-for-woocommerce' ),
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
	}

	/**
	 * Register settings for the REST API.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_settings() {
		register_setting(
			'general',  // Use 'general' for WordPress core settings endpoint
			'armada_plugin_api_key',
			array(
				'type'              => 'string',
				'description'       => __( 'Armada API Key', 'armada-delivery-for-woocommerce' ),
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => array(
					'schema' => array(
						'type'        => 'string',
						'description' => __( 'Armada API Key', 'armada-delivery-for-woocommerce' ),
					),
				),
				'default'           => '',
			)
		);
	}

	/**
	 * Get the API key.
	 *
	 * @return string
	 */
	public static function get_api_key() {
		return get_option( 'armada_plugin_api_key', '' );
	}

	/**
	 * Save the API key.
	 *
	 * @param string $api_key The API key to save.
	 * @return bool
	 */
	public static function save_api_key( $api_key ) {
		return update_option( 'armada_plugin_api_key', sanitize_text_field( $api_key ) );
	}

	/**
	 * Register custom REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'armada-plugin/v1',
			'/api-key',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_api_key_rest' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'armada-plugin/v1',
			'/api-key',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_api_key_rest' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'api_key' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Get API key REST callback.
	 *
	 * @return WP_REST_Response
	 */
	public function get_api_key_rest() {
		return rest_ensure_response(
			array(
				'api_key' => self::get_api_key(),
			)
		);
	}

	/**
	 * Update API key REST callback.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public function update_api_key_rest( $request ) {
		$api_key = $request->get_param( 'api_key' );
		$success = self::save_api_key( $api_key );

		if ( ! $success ) {
			return new \WP_Error(
				'armada_api_key_update_failed',
				__( 'Failed to update API key.', 'armada-delivery-for-woocommerce' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response(
			array(
				'api_key' => self::get_api_key(),
				'success' => true,
			)
		);
	}

	/**
	 * AJAX handler for updating the API key.
	 *
	 * @since 1.0.0
	 */
	public function ajax_update_api_key() {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'armada_plugin_api_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'armada-delivery-for-woocommerce' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to update the API key.', 'armada-delivery-for-woocommerce' ) ) );
		}

		// Get and sanitize the API key
		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

		// Save the API key
		$success = self::save_api_key( $api_key );

		if ( ! $success ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update API key.', 'armada-delivery-for-woocommerce' ) ) );
		}

		wp_send_json_success( array( 'api_key' => self::get_api_key() ) );
	}
}
