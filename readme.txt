=== Armada Delivery For WooCommerce ===
Contributors: armadateam
Tags: woocommerce, shipping, delivery, tracking, ecommerce
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 0.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce extension that integrates with Armada Delivery service, allowing merchants to easily ship orders, track deliveries, and manage shipping information.

== Description ==

Armada Delivery For WooCommerce is a powerful extension that seamlessly integrates your WooCommerce store with the Armada Delivery service. This plugin enables merchants to efficiently manage their shipping operations, track deliveries in real-time, and provide customers with a superior delivery experience.

= Key Features =

* **One-Click Shipping**: Ship orders with a single click directly from your WooCommerce admin panel
* **Real-Time Tracking**: Provide customers with real-time tracking information for their deliveries
* **QR Code Generation**: Automatically generate QR codes for easy package identification
* **Custom Order Status**: Adds a "Shipping" order status to better track order fulfillment
* **Driver Information**: Access driver details including name and contact information
* **Delivery Analytics**: View estimated distance and duration for deliveries
* **Payment Type Support**: Supports both Cash on Delivery (COD) and pre-paid orders

= Benefits =

* Streamline your shipping workflow
* Reduce manual data entry and errors
* Improve customer satisfaction with transparent delivery tracking
* Save time with automated shipping processes
* Gain insights into delivery performance

== Installation ==

= Minimum Requirements =

* WordPress 5.0 or greater
* WooCommerce 5.0 or greater
* PHP version 7.2 or greater
* MySQL version 5.6 or greater

= Automatic Installation =

1. Log in to your WordPress dashboard
2. Navigate to Plugins > Add New
3. Search for "Armada Delivery For WooCommerce"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin from the WordPress plugin repository
2. Log in to your WordPress dashboard
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin through the 'Plugins' menu in WordPress

= Configuration =

1. After activation, go to WooCommerce > Settings > Armada Delivery
2. Enter your Armada API key (you can obtain this from your Armada account dashboard)
3. Save your settings
4. You're ready to start shipping with Armada!

== Frequently Asked Questions ==

= Do I need an Armada account to use this plugin? =

Yes, you need to sign up for an Armada account at [armadadelivery.com](https://www.armadadelivery.com) to obtain an API key.

= How do I ship an order with Armada? =

Once the plugin is configured, you'll see a "Ship" button in your WooCommerce orders list for eligible orders. Click this button to create a shipping request with Armada.

= Can I track shipments? =

Yes, once an order is shipped with Armada, a tracking link is automatically generated and stored with the order. This link can be shared with customers.

= Does this plugin support international shipping? =

The Armada Delivery service coverage depends on your region. Please check with Armada for specific coverage details.

= What payment methods are supported? =

The plugin supports both Cash on Delivery (COD) and pre-paid orders.

== Screenshots ==

1. Armada API settings page
2. Order actions with Armada shipping button
3. Order details with tracking information
4. QR code for package identification

== Changelog ==

= 0.1.0 =
* Initial release

== Upgrade Notice ==

= 0.1.0 =
Initial release of the Armada Delivery For WooCommerce plugin.

== Developer Resources ==

= Prerequisites =
* [NPM](https://www.npmjs.com/)
* [Composer](https://getcomposer.org/download/)
* [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

= Development Setup =
```
npm install
npm run build
wp-env start
```

Visit the admin page at http://localhost:8888/wp-admin/admin.php?page=wc-admin&path=%2Fexample
