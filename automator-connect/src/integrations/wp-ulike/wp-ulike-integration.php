<?php

namespace Automator_Connect\Integrations\WP_Ulike;

use Uncanny_Automator\Integration;

/**
 * Class WP_Ulike_Integration
 *
 * Main WP ULike integration class.
 * 
 * @package Automator_Connect\Integrations\WP_Ulike
 */
class WP_Ulike_Integration extends Integration {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'WPULIKE';

	/**
	 * Integration name.
	 */
	private const INTEGRATION_NAME = 'WP ULike';

    /**
	 * Icon image URL.
	 */
	private const ICON_URL = 'img/wp-ulike-icon.svg';

	/**
	 * Set up the integration.
	 *
	 * @return void
	 */
	protected function setup() {

		// Build the helpers dependency.
		$this->helpers = new WP_Ulike_Helpers();

		$this->set_integration( self::INTEGRATION_CODE );
		$this->set_name( self::INTEGRATION_NAME );

        $this->set_icon_url( plugin_dir_url( __FILE__ ) . self::ICON_URL );
        $this->set_plugin_file_path( $this->get_main_plugin_file_path() );

		$this->set_is_third_party( true );
	}

	/**
	 * Load the integration parts.
	 *
	 * @return void
	 */
	public function load() {

		// Load triggers.
		new WP_Ulike_User_Likes_Post_Type( $this->helpers );
		new WP_Ulike_User_Unlikes_Post_Type( $this->helpers );
		new WP_Ulike_User_Likes_Comment( $this->helpers );
		new WP_Ulike_User_Unlikes_Comment( $this->helpers );
	}

	/**
	 * Arguments to pass to trigger/action constructors.
	 *
	 * @return array
	 */
	protected function get_load_arguments() {

		return array( $this->helpers );
	}

	/**
	 * Check whether the plugin is active.
	 *
	 * @return bool
	 */
	public function plugin_active() {

		return defined( 'WP_ULIKE_DIR' );
	}

    /**
	 * Get the main plugin file path if available.
	 *
	 * @return string
	 */
	private function get_main_plugin_file_path() {

		if ( defined( 'WP_ULIKE_BASENAME' ) ) {
			return (string) WP_ULIKE_BASENAME;
		}

		return '';
	}
}