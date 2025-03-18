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
require_once plugin_dir_path( __FILE__ ) . 'includes/user-order-details.php';

require_once plugin_dir_path( __FILE__ ) . 'admin/manage-store-menu.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-customize-emails.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-order-details.php';
