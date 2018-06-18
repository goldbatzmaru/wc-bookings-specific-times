<?php 

global $woocommerce, $post;

  $bookable_product = null;
  $product = wc_get_product( $post->ID );
  if(get_class($product) == 'WC_Product_Booking'): ?>
  	<style>
  		#bookings_specific_times .info {
			padding: 0 15px;
			margin-bottom: 15px;
			border-bottom: none;
		}

		#bookings_specific_times .info ul {
			font-size: 12px;
		    padding-left: 30px;
		    list-style: initial;
		}

		#bookings_specific_times .explanation {
			padding: 0 15px;
			margin: 15px 0;
			max-width: 500px;
		}

		#bookings_specific_times.woocommerce_options_panel input[type=text] {
			width: 100%;
		}

		#bookings_specific_times .table_grid table td.remove {
		    width: 16px!important;
			cursor: pointer;
			border: 1px solid #dfdfdf;
    		border-right: 0;
    		border-top: 0;
		    padding: 9px;
	        position: relative;
		}
		#bookings_specific_times .table_grid table td.remove::after{
			content: '✖';
			position: relative;
    		top: 5px;
    		right: 2px;
		}

		#bookings_specific_times .table_grid table td.remove:hover{
			color: #fff;
			background-color: red;
		}

  	</style>
  	<li class="bookings_tab bookings_specific_times_tab advanced_options show_if_booking"><a href="#bookings_specific_times"><?php _e( 'Specific Times', 'woocommerce-bookings' ); ?></a></li>


 <?php endif;






