<?php
/**
 * Plugin Name: Armada Delivery For WooCommerce
 * Required Plugins: woocommerce
 * Description: A WooCommerce extension that integrates with Armada Delivery service, allowing merchants to easily ship orders, track deliveries, and manage shipping information.
 * Version: 0.1.0
 * Author: Armada Tech Team
 * Author URI: https://www.armadadelivery.com
 * Text Domain: armada-delivery-for-woocommerce
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ARMADEFO_MAIN_PLUGIN_FILE' ) ) {
	define( 'ARMADEFO_MAIN_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( __FILE__ ) . '/includes/admin/setup.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/admin/api-settings.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/admin/order-actions.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/admin/order-statuses.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/api/api-client.php';

use ARMADEFO\Admin\Setup;
use ARMADEFO\Admin\ApiSettings;
use ARMADEFO\Admin\OrderActions;
use ARMADEFO\Admin\OrderStatuses;
use ARMADEFO\API\ApiClient;

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function armadefo_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Armada Plugin requires WooCommerce to be installed and active. You can download %s here.', 'armada-delivery-for-woocommerce' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( __FILE__, 'armadefo_activate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function armadefo_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'armadefo_missing_wc_notice' );
		return;
	}
}

if ( ! class_exists( 'ARMADEFO_Main' ) ) :
	/**
	 * The ARMADEFO_Main class.
	 */
	class ARMADEFO_Main {
		/**
		 * This class instance.
		 *
		 * @var \ARMADEFO_Main single instance of this class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( is_admin() ) {
				new Setup();
				new ApiSettings();
				new OrderActions();
				new OrderStatuses();
			}
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'armada-delivery-for-woocommerce' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'armada-delivery-for-woocommerce' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \ARMADEFO_Main
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;

add_action( 'plugins_loaded', 'armadefo_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function armadefo_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'armadefo_missing_wc_notice' );
		return;
	}

	ARMADEFO_Main::instance();
}
