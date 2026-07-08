<?php

namespace Automator_Connect\Integrations\Cloudways;

/**
 * Class Cloudways_Api_Credentials
 *
 * Reads the Cloudways email and API key from WordPress options and caches the
 * access token in a transient.
 *
 * This class does not perform authentication requests. It only handles the storage 
 * layer for Cloudways credentials.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Api_Credentials {

	/**
	 * Option name used to store the Cloudways email.
	 *
	 * @var string
	 */
	const EMAIL_OPTION = 'ac_cloudways_email';

	/**
	 * Option name used to store the Cloudways API key.
	 *
	 * @var string
	 */
	const API_KEY_OPTION = 'ac_cloudways_api_key';

	/**
	 * Transient name used to store the Cloudways access token.
	 *
	 * @var string
	 */
	const ACCESS_TOKEN_TRANSIENT = 'ac_cloudways_access_token';

	/**
	 * Get the configured Cloudways email.
	 *
	 * @return string The saved email address, or an empty string if not set.
	 */
	public function get_email() {
		$email = get_option( self::EMAIL_OPTION, '' );

		if ( ! is_string( $email ) ) {
			return '';
		}

		return trim( $email );
	}

	/**
	 * Get the configured Cloudways API key.
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
	 * Determine whether the required Cloudways credentials are available.
	 *
	 * @return bool True when both email and API key are present, false otherwise.
	 */
	public function has_credentials() {
		return ( '' !== $this->get_email() && '' !== $this->get_api_key() );
	}

	/**
	 * Get the cached Cloudways access token.
	 *
	 * @return string The cached access token, or an empty string if missing.
	 */
	public function get_access_token() {
		$token = get_transient( self::ACCESS_TOKEN_TRANSIENT );

		if ( ! is_string( $token ) ) {
			return '';
		}

		return trim( $token );
	}

	/**
	 * Store the Cloudways access token in a transient.
	 *
	 * @param string $token Access token.
	 * @param int    $expiration Expiration in seconds.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function set_access_token( $token, $expiration ) {
		$token      = trim( (string) $token );
		$expiration = absint( $expiration );

		if ( '' === $token ) {
			return false;
		}

		if ( $expiration < 1 ) {
			$expiration = HOUR_IN_SECONDS - 60;
		}

		return set_transient( self::ACCESS_TOKEN_TRANSIENT, $token, $expiration );
	}

	/**
	 * Remove the cached Cloudways access token.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function clear_access_token() {
		return delete_transient( self::ACCESS_TOKEN_TRANSIENT );
	}

	/**
	 * Remove the saved Cloudways credentials and cached access token.
	 *
	 * @return void
	 */
	public function clear_credentials() {
		delete_option( self::EMAIL_OPTION );
		delete_option( self::API_KEY_OPTION );
		delete_transient( self::ACCESS_TOKEN_TRANSIENT );
	}
}
