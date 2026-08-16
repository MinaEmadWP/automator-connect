<?php

namespace Automator_Connect\Integrations\Kinsta;

/**
 * Class Kinsta_Api_Credentials
 *
 * Reads the Kinsta API key and company ID from WordPress options.
 *
 * This class does not perform authentication requests. It only handles the
 * storage layer for Kinsta credentials.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_Api_Credentials {

	/**
	 * Option name used to store the Kinsta API key.
	 *
	 * @var string
	 */
	const API_KEY_OPTION = 'ac_kinsta_api_key';

	/**
	 * Option name used to store the Kinsta company ID.
	 *
	 * @var string
	 */
	const COMPANY_ID_OPTION = 'ac_kinsta_company_id';

	/**
	 * Get the configured Kinsta API key.
	 *
	 * @return string The saved API key, or an empty string if not set.
	 */
	public function get_api_key() {
		$api_key = get_option( self::API_KEY_OPTION, '' );

		if ( ! is_string( $api_key ) ) {
			return '';
		}

		return trim( $api_key );
	}

	/**
	 * Get the configured Kinsta company ID.
	 *
	 * @return string The saved company ID, or an empty string if not set.
	 */
	public function get_company_id() {
		$company_id = get_option( self::COMPANY_ID_OPTION, '' );

		if ( ! is_string( $company_id ) ) {
			return '';
		}

		return trim( $company_id );
	}

	/**
	 * Determine whether the required Kinsta credentials are available.
	 *
	 * @return bool True when both API key and company ID are present, false otherwise.
	 */
	public function has_credentials() {
		return ( '' !== $this->get_api_key() && '' !== $this->get_company_id() );
	}

	/**
	 * Store the Kinsta company ID.
	 *
	 * @param string $company_id Company ID.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set_company_id( $company_id ) {
		$company_id = trim( (string) $company_id );

		if ( '' === $company_id ) {
			return false;
		}

		return update_option( self::COMPANY_ID_OPTION, $company_id );
	}

	/**
	 * Remove the saved Kinsta credentials.
	 *
	 * @return void
	 */
	public function clear_credentials() {
		delete_option( self::API_KEY_OPTION );
		delete_option( self::COMPANY_ID_OPTION );
	}
}