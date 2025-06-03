<?php

namespace ARMADEFO\API;

/**
 * ArmadaPlugin API Client Class
 */
class ApiClient {
	/**
	 * API base URL
	 *
	 * @var string
	 */
	private $api_base_url;

	/**
	 * API key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->api_base_url = 'https://api.armadadelivery.com';
		$this->api_key = get_option('armadefo_api_key', '');
	}
	
	/**
	 * Create a delivery request
	 *
	 * @param array $delivery_data Delivery data.
	 * @return array|WP_Error
	 */
	public function create_delivery($delivery_data) {
		$endpoint = '/v0/deliveries';
		
		$response = wp_remote_post(
			$this->api_base_url . $endpoint,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Key ' . $this->api_key,
				),
				'body' => json_encode($delivery_data),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = json_decode(wp_remote_retrieve_body($response), true);
		error_log(print_r($response_body, true));
		error_log(print_r($response_code, true));

		if ($response_code < 200 || $response_code >= 300) {
			return new \WP_Error(
				'armada_api_error',
				isset($response_body['message']) ? $response_body['message'] : 'Unknown API error',
				array(
					'status' => $response_code,
					'response' => $response_body,
				)
			);
		}

		return $response_body;
	}
}
