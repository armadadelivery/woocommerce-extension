<?php

namespace ArmadaPlugin\Admin;

/**
 * ArmadaPlugin Order Statuses Class
 */
class OrderStatuses {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register new status
		add_action( 'init', array( $this, 'register_shipping_order_status' ) );
		
		// Add to list of WC Order statuses
		add_filter( 'wc_order_statuses', array( $this, 'add_shipping_to_order_statuses' ) );
		
		// Add to list of WC valid order statuses for payment
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'add_shipping_to_valid_order_statuses' ) );
		
		// Add to list of WC valid order statuses for payment complete
		add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'add_shipping_to_valid_order_statuses' ) );
		
		// Add status after processing
		add_filter( 'wc_order_statuses_after_processing', array( $this, 'add_shipping_to_valid_order_statuses' ) );
	}

	/**
	 * Register new shipping order status
	 */
	public function register_shipping_order_status() {
		register_post_status( 'wc-shipping', array(
			'label'                     => _x( 'Shipping', 'Order status', 'armada-delivery-for-woocommerce' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Shipping <span class="count">(%s)</span>', 'Shipping <span class="count">(%s)</span>', 'armada-delivery-for-woocommerce' )
		) );
	}

	/**
	 * Add shipping to order statuses
	 *
	 * @param array $order_statuses Order statuses.
	 * @return array
	 */
	public function add_shipping_to_order_statuses( $order_statuses ) {
		$new_order_statuses = array();
		
		// Add new order status after processing
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			
			if ( 'wc-processing' === $key ) {
				$new_order_statuses['wc-shipping'] = _x( 'Shipping', 'Order status', 'armada-delivery-for-woocommerce' );
			}
		}
		
		return $new_order_statuses;
	}

	/**
	 * Add shipping to valid order statuses
	 *
	 * @param array $statuses Order statuses.
	 * @return array
	 */
	public function add_shipping_to_valid_order_statuses( $statuses ) {
		$statuses[] = 'shipping';
		return $statuses;
	}
}
