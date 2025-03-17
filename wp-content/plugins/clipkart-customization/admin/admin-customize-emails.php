<?php
/**
 * This file has functions that modify email functionality.
 *
 * @package clipkart-customization
 */

add_action( 'woocommerce_email_order_meta', 'custom_checkout_fields_esc_html_email', 10, 3 );

/**
 * Include Pickup Details in Order Emails (Backend & Customer Email).
 *
 * @param WC_Order $order       Order object.
 * @param bool     $sent_to_admin Whether email is sent to admin.
 * @param bool     $plain_text  Whether email is plain text.
 */
function custom_checkout_fields_esc_html_email( $order, $sent_to_admin, $plain_text ) {
	$delivery_method = get_post_meta( $order->get_id(), '_delivery_method', true );
	$pickup_store    = get_post_meta( $order->get_id(), '_pickup_store', true );
	$pickup_date     = get_post_meta( $order->get_id(), '_pickup_date', true );
	$store_address   = get_store_address_by_name( $pickup_store );

	$delivery_method_display = ( 'local_pickup' === $delivery_method ) ? 'Local Pickup' : ucfirst( $delivery_method );

	?>
	<h3><?php esc_html_e( 'Delivery Details', 'clipkart-customization' ); ?></h3>
	<table style="width: 100%; border-collapse: collapse;">
		<tbody>
			<tr>
				<th style="text-align: left; padding: 5px; border: 1px solid #ddd;"><?php esc_html_e( 'Method', 'clipkart-customization' ); ?></th>
				<td style="padding: 5px; border: 1px solid #ddd;"><?php echo esc_html( $delivery_method_display ); ?></td>
			</tr>
			<?php if ( 'local_pickup' === $delivery_method ) : ?>
				<tr>
					<th style="text-align: left; padding: 5px; border: 1px solid #ddd;"><?php esc_html_e( 'Pickup Store', 'clipkart-customization' ); ?></th>
					<td style="padding: 5px; border: 1px solid #ddd;"><?php echo esc_html( $pickup_store ); ?></td>
				</tr>
				<tr>
					<th style="text-align: left; padding: 5px; border: 1px solid #ddd;"><?php esc_html_e( 'Store Address', 'clipkart-customization' ); ?></th>
					<td style="padding: 5px; border: 1px solid #ddd;"><?php echo esc_html( $store_address ); ?></td>
				</tr>
				<tr>
					<th style="text-align: left; padding: 5px; border: 1px solid #ddd;"><?php esc_html_e( 'Pickup Date', 'clipkart-customization' ); ?></th>
					<td style="padding: 5px; border: 1px solid #ddd;"><?php echo esc_html( $pickup_date ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<br>
	<?php
}
