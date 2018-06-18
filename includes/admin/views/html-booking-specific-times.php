<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $woocommerce, $post;

  $bookable_product = null;
  $product = wc_get_product( $post->ID );
  if(get_class($product) == 'WC_Product_Booking') {
  	$bookable_product = $product;
  }
?>
<div id="bookings_specific_times" class="panel woocommerce_options_panel">
	<div class="options_group info">
		<h3>WooCommerce Booking - Specific Times</h3>
		<p>This modification of WooCommerce Bookings allows the admin to create specific times for a bookable product to be booked with the following configurations:</p>
		<ul>
			<li>The specific start time of when a booking is available</li>
			<li>The specific hour intervals allowed to be booked associated with that start time</li>
			<li>The specific prices associated with that start time's hour intervals (as opposed to a multiplier of a base price per block of time)</li>
		</ul>
		<p><strong>Important!:</strong> If this feature is enabled and has Specific Times associated with it, no other times or prices will be available to users for this product except those specified in this panel.</p>

		<?php
			// Enable Specific Times? Checkbox
			woocommerce_wp_checkbox( 
			array(
            	'id'          => '_wc_booking_specific_times_enable',
            	'label'       => __( 'Enable Specific Times?', 'woocommerce-bookings' ),
            	'description' => __( 'Check this box if you would like to enable specific times for this product. Understand that if this is enabled and specific times have been saved, ONLY those specific times will be available to customers.', 'woocommerce-bookings' )
            	)
			);
		?>
	</div>
	<div class="options_group">
		<div class="table_grid">
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Start Time', 'woocommerce-bookings' ); ?></th>
						<th><?php esc_html_e( 'Allowed Hour Intervals', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php _e( 'List hour intervals separated by a comma', 'woocommerce-bookings' ); ?>">[?]</a></th>
						<th><?php esc_html_e( 'Relative Interval Prices', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php _e( 'List prices relative to hour intervals separated by a comma', 'woocommerce-bookings' ); ?>">[?]</a></th>
						<th class="remove" width="1%">&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="6">
							<a href="#" class="button add_row" data-row="<?php
							ob_start();
							include( 'html-booking-specific-times-fields.php' );
							$html = ob_get_clean();
							echo esc_attr( $html );
							?>"><?php _e( 'Add Specific Time', 'woocommerce-bookings' ); ?></a>
							<span class="description"><?php echo get_wc_booking_specific_times_explanation(); ?></span>
						</th>
					</tr>
				</tfoot>
				<tbody id="specific_times_rows">
					<?php
						$specific_times = get_specific_times($bookable_product->get_id());

						if ( ! empty( $specific_times ) && is_array( $specific_times ) ) {
							foreach ( $specific_times as $specific_time ) {
								include( 'html-booking-specific-times-fields.php' );
							}
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
