/**
 * pickup-date-block.js
 *
 * Adds a pickup date field to the block-based checkout.
 */

import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';


const ExperimentalOrderLocalPickupPackages =
	window.wc &&
	window.wc.blocksCheckout &&
	window.wc.blocksCheckout.ExperimentalOrderLocalPickupPackages
		? window.wc.blocksCheckout.ExperimentalOrderLocalPickupPackages
		: () => null; // Fallback to a null component if not available

// Similarly, get the cart store key from the global variable if available.
const CART_STORE_KEY =
	window.wc && window.wc.blockData && window.wc.blockData.STORE_KEY
		? window.wc.blockData.STORE_KEY
		: 'wc/store/cart';

const PickupDateBlockExtension = () => {
	const [ pickupDate, setPickupDate ] = useState( '' );

	// Read cart data from WooCommerce blocks store
	const cartData = useSelect( ( select ) => select( CART_STORE_KEY ).getCartData(), [] );
	// A dispatcher to set custom extension data in the cart
	const { setCartExtensionData } = useDispatch( CART_STORE_KEY );

	const handleDateChange = ( event ) => {
		const newDate = event.target.value;
		setPickupDate( newDate );

		// Save custom extension data so it persists in the cart
		setCartExtensionData( 'clipkart-customization', {
			pickup_date: newDate,
		} );
	};

	return (
		<ExperimentalOrderLocalPickupPackages>
			<div className="pickup-date-field">
				<label htmlFor="pickup-date">
					{ __( 'Pickup Date', 'clipkart-customization' ) }
				</label>
				<input
					type="date"
					id="pickup-date"
					value={ pickupDate }
					onChange={ handleDateChange }
					min={ new Date().toISOString().split( 'T' )[0] }
					max={ new Date( Date.now() + 7 * 24 * 60 * 60 * 1000 )
						.toISOString()
						.split( 'T' )[0] }
				/>
			</div>
		</ExperimentalOrderLocalPickupPackages>
	);
};

// Register the extension so it appears in the block-based checkout
registerPlugin( 'pickup-date-block-extension', {
	scope: 'woocommerce-checkout',
	render: PickupDateBlockExtension,
} );
