<?php
/**
 * This file has functions that modify email functionality.
 *
 * @package clipkart-customization
 */

/**
 * Include Pickup Details in Order Emails (Backend & Customer Email).
 *
 * @param WC_Order $order        Order object.
 * @param bool     $sent_to_admin Whether email is sent to admin.
 * @param bool     $plain_text    Whether email is plain text.
 */
function custom_checkout_fields_esc_html_email( $order, $sent_to_admin = false, $plain_text = false ) {
	unset( $sent_to_admin, $plain_text );

	$delivery_method = get_post_meta( $order->get_id(), '_delivery_method', true );
	$pickup_store    = get_post_meta( $order->get_id(), '_pickup_store', true );
	$pickup_date     = get_post_meta( $order->get_id(), '_pickup_date', true );
	$store_address   = get_post_meta( $order->get_id(), '_store_address', true );

	$delivery_method_display = ( 'local_pickup' === $delivery_method ) ? 'Local Pickup' : ucfirst( $delivery_method );

	// Generate Google Maps search link.
	$map_link = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $store_address );

	// Generate Static Map Image URL (Replace YOUR_API_KEY with a valid Google Maps API key).
	$google_maps_api_key = get_option( 'google_maps_api_key' );
	$static_map_url      = 'https://maps.googleapis.com/maps/api/staticmap?center=' . rawurlencode( $store_address ) .
						'&zoom=15&size=600x300&maptype=roadmap&markers=color:red%7C' . rawurlencode( $store_address ) .
						'&key=' . $google_maps_api_key;
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
				<tr>
					<th style="text-align: left; padding: 5px; border: 1px solid #ddd;"><?php esc_html_e( 'Store Location', 'clipkart-customization' ); ?></th>
					<td style="padding: 5px; border: 1px solid #ddd;">
						<a href="<?php echo esc_url( $map_link ); ?>" target="_blank">
							<img src="<?php echo esc_url( $static_map_url ); ?>" alt="Store Location Map" style="max-width: 100%; border-radius: 8px;">
						</a>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<br>
	<?php
}
add_action( 'woocommerce_email_order_meta', 'custom_checkout_fields_esc_html_email', 10, 3 );

/**
 * Schedule a pickup reminder email when an order is placed.
 *
 * @param int $order_id The order ID.
 */
function schedule_pickup_reminder_on_order( $order_id ) {
	if ( empty( $order_id ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$delivery_method = get_post_meta( $order_id, '_delivery_method', true );
	$pickup_date     = get_post_meta( $order_id, '_pickup_date', true );

	// Only schedule if delivery method is local pickup and pickup date exists.
	if ( 'local_pickup' !== $delivery_method || empty( $pickup_date ) ) {
		return;
	}

	// Get today's and tomorrow's date.
	$today    = gmdate( 'Y-m-d' );
	$tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day' ) );

	// If the pickup date is tomorrow, send the reminder immediately.
	if ( $pickup_date === $tomorrow ) {
		send_pickup_reminder_email( $order_id );
		return;
	}

	// Get order creation time in timestamp.
	$order_time = get_post_time( 'U', true, $order_id );

	// Calculate reminder time (exactly one day before pickup at the same order time).
	$reminder_time = strtotime( '-1 day', strtotime( $pickup_date ) );
	$reminder_time = strtotime( gmdate( 'Y-m-d', $reminder_time ) . ' ' . gmdate( 'H:i:s', $order_time ) );

	// Ensure reminder is not in the past before scheduling.
	if ( $reminder_time > time() ) {
		// Prevent duplicate scheduling.
		$existing_event = wp_next_scheduled( 'send_pickup_reminder_email_hook', array( $order_id ) );
		if ( ! $existing_event ) {
			wp_schedule_single_event( $reminder_time, 'send_pickup_reminder_email_hook', array( $order_id ) );
		}
	}
}
add_action( 'woocommerce_thankyou', 'schedule_pickup_reminder_on_order' );

/**
 * Send the pickup reminder email.
 *
 * @param int $order_id The order ID.
 */
function send_pickup_reminder_email( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$pickup_store   = get_post_meta( $order_id, '_pickup_store', true );
	$pickup_date    = get_post_meta( $order_id, '_pickup_date', true );
	$store_address  = get_post_meta( $order_id, '_store_address', true );
	$customer_email = $order->get_billing_email();

	// Email subject.
	$subject_prefix = esc_html__( 'Reminder: Pickup Your Order Tomorrow - ', 'clipkart-customization' );
	$subject        = $subject_prefix . esc_html( $pickup_date );

	// Email message.
	$message  = esc_html__( "Dear Customer,\n\nThis is a friendly reminder that your order is scheduled for pickup on: ", 'clipkart-customization' );
	$message .= esc_html( $pickup_date ) . "\n\n";
	$message .= esc_html__( 'Pickup Store: ', 'clipkart-customization' ) . esc_html( $pickup_store ) . "\n";
	$message .= esc_html__( 'Store Address: ', 'clipkart-customization' ) . esc_html( $store_address ) . "\n\n";
	$message .= esc_html__( 'Thank you for shopping with us!', 'clipkart-customization' );

	wp_mail( sanitize_email( $customer_email ), $subject, $message );
}
// Hook the reminder function to the scheduled event.
add_action( 'send_pickup_reminder_email_hook', 'send_pickup_reminder_email', 10, 1 );
