<?php
/**
 * Update order details pages.
 *
 * @package clipkart-customization
 */

add_action( 'woocommerce_order_details_after_order_table', 'display_custom_checkout_fields_account', 20 );

/**
 * Display Pickup Details on the Thank You and My Account Order Details Page (User Side, Table Format).
 *
 * @param WC_Order $order Order object.
 */
function display_custom_checkout_fields_account( $order ) {
	$order_id        = $order->get_id();
	$delivery_method = get_post_meta( $order_id, '_delivery_method', true );
	if ( 'local_pickup' === $delivery_method ) {
		$pickup_store            = get_post_meta( $order_id, '_pickup_store', true );
		$pickup_date             = get_post_meta( $order_id, '_pickup_date', true );
		$store_address           = get_post_meta( $order->get_id(), '_store_address', true );
		$delivery_method_display = 'Local Pickup';
		?>
		<h3><?php esc_html_e( 'Delivery Details', 'clipkart-customization' ); ?></h3>
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
			<tbody>
				<tr>
					<th><?php esc_html_e( 'Method', 'clipkart-customization' ); ?></th>
					<td><?php echo esc_html( $delivery_method_display ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Pickup Store', 'clipkart-customization' ); ?></th>
					<td><?php echo esc_html( $pickup_store ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Store Address', 'clipkart-customization' ); ?></th>
					<td><?php echo esc_html( $store_address ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Pickup Date', 'clipkart-customization' ); ?></th>
					<td><?php echo esc_html( $pickup_date ); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
