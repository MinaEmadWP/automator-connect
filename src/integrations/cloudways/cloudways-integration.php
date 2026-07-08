<?php

namespace Automator_Connect\Integrations\Cloudways;

use Automator_Connect\Integrations\Cloudways\Cloudways_Add_App;
use Automator_Connect\Integrations\Cloudways\Cloudways_Get_Operation_Status;
use Automator_Connect\Integrations\Cloudways\Cloudways_Remove_App;
use Automator_Connect\Integrations\Cloudways\Cloudways_Create_App_Backup;
use Automator_Connect\Integrations\Cloudways\Cloudways_Settings;
use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Credentials;
use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Client;
use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Caller;
use Automator_Connect\Integrations\Cloudways\Cloudways_App_Helpers;
use Uncanny_Automator\Integration;

/**
 * Class Cloudways_Integration
 *
 * Main Cloudways integration class.
 */
class Cloudways_Integration extends Integration {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'CLOUDWAYS';

	/**
	 * Integration name.
	 */
	private const INTEGRATION_NAME = 'Cloudways';

	/**
	 * Settings URL.
	 */
	private const SETTINGS_URL = 'edit.php?post_type=uo-recipe&page=uncanny-automator-config&tab=premium-integrations&integration=cloudways';

	/**
	 * Icon image URL.
	 */
	 private const ICON_URL = 'img/cloudways-icon.svg';

	/**
	 * Cloudways API credentials.
	 *
	 * @var Cloudways_Api_Credentials
	 */
	private $credentials;

	/**
	 * Cloudways API client.
	 *
	 * @var Cloudways_Api_Client
	 */
	private $client;

	/**
	 * Cloudways API caller.
	 *
	 * @var Cloudways_Api_Caller
	 */
	private $caller;

	/**
	 * Cloudways app helpers.
	 *
	 * @var Cloudways_App_Helpers
	 */
	private $app_helpers;

	/**
	 * Set up the integration.
	 *
	 * @return void
	 */
	protected function setup() {
		$this->build_dependencies();

		$this->set_integration( self::INTEGRATION_CODE );
		$this->set_name( self::INTEGRATION_NAME );

		$this->set_icon_url( plugin_dir_url( __FILE__ ) . self::ICON_URL );
		$this->set_settings_url(  admin_url( self::SETTINGS_URL ) );
		$this->set_plugin_file_path( $this->get_main_plugin_file_path() );

		$this->set_is_third_party( true );

		// Check credentials and set connection status (true or false).
		$this->set_connected( $this->credentials->has_credentials() );

		// Keep the parent helpers property aligned with the helper object used by actions.
		$this->helpers = $this->app_helpers;
	}

	/**
	 * Load the integration parts.
	 *
	 * @return void
	 */
	public function load() {
		// Load settings page.
		new Cloudways_Settings( $this->app_helpers, $this->client, $this->credentials );
		// Load actions.
		new Cloudways_Add_App( $this->app_helpers, $this->caller );
		new Cloudways_Remove_App( $this->app_helpers, $this->caller );
		new Cloudways_Create_App_Backup( $this->app_helpers, $this->caller );
		new Cloudways_Get_Operation_Status( $this->app_helpers, $this->caller );
	}

	/**
	 * Arguments to pass to trigger/action constructors.
	 *
	 * @return array
	 */
	protected function get_load_arguments() {
		return array( $this->app_helpers, $this->caller );
	}

	/**
	 * Build the shared Cloudways dependency graph once.
	 *
	 * @return void
	 */
	private function build_dependencies() {
		$this->credentials = new Cloudways_Api_Credentials();
		$this->client      = new Cloudways_Api_Client( $this->credentials );
		$this->caller      = new Cloudways_Api_Caller( $this->client );
		$this->app_helpers = new Cloudways_App_Helpers( $this->caller );
	}

	/**
	 * Get the main plugin file path if available.
	 *
	 * @return string
	 */
	private function get_main_plugin_file_path() {
		if ( defined( 'AUTOMATOR_CONNECT_BASE_FILE' ) ) {
			return (string) AUTOMATOR_CONNECT_BASE_FILE;
		}

		return '';
	}
}
