<?php
/**
 * Listing form
 */
defined( 'ABSPATH' ) || exit;

do_action( 'lsx_bd_before_listing_form' ); ?>

<div class="lsx-bd-my-account listings">
	<form class="woocommerce-EditAccountForm listing-form" enctype="multipart/form-data" action="" method="post" <?php do_action( 'lsx_bd_listing_form_tag' ); ?> >

		<?php do_action( 'lsx_bd_listing_form_start' ); ?>

		<?php
		$listing_id = get_query_var( 'edit-listing', false );
		$sections   = \lsx\business_directory\includes\get_listing_form_fields();
		$all_values = \lsx\business_directory\includes\get_listing_form_field_values( $sections, $listing_id );
		$defaults   = \lsx\business_directory\includes\get_listing_form_field_defaults();

		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section_key => $section_values ) {
				$class = str_replace( '_', '-', $section_key );
				?>
				<fieldset class="<?php echo esc_attr( $class ); ?>-fieldset">
					<legend><?php echo esc_attr( $section_values['label'] ); ?></legend>
					<?php
					if ( ! empty( $section_values['fields'] ) ) {
						foreach ( $section_values['fields'] as $field_key => $field_args ) {
							if ( 'lsx_bd_post_content' === $field_key ) {
								$eitor_settings = array(
									'wpautop'       => true,
									'media_buttons' => false,
								);
								wp_editor( $all_values[ $field_key ], $field_key, $eitor_settings );
							} else {
								// This adds the handle of the image field.
								$field_args = wp_parse_args( $field_args, $defaults );
								woocommerce_form_field(
									$field_key,
									$field_args,
									$all_values[ $field_key ]
								);
								if ( false !== $listing_id && 'image' === $field_args['type'] ) {
									?>
									<p>
										<img src="<?php echo esc_url( lsx_bd_get_thumbnail_wrapped( $listing_id, 'lsx-thumbnail-wide' ) ); ?>">
									</p>
									<?php
								}
							}
						}
					}
					?>
				</fieldset>
				<?php
			}
		}
		?>

		<?php do_action( 'lsx_bd_listing_form' ); ?>

		<p>
			<?php wp_nonce_field( 'lsx_bd_add_listing', 'lsx-bd-add-listing-nonce' ); ?>
			<button type="submit" class="woocommerce-Button button" name="save_listing_details" value="<?php esc_attr_e( 'Save', 'lsx-business-directory' ); ?>"><?php esc_html_e( 'Save', 'lsx-business-directory' ); ?></button>

			<?php
				$preview_action = wc_get_endpoint_url( lsx_bd_get_option( 'translations_listings_preview_endpoint', 'preview-listing' ) . '/' . $listing_id . '/', '', wc_get_page_permalink( 'myaccount' ) );
			?>
			<button
				type="submit"
				class="woocommerce-Button button"
				name="preview_listing_details"
				value="<?php esc_attr_e( 'Preview', 'lsx-business-directory' ); ?>"
				formtarget="_blank"
				formaction="<?php esc_attr_e( $preview_action ); ?>">
					<?php esc_html_e( 'Preview', 'lsx-business-directory' ); ?>
			</button>

			<?php
			if ( false !== $listing_id && '' !== $listing_id ) {
				?>
				<input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>" />
				<input type="hidden" name="action" value="edit_listing_details" />
				<?php
			} else {
				?>
				<input type="hidden" name="action" value="save_listing_details" />
				<?php
			}
			?>
		</p>

		<?php do_action( 'lsx_bd_listing_form_end' ); ?>
	</form>
</div>


<?php do_action( 'lsx_bd_after_listing_form' ); ?>
