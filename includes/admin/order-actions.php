<?php

namespace ARMADEFO\Admin;

/**
 * ArmadaPlugin Order Actions Class
 */
class OrderActions {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	/**
	 * API Client instance
	 *
	 * @var \ARMADEFO\API\ApiClient
	 */
	private $api_client;

	public function __construct() {
		// Initialize API client
		$this->api_client = new \ARMADEFO\API\ApiClient();

		// Add custom order action button
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_send_delivery_order_action' ), 10, 2 );
		
		// Handle the custom action
		add_action( 'admin_action_armadefo_send_delivery', array( $this, 'process_send_delivery_action' ) );
		
		// Add custom CSS for the action button
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		
		add_filter( 'woocommerce_admin_order_preview_get_order_details', array( $this, 'admin_order_preview_add_order_notes_data' ), 10, 2 );
		add_action( 'woocommerce_admin_order_preview_end', array( $this, 'add_qr_code_to_preview_data' ) );
		
	}

	function admin_order_preview_add_order_notes_data( $data, $order ) {
        
    $notes = wc_get_order_notes([
        'order_id' => $order->get_id(),
    ]);
		$qr_code_link = $order->get_meta('_armada_qr_code_link');

    ob_start();

    ?>
    <div class="wc-order-preview-order-note-container">
        <div class="wc-order-preview-custom-note">
					<ul class="order_notes">
						<?php
                if ( $qr_code_link ) {
									?>
										<h2 class="order-note">Order QR Code:</h2>
                    <img src="<?php echo esc_url( $qr_code_link ); ?>" alt="QR Code" class="armada-qr-code" />
									<?php
                } else {
                    ?>
                    <li class="no-items"><?php esc_html_e( 'There are no notes yet.', 'armada-delivery-for-woocommerce' ); ?></li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
    <?php

    $order_notes = ob_get_clean();  

    $data['order_notes'] = $order_notes;

    return $data;
	}

	/**
	 * Function to add QR code to order preview.
	 * 
	 * @return void
	 */
	function add_qr_code_to_preview_data() {
		?> {{{data.order_notes}}} <?php
	}
	
	/**
	 * AJAX handler to get the QR code link for an order.
	 */
	public function ajax_get_qr_code() {
		// Check nonce
		check_ajax_referer('armada-get-qr-code', 'security');
		
		// Check permissions
		if (!current_user_can('edit_shop_orders')) {
			wp_send_json_error(array('message' => __('You do not have permission to edit orders', 'armada-delivery-for-woocommerce')));
			return;
		}
		
		// Get the order ID
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		
		if (!$order_id) {
			wp_send_json_error(array('message' => __('Invalid order ID', 'armada-delivery-for-woocommerce')));
			return;
		}
		
		// Get the order
		$order = wc_get_order($order_id);
		
		if (!$order) {
			wp_send_json_error(array('message' => __('Invalid order', 'armada-delivery-for-woocommerce')));
			return;
		}
		
		// Get the QR code link
		$qr_code_link = $order->get_meta('_armada_qr_code_link');
		
		// Send the response
		wp_send_json_success(array(
			'qr_code_link' => $qr_code_link
		));
	}

	/**
	 * Add Send Delivery action to order actions.
	 *
	 * @param array    $actions Order actions.
	 * @param WC_Order $order   Order object.
	 * @return array
	 */
	public function add_send_delivery_order_action( $actions, $order ) {
		// Get the delivery code from order meta
		$delivery_code = $order->get_meta('_armada_delivery_code');
		
		// Only show if order is "Processing" and doesn't have a delivery code
		if ( empty($delivery_code) ) {
			$actions['armadefo_send_delivery'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin.php?action=armadefo_send_delivery&order_id=' . $order->get_id() ), 'armadefo_send_delivery' ),
				'name'   => __( 'Ship', 'armada-delivery-for-woocommerce' ),
				'action' => 'armadefo_send_delivery',
			);
		}

		return $actions;
	}

	/**
	 * Process the Send Delivery action.
	 *
	 * @return void
  */
	public function process_send_delivery_action() {
		// Check if we have the required data
		if ( ! isset( $_GET['order_id'] ) ) {
			wp_die( esc_html__( 'Order ID is missing', 'armada-delivery-for-woocommerce' ) );
		}

		// Verify nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'armadefo_send_delivery' ) ) {
			wp_die( esc_html__( 'Security check failed', 'armada-delivery-for-woocommerce' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'You do not have permission to edit orders', 'armada-delivery-for-woocommerce' ) );
		}

		$order_id = absint( $_GET['order_id'] );
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wp_die( esc_html__( 'Invalid order', 'armada-delivery-for-woocommerce' ) );
		}

		// Get order data
		$order_data = $order->get_data();
		$shipping = $order_data['shipping'];
		
		// Determine payment type
		$payment_type = 'cod';
		if ($order->get_payment_method() === 'paid') {
			$payment_type = 'paid';
		}
		
		// Prepare delivery data for API
		$delivery_data = array(
			'platformName' => 'woocommerce',
			'platformData' => array(
				'orderId' => $order->get_order_number(),
				'name' => $shipping['first_name'] . ' ' . $shipping['last_name'],
				'phone' => $shipping['phone'],
				'firstLine' => $shipping['address_1'],
				'amount' => $order->get_total(),
				'paymentType' => $payment_type
			)
		);
		
		// Send delivery request to Armada API
		$response = $this->api_client->create_delivery($delivery_data);
		
		// Check if the request was successful
		if (is_wp_error($response)) {
			// Log the error
			error_log('Armada API Error: ' . $response->get_error_message());
			
			// Add an order note with the error
			$order->add_order_note(sprintf(__('Failed to send delivery request to Armada: %s', 'armada-delivery-for-woocommerce'), $response->get_error_message()));
		} else {
			// Store all relevant delivery information from the API response
			$order->update_meta_data('_armada_delivery_code', isset($response['code']) ? $response['code'] : '');
			$order->update_meta_data('_armada_delivery_fee', isset($response['deliveryFee']) ? $response['deliveryFee'] : '');
			$order->update_meta_data('_armada_customer_address', isset($response['customerAddress']) ? $response['customerAddress'] : '');
			
			// Store location data if available
			if (isset($response['customerLocation'])) {
				$order->update_meta_data('_armada_customer_latitude', $response['customerLocation']['latitude']);
				$order->update_meta_data('_armada_customer_longitude', $response['customerLocation']['longitude']);
			}
			
			// Store driver information if available
			if (isset($response['driver'])) {
				$order->update_meta_data('_armada_driver_name', $response['driver']['name']);
				$order->update_meta_data('_armada_driver_phone', $response['driver']['phoneNumber']);
			}
			
			// Store tracking information
			$order->update_meta_data('_armada_tracking_link', isset($response['trackingLink']) ? $response['trackingLink'] : '');
			$order->update_meta_data('_armada_qr_code_link', isset($response['qrCodeLink']) ? $response['qrCodeLink'] : '');
			$order->update_meta_data('_armada_order_created_at', isset($response['orderCreatedAt']) ? $response['orderCreatedAt'] : '');
			$order->update_meta_data('_armada_order_status', isset($response['orderStatus']) ? $response['orderStatus'] : '');
			$order->update_meta_data('_armada_estimated_distance', isset($response['estimatedDistance']) ? $response['estimatedDistance'] : '');
			$order->update_meta_data('_armada_estimated_duration', isset($response['estimatedDuration']) ? $response['estimatedDuration'] : '');
			
			// Save all meta data
			$order->save();
			
			// Change order status to shipping
			$order->update_status('shipping', __('Order marked as shipping via Armada Plugin.', 'armada-delivery-for-woocommerce'));
			
			// Add an order note with the delivery details
			$note = sprintf(
				__('Delivery request sent via Armada Plugin. Delivery Code: %s', 'armada-delivery-for-woocommerce'),
				isset($response['code']) ? $response['code'] : 'N/A'
			);
			
			// Add tracking link if available
			if (isset($response['trackingLink']) && !empty($response['trackingLink'])) {
				$note .= "\n" . sprintf(__('Tracking Link: %s', 'armada-delivery-for-woocommerce'), $response['trackingLink']);
			}
			
			// Add driver info if available
			if (isset($response['driver']) && isset($response['driver']['name'])) {
				$note .= "\n" . sprintf(__('Driver: %s (%s)', 'armada-delivery-for-woocommerce'), $response['driver']['name'], $response['driver']['phoneNumber']);
			}
			
			$order->add_order_note($note);
		}

		// Redirect back to the orders page
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=shop_order' ) );
		exit;
	}

	/**
	 * Enqueue admin styles for order actions.
	 *
	 * @param string $hook_suffix The current admin page.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook_suffix ) {
		// Only load on WooCommerce order pages
		if ( ! in_array( $hook_suffix, array( 'edit.php', 'post.php' ), true ) ) {
			return;
		}
		
		// Check if we're on a WooCommerce order page
		global $post_type;
		if ( 'shop_order' !== $post_type ) {
			return;
		}
		
		// Define the CSS
		$css = '
			.wc-action-button-armadefo_send_delivery::after { 
				font-family: woocommerce !important;  
				content: "\e01a" !important; 
			}
			
			/* Order preview container styles */
			.wc-order-preview-order-note-container {
				padding: 20px;
			}
			
			/* QR Code styles */
			.armada-qr-code-container {
				text-align: center;
				margin: 10px 0;
			}
			
			.armada-qr-code-container img {
				max-width: 100%;
				height: auto;
				display: block;
				margin: 0 auto;
			}
		';
		
		// Add inline styles to the admin area
		wp_add_inline_style( 'woocommerce_admin_styles', $css );
	}	
}
