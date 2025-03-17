<?php
/**
 * Manage Stores Admin Page and Processing.
 *
 * @package YourThemeOrPlugin
 */

add_action( 'admin_menu', 'create_manage_stores_menu' );
/**
 * Create Admin Menu for Managing Stores.
 */
function create_manage_stores_menu() {
	add_menu_page(
		esc_html__( 'Manage Stores', 'textdomain' ), // Page Title.
		esc_html__( 'Manage Stores', 'textdomain' ), // Menu Title.
		'manage_options',                           // Capability.
		'manage_stores',                            // Menu Slug.
		'manage_stores_page',                       // Function to display the page.
		'dashicons-store',                          // Icon.
		25                                          // Position.
	);
}

/**
 * Output the Manage Stores admin page.
 */
function manage_stores_page() {
	$stores     = get_option( 'custom_stores', array() );
	$edit_index = null;
	if ( isset( $_GET['edit'] ) ) {
		// Sanitize the nonce.
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( wp_verify_nonce( $nonce, 'manage_stores_edit_' . absint( $_GET['edit'] ) ) ) {
			$edit_index = absint( wp_unslash( $_GET['edit'] ) );
		}
	}
	$edit_store = ( null !== $edit_index && isset( $stores[ $edit_index ] ) ) ? $stores[ $edit_index ] : null;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Manage Stores', 'textdomain' ); ?></h1>
		<form method="post" action="">
			<?php wp_nonce_field( 'manage_stores_action', 'manage_stores_nonce' ); ?>
			<input type="hidden" name="edit_index" value="<?php echo esc_attr( $edit_store ? $edit_index : '' ); ?>">
			<table class="form-table">
				<tr>
					<th>
						<label for="store_name"><?php esc_html_e( 'Store Name', 'textdomain' ); ?></label>
					</th>
					<td>
						<input type="text" id="store_name" name="store_name" value="<?php echo esc_attr( $edit_store['name'] ?? '' ); ?>" required>
					</td>
				</tr>
				<tr>
					<th>
						<label for="store_address"><?php esc_html_e( 'Store Address', 'textdomain' ); ?></label>
					</th>
					<td>
						<textarea id="store_address" name="store_address" required><?php echo esc_textarea( $edit_store['address'] ?? '' ); ?></textarea>
					</td>
				</tr>
			</table>
			<?php
			submit_button( $edit_store ? __( 'Update Store', 'textdomain' ) : __( 'Add Store', 'textdomain' ) );
			?>
		</form>
		<h2><?php esc_html_e( 'Existing Stores', 'textdomain' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'textdomain' ); ?></th>
					<th><?php esc_html_e( 'Address', 'textdomain' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'textdomain' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $stores ) ) {
					foreach ( $stores as $index => $store ) {
						$edit_url   = add_query_arg(
							array(
								'page'     => 'manage_stores',
								'edit'     => $index,
								'_wpnonce' => wp_create_nonce( 'manage_stores_edit_' . $index ),
							),
							admin_url( 'admin.php' )
						);
						$delete_url = add_query_arg(
							array(
								'page'     => 'manage_stores',
								'delete'   => $index,
								'_wpnonce' => wp_create_nonce( 'manage_stores_delete_' . $index ),
							),
							admin_url( 'admin.php' )
						);
						?>
						<tr>
							<td><?php echo esc_html( $store['name'] ); ?></td>
							<td><?php echo esc_html( $store['address'] ); ?></td>
							<td>
								<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'textdomain' ); ?></a> |
								<a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this store?', 'textdomain' ); ?>');">
									<?php esc_html_e( 'Delete', 'textdomain' ); ?>
								</a>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="3"><?php esc_html_e( 'No stores added yet.', 'textdomain' ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}

add_action( 'admin_init', 'handle_store_submission' );
/**
 * Process store addition and editing.
 */
function handle_store_submission() {
	if ( isset( $_POST['store_name'] ) && isset( $_POST['store_address'] ) ) {
		// Verify nonce.
		if ( ! isset( $_POST['manage_stores_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['manage_stores_nonce'] ), 'manage_stores_action' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_die( esc_html__( 'Security check failed. Please try again.', 'textdomain' ) );
		}

		$stores    = get_option( 'custom_stores', array() );
		$new_store = array(
			'name'    => sanitize_text_field( wp_unslash( $_POST['store_name'] ) ),
			'address' => sanitize_textarea_field( wp_unslash( $_POST['store_address'] ) ),
		);

		$edit_index = isset( $_POST['edit_index'] ) && '' !== $_POST['edit_index'] ? (int) absint( wp_unslash( $_POST['edit_index'] ) ) : null;

		if ( null !== $edit_index && isset( $stores[ $edit_index ] ) ) {
			// Update existing store.
			$stores[ $edit_index ] = $new_store;
		} else {
			// Add new store.
			$stores[] = $new_store;
		}

		update_option( 'custom_stores', $stores );
		wp_safe_redirect( admin_url( 'admin.php?page=manage_stores' ) );
		exit;
	}
}

add_action( 'admin_init', 'handle_store_deletion' );
/**
 * Process store deletion.
 */
function handle_store_deletion() {
	if ( isset( $_GET['delete'] ) ) {
		// Sanitize and verify nonce.
		$nonce     = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$delete_id = absint( wp_unslash( $_GET['delete'] ) );
		if ( ! wp_verify_nonce( $nonce, 'manage_stores_delete_' . $delete_id ) ) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'textdomain' ) );
		}

		$stores = get_option( 'custom_stores', array() );
		unset( $stores[ $delete_id ] );
		update_option( 'custom_stores', array_values( $stores ) );
		wp_safe_redirect( admin_url( 'admin.php?page=manage_stores' ) );
		exit;
	}
}

