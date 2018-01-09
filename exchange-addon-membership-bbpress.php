<?php
/*
 * Plugin Name: ExchangeWP - Membership bbPress Add-on
 * Version: 1.1.3
 * Description: Adds the ExchangeWP Membership management functionality to bbPress
 * Plugin URI: https://exchangewp.com/downloads/membership-bbpress/
 * Author: ExchangeWP
 * Author URI: http://exchangewp.com
 * ExchangeWP Package: exchange-addon-membership-bbpress

 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

define( 'ITE_MEMBERSHIP_BBPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This registers our plugin as a membership addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_membership_bbpress_addon() {
	$options = array(
		'name'              => __( 'Membership for bbPress', 'LION' ),
		'description'       => __( 'Add Memberships functionality to your bbPress forums.', 'LION' ),
		'author'            => 'ExchangeWP',
		'author_url'        => 'https://exchangewp.com/downloads/membership-bbpress/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/bbpress50px.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'other',
		'basename'          => plugin_basename( __FILE__ ),
		'settings-callback' => 'it_exchange_membership_bbpress_addon_settings_callback',
		'labels'      => array(
			'singular_name' => __( 'Membership bbPress', 'LION' ),
		),
	);
	it_exchange_register_addon( 'membership-bbpress', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_membership_bbpress_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.3
 * @return void
*/
function it_exchange_membership_bbpress_set_textdomain() {
	load_plugin_textdomain( 'LION', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'it_exchange_membership_bbpress_set_textdomain' );

/**
 * Registers Plugin with ExchangeWP updater class
 *
 * @since 1.0.0
 *
 * @param object $updater exchangewp updater object
 * @return void
*/
function exchange_membership_bbpress_plugin_updater() {

	$license_check = get_transient( 'exchangewp_license_check' );

	if ($license_check->license == 'valid' ) {
		$license_key = it_exchange_get_option( 'exchangewp_licenses' );
		$license = $license_key['exchange_license'];

		$edd_updater = new EDD_SL_Plugin_Updater( 'https://exchangewp.com', __FILE__, array(
				'version' 		=> '1.1.3', 				// current version number
				'license' 		=> $license, 				// license key (used get_option above to retrieve from DB)
				'item_id'		 	=> 582,					 	  // name of this plugin
				'author' 	  	=> 'ExchangeWP',    // author of this plugin
				'url'       	=> home_url(),
				'wp_override' => true,
				'beta'		  	=> false
			)
		);
	}

}

add_action( 'admin_init', 'exchange_membership_bbpress_plugin_updater', 0 );
