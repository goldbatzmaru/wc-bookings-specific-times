<?php
/*
* Plugin Name: WooCommerce Bookings - Specific Times
* Description: Allows for the overriding of bookable products normal price structure with specific booking start times, their allowed hour intervals, and those intervals associated base prices.
* Version: 1.0.0
* Author: Daniel Ahrendt
*/

// Define constants
define( 'WC_BOOKINGS_ST_VERSION', '1.0.0' );
define( 'WC_BOOKINGS_ST_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
define( 'WC_BOOKINGS_ST_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_BOOKINGS_ST_ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WC_BOOKINGS_ABSPATH', ABSPATH . 'wp-content/plugins/woocommerce-bookings/');

// Include helper functions
include_once( WC_BOOKINGS_ST_ABSPATH . 'includes/wc-bookings-specific-times-functions.php' );

//WooCommerce & WC Booking check to run on activation
function wc_bookings_specific_times_activate() {
  
    if( !class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and Activate WooCommerce.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    } else {
       if( !class_exists( 'WC_Bookings' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and Activate WooCommerce Bookings.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
    }
}

//sets up activation hook
register_activation_hook(__FILE__, 'wc_bookings_specific_times_activate');


//allows us to override WC Booking templates from this plugin
add_filter( 'woocommerce_locate_template', 'wc_bookings_specific_times_locate_template', 10, 3 );

function wc_bookings_specific_times_locate_template( $template, $template_name, $template_path ) {
  global $woocommerce;

  $_template = $template;

  if ( ! $template_path ) $template_path = $woocommerce->template_url;

  $plugin_path  = WC_BOOKINGS_ST_ABSPATH . '/woocommerce-bookings/';

  // Look within passed path within the theme - this is priority
  $template = locate_template(

    array(
      $template_path . $template_name,
      $template_name
    )
  );

  // Modification: Get the template from this plugin, if it exists
  if ( ! $template && file_exists( $plugin_path . $template_name ) )
    $template = $plugin_path . $template_name;

  // Use default template
  if ( ! $template )
    $template = $_template;

  // Return what we found
  return $template;
}

function get_current_post_id(){
	global $woocommerce, $post;
	return $post->ID;
}

function get_current_product(){
	global $woocommerce, $post;
	return wc_get_product( $post->ID );
}

function get_posted_resources( $product ) {
	$resources = array();

	if ( isset( $_POST['resource_id'] ) && isset( $_POST['_wc_booking_has_resources'] ) ) {
		$resource_ids         = $_POST['resource_id'];
		$resource_menu_order  = $_POST['resource_menu_order'];
		$resource_base_cost   = $_POST['resource_cost'];
		$resource_block_cost  = $_POST['resource_block_cost'];
		$max_loop             = max( array_keys( $_POST['resource_id'] ) );
		$resource_base_costs  = array();
		$resource_block_costs = array();

		foreach ( $resource_menu_order as $key => $value ) {
			$resources[ absint( $resource_ids[ $key ] ) ] = array(
				'base_cost'  => wc_clean( $resource_base_cost[ $key ] ),
				'block_cost' => wc_clean( $resource_block_cost[ $key ] ),
			);
		}
	}
	return $resources;
}

// Get Product Specific Time Info

function product_specific_time_info($product_id, $is_from_post = null){
		$st_enabled = get_is_specific_times_enabled($product_id);
		$specific_times = (get_specific_times($product_id) != null ? get_specific_times($product_id) : null);

		if($st_enabled == 'yes'){
			$start_times_allowed = [];
			$intervals_allowed = [];
			$prices_allowed = [];
			$timesRelativeInfo = [];
			foreach($specific_times as $i => $value) {
				$cleaned_time = str_replace(':', '', $value['start']);
				//Create interval array
				$intervals = explode(",", $value['intervals']);
				//Create prices array
				$prices = explode(",", $value['prices']);
				$intervalCount = count($intervals);
				$priceCount = count($prices);
				$intervalPriceArray = [];

				if($intervalCount == $priceCount) {
					foreach($intervals as $i => $interval){
						$intervalPriceArray[$interval] = $prices[$i];
					}
				}
				if((count($intervalPriceArray) == $intervalCount) && (count($intervalPriceArray) != 0)){
					$timesRelativeInfo[$cleaned_time] = $intervalPriceArray;
				}
			}
			foreach($timesRelativeInfo as $key => $value){
				$start_times_allowed[] = $key;

			}
			$start_times_allowed = $start_times_allowed;
			$returnArray = json_encode(array($start_times_allowed, $timesRelativeInfo));
			if($is_from_post){
				die($returnArray);
			} else {
				return $returnArray;
			}
			
		}
}


// Return Specific Time cost
function specific_time_cost( $product_id, $start_time, $duration ) {
	$start_time = str_replace(':', '', $start_time);
	$start_time = strval($start_time);

	if(strlen($start_time) == '3'){
		$start_time = "0". $start_time;
	
	}
	$specific_time_info = product_specific_time_info($product_id);
	$specific_time_info = json_decode($specific_time_info, true);
	$cost = $specific_time_info[1][$start_time][$duration];
	return $cost;
}


// Adding functionality to Bookable products panels
function add_specific_times_tab() {
  include( 'includes/admin/views/html-booking-tab.php' );
}

function add_specific_times_panel_content() {
  include( 'includes/admin/views/html-booking-specific-times.php' );
}

function init_specific_times_tab() {
  add_action( 'woocommerce_product_write_panel_tabs', 'add_specific_times_tab', 6 );
  add_action( 'woocommerce_product_data_panels', 'add_specific_times_panel_content', 6 ); 
}


function get_posted_specific_times() {
	$specific_times = array();
	$row_size     = isset( $_POST['wc_booking_specific_time_start']) ? sizeof( $_POST['wc_booking_specific_time_start'] ) : 0;
	for ( $i = 0; $i < $row_size; $i ++ ) {
		$specific_times[ $i ]['start']     = wc_clean( $_POST['wc_booking_specific_time_start'][ $i ] );
		$specific_times[ $i ]['intervals'] = wc_clean( $_POST['wc_booking_specific_times_intervals'][ $i ] );
		$specific_times[ $i ]['prices'] = wc_clean( $_POST['wc_booking_specific_time_rel_prices'][ $i ] );
	}
	return $specific_times;
}


function set_specific_times_props( $product ) {
	// Only set props if the product is a bookable product.
	if ( ! is_a( $product, 'WC_Product_Booking' ) ) {
		return;
	}
	$specific_times_enabled = isset($_POST['_wc_booking_specific_times_enable']);
	$specific_times = get_posted_specific_times();

	// $resources = get_posted_resources( $product );
	$product->set_props(array(
		'specific_times_enabled'        => $specific_times_enabled,
		'specific_times'                => $specific_times
	) );
}


function wc_bookings_specific_times_save(){

 	$post_id = get_current_post_id();
 	$product = new WC_Product_Booking( $post_id );

 	// Update post meta for specific times
 	$post_specific_times = get_posted_specific_times();
 	update_post_meta( $post_id, '_wc_booking_specific_times', $post_specific_times);
 	// Update post meta for enabling or disabling specific times
 	$post_specific_times_enable = $_POST['_wc_booking_specific_times_enable'];
 	$specific_times_enabled_checkbox = isset( $post_specific_times_enable ) ? 'yes' : 'no';
 	update_post_meta( $post_id, '_wc_booking_specific_times_enable', $specific_times_enabled_checkbox );
}


if ( is_admin() ) {
  add_action( 'admin_init', 'init_specific_times_tab', 10);
}

// Save Fields
add_action( 'woocommerce_process_product_meta', 'wc_bookings_specific_times_save', 10);

// Change the order of fields on the booking form
function custom_order_booking_fields ( $fields ) {
	$reorder  = array();
	
	$reorder[] = $fields['wc_bookings_field_resource'];  // Resource
	$reorder[] = $fields['wc_bookings_field_persons'];  // Persons
	$reorder[] = $fields['wc_bookings_field_start_date'];  // Calendar or Start Date
	$reorder[] = $fields['wc_bookings_field_duration'];  // Duration

	return $reorder;
}
add_filter( 'booking_form_fields', 'custom_order_booking_fields');



//View JavaScripts
function wpa54064_inspect_scripts() {
    global $wp_scripts;
    foreach( $wp_scripts->queue as $handle ) :
        echo $handle . ' | ';
    endforeach;
}

//Enqueue Specific Times WooCommerce Bookings Booking Form scripts
function initialLoadModifiedScripts() {
	global $wp_locale;
	$product = get_current_product();

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$wc_bookings_booking_form_args = array(
		'closeText'                  => __( 'Close', 'woocommerce-bookings' ),
		'currentText'                => __( 'Today', 'woocommerce-bookings' ),
		'prevText'                   => __( 'Previous', 'woocommerce-bookings' ),
		'nextText'                   => __( 'Next', 'woocommerce-bookings' ),
		'monthNames'                 => array_values( $wp_locale->month ),
		'monthNamesShort'            => array_values( $wp_locale->month_abbrev ),
		'dayNames'                   => array_values( $wp_locale->weekday ),
		'dayNamesShort'              => array_values( $wp_locale->weekday_abbrev ),
		'dayNamesMin'                => array_values( $wp_locale->weekday_initial ),
		'firstDay'                   => get_option( 'start_of_week' ),
		'current_time'               => date( 'Ymd', current_time( 'timestamp' ) ),
		'check_availability_against' => $product->get_check_start_block_only() ? 'start' : '',
		'duration_unit'              =>$product->get_duration_unit(),
		'resources_assignment'       => ! $product->has_resources() ? 'customer' : $product->get_resources_assignment(),
		'isRTL'                      => is_rtl(),
		'product_id'                 => $product->get_id(),
		'default_availability'       => $product->get_default_availability(),
	);

	$wc_bookings_date_picker_args = array(
		'ajax_url'                   => WC_AJAX::get_endpoint( 'wc_bookings_find_booked_day_blocks' ),
	);

	if ( in_array( $product->get_duration_unit(), array( 'minute', 'hour' ) ) ) {
		$wc_bookings_booking_form_args['booking_duration'] = 1;
	} else {
		$wc_bookings_booking_form_args['booking_duration']        = $product->get_duration();
		$wc_bookings_booking_form_args['booking_duration_type']   = $product->get_duration_type();

		if ( 'customer' == $wc_bookings_booking_form_args['booking_duration_type'] ) {
			$wc_bookings_booking_form_args['booking_min_duration'] = $product->get_min_duration();
			$wc_bookings_booking_form_args['booking_max_duration'] = $product->get_max_duration();
		} else {
			$wc_bookings_booking_form_args['booking_min_duration'] = $wc_bookings_booking_form_args['booking_duration'];
			$wc_bookings_booking_form_args['booking_max_duration'] = $wc_bookings_booking_form_args['booking_duration'];
		}
	}

	wp_enqueue_script( 'wc-bookings-booking-form', WC_BOOKINGS_ST_PLUGIN_URL . '/assets/js/booking-form' . $suffix . '.js', array( 'jquery', 'jquery-blockui' ), WC_BOOKINGS_ST_VERSION, true );
	wp_localize_script( 'wc-bookings-booking-form', 'wc_bookings_booking_form', $wc_bookings_booking_form_args );
	wp_register_script( 'wc-bookings-date-picker', WC_BOOKINGS_ST_PLUGIN_URL . '/assets/js/date-picker' . $suffix . '.js', array( 'wc-bookings-booking-form', 'jquery-ui-datepicker', 'underscore' ), WC_BOOKINGS_ST_VERSION, true );
	wp_localize_script( 'wc-bookings-date-picker', 'wc_bookings_date_picker_args', $wc_bookings_date_picker_args );
	wp_register_script( 'wc-bookings-month-picker', WC_BOOKINGS_ST_PLUGIN_URL . '/assets/js/month-picker' . $suffix . '.js', array( 'wc-bookings-booking-form' ), WC_BOOKINGS_ST_VERSION, true );
	wp_register_script( 'wc-bookings-time-picker', WC_BOOKINGS_ST_PLUGIN_URL . '/assets/js/time-picker' . $suffix . '.js', array( 'wc-bookings-booking-form' ), WC_BOOKINGS_ST_VERSION, true );

	// Variables for JS scripts
	$booking_form_params = array(
		'cache_ajax_requests'        => 'false',
		'ajax_url'                   => admin_url( 'admin-ajax.php' ),
		'i18n_date_unavailable'      => __( 'This date is unavailable', 'woocommerce-bookings' ),
		'i18n_date_fully_booked'     => __( 'This date is fully booked and unavailable', 'woocommerce-bookings' ),
		'i18n_date_partially_booked' => __( 'This date is partially booked - but bookings still remain', 'woocommerce-bookings' ),
		'i18n_date_available'        => __( 'This date is available', 'woocommerce-bookings' ),
		'i18n_start_date'            => __( 'Choose a Start Date', 'woocommerce-bookings' ),
		'i18n_end_date'              => __( 'Choose an End Date', 'woocommerce-bookings' ),
		'i18n_dates'                 => __( 'Dates', 'woocommerce-bookings' ),
		'i18n_choose_options'        => __( 'Please select the options for your booking and make sure duration rules apply.', 'woocommerce-bookings' ),
		'i18n_clear_date_selection'  => __( 'To clear selection, pick a new start date', 'woocommerce-bookings' ),
	);

	wp_localize_script( 'wc-bookings-booking-form', 'booking_form_params', apply_filters( 'booking_form_params', $booking_form_params ) );
}


//Dequeue Original WooCommerce Bookings Booking Form scripts and enqueue modified ones
function dequeue_wc_bookings_booking_form() {
    wp_dequeue_script( 'wc-bookings-booking-form' );
    wp_deregister_script( 'wc-bookings-booking-form' );
    wp_dequeue_script( 'wc-bookings-date-picker' );
    wp_deregister_script( 'wc-bookings-date-picker' );
    wp_dequeue_script( 'wc-bookings-month-picker' );
    wp_deregister_script( 'wc-bookings-month-picker' );
    wp_dequeue_script( 'wc-bookings-time-picker' );
    wp_deregister_script( 'wc-bookings-time-picker' );

	}

function dequeue_enqueue_booking_form(){
	dequeue_wc_bookings_booking_form();
	initialLoadModifiedScripts();
	wp_enqueue_script( 'wc-bookings-date-picker' );
	wp_enqueue_script( 'wc-bookings-time-picker' );
}

add_action( 'woocommerce_before_add_to_cart_button', 'dequeue_enqueue_booking_form', 100 );

//Custom Ajax class with cost calculation and info getter

if(!class_exists(' WC_Bookings_ST_Ajax')){

	class WC_Bookings_ST_Ajax {
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_wc_bookings_get_specific_times_info', array( $this, 'get_specific_times_info' ), 100);
			add_action( 'wp_ajax_nopriv_wc_bookings_get_specific_times_info', array( $this, 'get_specific_times_info' , 100) );
			remove_action('wp_ajax_wc_bookings_calculate_costs', 'calculate_costs');
			remove_action('wp_ajax_nopriv_wc_bookings_calculate_costs', 'calculate_costs');
			add_action( 'wp_ajax_wc_bookings_st_calculate_costs', array( $this, 'calculate_costs' ), 100);
			add_action( 'wp_ajax_nopriv_wc_bookings_st_calculate_costs', array( $this, 'calculate_costs' ), 100);
		}

		public function get_specific_times_info(){
			$product_id = $_POST['product_id'];
			$is_from_post = true;
			$specific_time_info = product_specific_time_info($product_id, $is_from_post);
		}	

		/**
		 * Calculate costs for Specific Times
		 */
		public function calculate_costs() {
			$posted = array();
			parse_str( $_POST['form'], $posted );

			$booking_id = $posted['add-to-cart'];
			$product    = wc_get_product( $booking_id );


			if ( ! $product ) {
				wp_send_json( array(
					'result' => 'ERROR',
					'html'   => apply_filters( 'woocommerce_bookings_calculated_booking_cost_success_output', '<span class="booking-error">' . __( 'This booking is unavailable.', 'woocommerce-bookings' ) . '</span>', null, null ),
				) );
			}
			$product_id = $posted['add-to-cart'];
			$start_time = $posted['wc_bookings_field_start_date_time'];
			$duration = $posted['wc_bookings_field_duration'];
			$booking_form     = new WC_Booking_Form( $product );
			$cost             = specific_time_cost( $product_id, $start_time, $duration);



			if ( is_wp_error( $cost ) ) {
				wp_send_json( array(
					'result' => 'ERROR',
					'html'   => apply_filters( 'woocommerce_bookings_calculated_booking_cost_success_output', '<span class="booking-error">' . $cost->get_error_message() . '</span>', $cost, $product ),
				) );
			}

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

			if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
				if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
					$display_price = wc_get_price_including_tax( $product, array( 'price' => $cost ) );
				} else {
					$display_price = $product->get_price_including_tax( 1, $cost );
				}
			} else {
				if ( function_exists( 'wc_get_price_excluding_tax' ) ) {
					$display_price = wc_get_price_excluding_tax( $product, array( 'price' => $cost ) );
				} else {
					$display_price = $product->get_price_excluding_tax( 1, $cost );
				}
			}

			if ( version_compare( WC_VERSION, '2.4.0', '>=' ) ) {
				$price_suffix = $product->get_price_suffix( $cost, 1 );
			} else {
				$price_suffix = $product->get_price_suffix();
			}

			// Build the output
			$output = apply_filters( 'woocommerce_bookings_booking_cost_string', __( 'Booking cost', 'woocommerce-bookings' ), $product ) . ': <strong>' . wc_price( $display_price ) . $price_suffix . '</strong>';

			if($cost == 0){
				$output = apply_filters( 'woocommerce_bookings_booking_cost_string', __( '<div class="no-cost"><strong>How many hours?</strong></div>'));
			}

			// Send the output
			wp_send_json( array(
				'result' => 'SUCCESS',
				'html'   => apply_filters( 'woocommerce_bookings_calculated_booking_cost_success_output', $output, $display_price, $product ),
			) );
		}
	}

	new WC_Bookings_ST_Ajax;
}


//Recalculate Cart prices

add_action( 'woocommerce_before_calculate_totals', 'bookings_recalculate_cart', 10);

function bookings_recalculate_cart($cart_object) {

	if ( is_admin() && ! defined( 'DOING_AJAX' ) ){
		return;
	}
		foreach ( $cart_object->get_cart() as $hash => $value ) {
			if ( ! is_a( $value['data'], 'WC_Product_Booking' ) ) {
				return;
			}
			$_product = $value['data'];
			$_product_id = $value['product_id'];
			$_booking = $value['booking'];
			$_booking_time = $_booking['_time'];
			$_booking_duration = $_booking['_duration'];

			$specific_time_cost = specific_time_cost( $_product_id, $_booking_time, $_booking_duration);

			$_product->set_price(floatval($specific_time_cost));
 
	} 
}

//Redirect to checkout on add to cart
function redirect_checkout_add_cart( $url ) {
    $url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) ); 
    return $url;
}
 
add_filter( 'woocommerce_add_to_cart_redirect', 'redirect_checkout_add_cart' );
