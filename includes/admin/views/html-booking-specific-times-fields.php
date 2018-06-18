<?php 
	$start = $specific_time['start'];
	$intervals = $specific_time['intervals'];
	$prices = $specific_time['prices'];
?>
<tr>
	<td>
		<div class="wc_booking_specific_times_start">
			<input type="time" name="wc_booking_specific_time_start[]" id="wc_booking_specific_time_start"
			value="<?php if($start != null){ echo $start; } ?>"
			placeholder="HH:MM" required />
		</div>
	</td>
	<td>
		<div class="wc_booking_specific_times_intervals">
			<input type="text" name="wc_booking_specific_times_intervals[]" id="wc_booking_specific_times_intervals" 
			value="<?php if($intervals != null){ echo $intervals; } ?>"
			placeholder='Ex: "2,4,8"' required />
		</div>
	</td>
	<td>
		<div class="wc_booking_specific_times_rel_prices">
			<input type="text" name="wc_booking_specific_time_rel_prices[]" id="wc_booking_specific_time_rel_prices" 
			value="<?php if($prices != null){ echo $prices; } ?>"
			placeholder='Ex: "210.00,295.00,480.00"' required />
		</div>
	</td>
	<td class="remove">&nbsp;</td>
</tr>
