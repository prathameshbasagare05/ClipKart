<?php
/**
 * This file contains Functions to modify admin order details.
 *
 * @package clipkart-customization
 */

add_action( 'woocommerce_admin_order_data_after_billing_address', 'display_custom_checkout_fields_admin', 10, 1 );

/**
 * Display Pickup Details in Admin Order Details (Backend).
 *
 * @param array $order Contains details about order.
 */
function display_custom_checkout_fields_admin( $order ) {
	$delivery_method = get_post_meta( $order->get_id(), '_delivery_method', true );
	$pickup_store    = get_post_meta( $order->get_id(), '_pickup_store', true );
	$pickup_date     = get_post_meta( $order->get_id(), '_pickup_date', true );
	$store_address   = get_post_meta( $order->get_id(), '_store_address', true );

	// Format the delivery method for display.
	$delivery_method_display = ( 'local_pickup' === $delivery_method ) ? 'Local Pickup' : ucfirst( $delivery_method );

	echo '<h3>Delivery Details</h3>';
	echo '<p><strong>Method:</strong> ' . esc_html( $delivery_method_display ) . '</p>';
	if ( 'local_pickup' === $delivery_method ) {
		echo '<p><strong>Pickup Store:</strong> ' . esc_html( $pickup_store ) . '</p>';
		echo '<p><strong>Store Address:</strong> ' . esc_html( $store_address ) . '</p>';
		echo '<p><strong>Pickup Date:</strong> ' . esc_html( $pickup_date ) . '</p>';
	}
	?>
	<?php
}
