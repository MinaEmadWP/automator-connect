<?php
/**
 * Plugin Name:       Automator Connect
 * Description:       Extends Uncanny Automator with a number of third-party integrations.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Mina Emad
 * Author URI:        https://minaemad.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       automator-connect
 * Domain Path:       /languages
 * Requires Plugins:  uncanny-automator
 */

if ( ! defined( 'ABSPATH' ) ) {
	/**
	 * If WordPress not loaded or file called directly, abort.
	 */
	exit;
}

if ( ! defined( 'AUTOMATOR_CONNECT_PLUGIN_VERSION' ) ) {
	/**
	 * Automator Connect version.
	 */
	define( 'AUTOMATOR_CONNECT_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'AUTOMATOR_CONNECT_BASE_FILE' ) ) {
	/**
	 * Absolute path to this file, used for path/URL helpers elsewhere.
	 */
	define( 'AUTOMATOR_CONNECT_BASE_FILE', __FILE__ );
}

if ( ! defined( 'AC_ABSPATH' ) ) {
	/**
	 * Base path Automator Connect uses to require its own files.
	 */
	define( 'AC_ABSPATH', dirname( AUTOMATOR_CONNECT_BASE_FILE ) . DIRECTORY_SEPARATOR );
}

add_action( 'plugins_loaded', 'automator_connect_load_textdomain' );

if ( ! function_exists( 'automator_connect_load_textdomain' ) ) {

	/**
	 * Load Automator Connect's translation files.
	 *
	 * Required for self-hosted (non-WordPress.org) distribution, since WordPress.org's
	 * automatic translation loading doesn't apply here.
	 *
	 * @return void
	 */
	function automator_connect_load_textdomain() {
		load_plugin_textdomain(
			'automator-connect',
			false,
			dirname( plugin_basename( AUTOMATOR_CONNECT_BASE_FILE ) ) . '/languages'
		);
	}
}

add_action( 'automator_add_integration', 'automator_connect_register_integrations' );

if ( ! function_exists( 'automator_connect_register_integrations' ) ) {

	/**
	 * Load all integration bootstrap files found in the integrations directory.
	 *
	 * Each integration is expected to have its own folder under `src/integrations/`
	 * with a `load.php` file that wires up the integration classes.
	 *
	 * @return void
	 */
	function automator_connect_register_integrations() {

		// If this class doesn't exist, Uncanny Automator plugin needs to be updated.
		if ( ! class_exists( '\Uncanny_Automator\Integration' ) ) {
			return;
		}

		// Otherwise, start loading the Automator Connect files.
		$load_files = glob( AC_ABSPATH . 'src/integrations/*/load.php' );

		if ( empty( $load_files ) || ! is_array( $load_files ) ) {
			return;
		}

		sort( $load_files );

		foreach ( $load_files as $load_file ) {
			if ( is_readable( $load_file ) ) {
				require_once $load_file;
			}
		}
	}
}
