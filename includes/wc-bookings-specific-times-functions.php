<?php

/**
 * get_wc_booking_priority_explanation.
 *
 * @return string
 */
function get_wc_booking_specific_times_explanation() {

	$explanation = 
	'<div class="explanation">
		<ul>
			<li><strong>Start Time: </strong> A time at which a booking can begin</li>
			<li><strong>Allowed Hour Intervals: </strong>List hour intervals separated by a comma. Ex: "2,4,8"</li>
			<li><strong>Relative Interval Prices: </strong>List prices relative to hour intervals separated by a comma. Ex: "210.00,295.00,480.00"</li>
		</ul>
	</div>';

	return __( $explanation, 'woocommerce-bookings' );
}

function get_specific_times($post_id){
	$post_meta = get_post_meta($post_id)['_wc_booking_specific_times'];
	return unserialize($post_meta[0]);
}

function get_is_specific_times_enabled($post_id){
	$post_meta = get_post_meta($post_id)['_wc_booking_specific_times_enable'];
	return $post_meta[0];
}
