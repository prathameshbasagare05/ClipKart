<?php
/**
 * Plugin Name: Clipkart Customization
 * Description: This plugin is created to customize our Ecommerce Site
 * Version: 1.0.0
 * Author: Prathamesh Basagare
 * Text Domain: clipkart-customization
 * Domain Path: /languages
 *
 * @package clipkart-customization
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Include checkout fields customizations.
require_once plugin_dir_path( __FILE__ ) . 'includes/checkout-fields.php';


/**
 * Function to display custom checkout field.
 */
function clipkart_customization_enqueue_scripts() {
	// Only enqueue on the checkout page (and if blocks are enabled).

	/*
	 * Commented Code.

	*/
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_script(
			'pickup-date-block',
			plugin_dir_url( __FILE__ ) . 'build/index.js',
			array(
				'wp-plugins',
				'wp-element',
				'wp-data',
				'wp-i18n',
				'wc-blocks-checkout',
			),
			'1.0.0',
			true
		);
	}
}


add_action( 'wp_enqueue_scripts', 'clipkart_customization_enqueue_scripts' );
