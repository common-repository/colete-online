<?php

defined( 'ABSPATH' ) || exit;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.colete-online.ro
 * @since      1.0.0
 *
 * @package    Colete_Online
 * @subpackage Colete_Online/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Colete_Online
 * @subpackage Colete_Online/includes
 * @author     Colete Online <alex@colete-online.ro>
 */
class Colete_Online_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'coleteonline',
			false,
			COLETE_ONLINE_PLUGIN_ROOT . '/languages'
		);

	}



}
