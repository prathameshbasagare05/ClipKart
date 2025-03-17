<?php
/**
 * This file contains functions to display checkout fields on the WooCommerce Checkout Page.
 *
 * @package clipkart-customization
 */

add_action( 'woocommerce_before_order_notes', 'custom_checkout_fields' );
/**
 * Add custom checkout fields for delivery method.
 */
function custom_checkout_fields() {
	?>
	<h3><?php esc_html_e( 'Delivery Method', 'clipkart-customization' ); ?></h3>

	<p class="form-row form-row-wide">
		<label>
			<input type="radio" name="delivery_method" value="shipping" checked> <?php esc_html_e( 'Shipping', 'clipkart-customization' ); ?>
		</label>
		<label>
			<input type="radio" name="delivery_method" value="local_pickup"> <?php esc_html_e( 'Local Pickup', 'clipkart-customization' ); ?>
		</label>
	</p>

	<?php
	// Add a nonce field for security.
	wp_nonce_field( 'custom_checkout_action', 'custom_checkout_nonce' );

	// Retrieve the list of stores from the custom_stores option.
	$stores = get_option( 'custom_stores', array() );
	// Prepare a mapping of store names to addresses for use in JavaScript.
	$store_data = array();
	foreach ( $stores as $store ) {
		if ( isset( $store['name'] ) && isset( $store['address'] ) ) {
			$store_data[ $store['name'] ] = $store['address'];
		}
	}

	?>

	<p class="form-row form-row-wide" id="pickup_store_field" style="display: none;">
		<label for="pickup_store"><?php esc_html_e( 'Select Store', 'clipkart-customization' ); ?> <span class="required">*</span></label>
		<select name="pickup_store" id="pickup_store">
			<option value=""><?php esc_html_e( 'Select a Store', 'clipkart-customization' ); ?></option>
			<?php if ( ! empty( $stores ) ) : ?>
				<?php foreach ( $stores as $store ) : ?>
					<option value="<?php echo esc_attr( $store['name'] ); ?>">
						<?php echo esc_html( $store['name'] ); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
	</p>

	<p class="form-row form-row-wide" id="store_address_display" style="display: none;">
		<label><?php esc_html_e( 'Store Address:', 'clipkart-customization' ); ?></label>
		<span id="store_address_text"></span>
	</p>

	<p class="form-row form-row-wide" id="pickup_date_field" style="display: none;">
		<label for="pickup_date"><?php esc_html_e( 'Pickup Date', 'clipkart-customization' ); ?> <span class="required">*</span></label>
		<input type="date" name="pickup_date" id="pickup_date" min="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 days' ) ) ); ?>" max="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+7 days' ) ) ); ?>">
	</p>

	<script>
	jQuery( document ).ready( function( $ ) {
		var storeData = <?php echo wp_json_encode( $store_data ); ?>;

		function toggleFields() {
			if ( 'shipping' === $( 'input[name=delivery_method]:checked' ).val() ) {
				$( '#pickup_store_field, #pickup_date_field, #store_address_display' ).hide();
				$( '#pickup_store, #pickup_date' ).prop( 'required', false );
				$( '#billing_address_1_field, #shipping_address_1_field' ).show();
			} else {
				$( '#pickup_store_field, #pickup_date_field' ).show();
				$( '#pickup_store, #pickup_date' ).prop( 'required', true );
				$( '#billing_address_1_field, #shipping_address_1_field' ).hide();
			}
		}

		$( 'input[name=delivery_method]' ).change( toggleFields );
		toggleFields();

		$( '#pickup_store' ).change( function() {
			var selectedStore = $( this ).val();
			if ( selectedStore && storeData[selectedStore] ) {
				$( '#store_address_text' ).text( storeData[selectedStore] );
				$( '#store_address_display' ).show();
			} else {
				$( '#store_address_display' ).hide();
				$( '#store_address_text' ).text( '' );
			}
		} );
	} );
	</script>
	<?php
}


add_action( 'woocommerce_checkout_process', 'validate_custom_checkout_fields' );
/**
 * Validate custom checkout fields.
 */
function validate_custom_checkout_fields() {
	if ( isset( $_POST['delivery_method'] ) && 'local_pickup' === $_POST['delivery_method'] ) {
		// Verify the custom nonce.
		if ( ! isset( $_POST['custom_checkout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['custom_checkout_nonce'] ) ), 'custom_checkout_action' ) ) {
			wc_add_notice( esc_html__( 'Security check failed. Please try again.', 'clipkart-customization' ), 'error' );
		}

		if ( empty( $_POST['pickup_store'] ) ) {
			wc_add_notice( esc_html__( 'Please select a pickup store.', 'clipkart-customization' ), 'error' );
		}
		if ( empty( $_POST['pickup_date'] ) ) {
			wc_add_notice( esc_html__( 'Please select a pickup date.', 'clipkart-customization' ), 'error' );
		}
	}
}


add_action( 'woocommerce_checkout_update_order_meta', 'save_custom_checkout_fields' );
/**
 * Save custom checkout fields to order meta.
 *
 * @param int $order_id Order ID.
 */
function save_custom_checkout_fields( $order_id ) {
	if ( ! isset( $_POST['custom_checkout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['custom_checkout_nonce'] ) ), 'custom_checkout_action' ) ) {
		return;
	}

	if ( ! empty( $_POST['delivery_method'] ) ) {
		update_post_meta( $order_id, '_delivery_method', sanitize_text_field( wp_unslash( $_POST['delivery_method'] ) ) );
	}
	if ( ! empty( $_POST['pickup_store'] ) ) {
		update_post_meta( $order_id, '_pickup_store', sanitize_text_field( wp_unslash( $_POST['pickup_store'] ) ) );
	}
	if ( ! empty( $_POST['pickup_date'] ) ) {
		update_post_meta( $order_id, '_pickup_date', sanitize_text_field( wp_unslash( $_POST['pickup_date'] ) ) );
	}
}

/**
 * Retrieve store address based on store name.
 *
 * @param string $pickup_store Pickup store name.
 * @return string Store address.
 */
function get_store_address_by_name( $pickup_store ) {
	$store_address = '';
	$stores        = get_option( 'custom_stores', array() );
	if ( ! empty( $stores ) && $pickup_store ) {
		foreach ( $stores as $store ) {
			if ( isset( $store['name'] ) && $store['name'] === $pickup_store && isset( $store['address'] ) ) {
				$store_address = $store['address'];
				break;
			}
		}
	}
	return $store_address;
}
