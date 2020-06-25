<?php
/**
 * Plugin Name:          Claudio Sanches - PagSeguro for WooCommerce
 * Plugin URI:           https://github.com/claudiosanches/woocommerce-pagseguro
 * Description:          Includes PagSeguro as a payment gateway to WooCommerce.
 * Author:               Claudio Sanches
 * Author URI:           https://claudiosanches.com
 * Version:              2.14.0
 * License:              GPLv3 or later
 * Text Domain:          woocommerce-pagseguro
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      3.4.0
 *
 * Claudio Sanches - PagSeguro for WooCommerce is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or any later version.
 *
 * Claudio Sanches - PagSeguro for WooCommerce is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Claudio Sanches - PagSeguro for WooCommerce. If not, see
 * <https://www.gnu.org/licenses/gpl-3.0.txt>.
 *
 * @package WooCommerce_PagSeguro
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WC_PAGSEGURO_VERSION', '2.14.0' );
define( 'WC_PAGSEGURO_PLUGIN_FILE', __FILE__ );


if ( ! class_exists( 'WC_PagSeguro' ) ) {
	# custom functions 
	//require_once dirname(__FILE__) . '/vendor/autoload.php';
	//use CWG\PagSeguro\PagSeguroAssinaturas;
	/*end*/
	include_once dirname( __FILE__ ) . '/includes/class-wc-pagseguro.php';
	include_once dirname( __FILE__ ) . '/includes/custom-functions.php';
	include_once dirname( __FILE__ ) . '/includes/shortcodes.php';
	add_action( 'plugins_loaded', array( 'WC_PagSeguro', 'init' ) );
}


function myprefix_custom_cron_schedule( $schedules ) {
    $schedules['every_five_min'] = array(
        'interval' => 300, // Every 6 hours
        'display'  => __( 'A cadaa 5 min' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'myprefix_custom_cron_schedule' );

//Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'myprefix_cron_hook' ) ) {
    wp_schedule_event( time(), 'every_five_min', 'myprefix_cron_hook' );
}






