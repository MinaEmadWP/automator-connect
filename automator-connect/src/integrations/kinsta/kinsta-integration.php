<?php

namespace Automator_Connect\Integrations\Kinsta;

use Uncanny_Automator\Integration;

/**
 * Class Kinsta_Integration
 *
 * Main Kinsta integration class.
 */
class Kinsta_Integration extends Integration {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'KINSTA';

	/**
	 * Integration name.
	 */
	private const INTEGRATION_NAME = 'Kinsta';

	/**
	 * Settings URL.
	 */
	private const SETTINGS_URL = 'edit.php?post_type=uo-recipe&page=uncanny-automator-config&tab=premium-integrations&integration=kinsta';

	/**
	 * Icon image URL.
	 */
	private const ICON_URL = 'img/kinsta-icon.svg';

	/**
	 * Kinsta API credentials.
	 *
	 * @var Kinsta_Api_Credentials
	 */
	private $credentials;

	/**
	 * Kinsta API client.
	 *
	 * @var Kinsta_Api_Client
	 */
	private $client;

	/**
	 * Kinsta API caller.
	 *
	 * @var Kinsta_Api_Caller
	 */
	private $caller;

	/**
	 * Kinsta app helpers.
	 *
	 * @var Kinsta_App_Helpers
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
		new Kinsta_Settings( $this->app_helpers, $this->caller, $this->credentials );
		// Load actions.
		new Kinsta_Create_Plain_Site( $this->app_helpers, $this->caller );
		new Kinsta_Delete_Site( $this->app_helpers, $this->caller );
		new Kinsta_Get_Operation_Status( $this->app_helpers, $this->caller );
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
	 * Build the shared Kinsta dependency graph once.
	 *
	 * @return void
	 */
	private function build_dependencies() {

		$this->credentials = new Kinsta_Api_Credentials();
		$this->client      = new Kinsta_Api_Client( $this->credentials );
		$this->caller      = new Kinsta_Api_Caller( $this->client, $this->credentials );
		$this->app_helpers = new Kinsta_App_Helpers( $this->caller );
	}
}
