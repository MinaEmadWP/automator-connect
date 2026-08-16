<?php

namespace Automator_Connect\Integrations\Cloudways;

/**
 * Class Cloudways_Api_Credentials
 *
 * Reads the Cloudways access token from the WordPress options table.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Api_Credentials {

	/**
	 * Option name used to store the Cloudways access token.
	 *
	 * @var string
	 */
	const ACCESS_TOKEN_OPTION = 'ac_cloudways_access_token';

	/**
	 * Get the configured Cloudways access token.
	 *
	 * @return string The saved access token, or an empty string if not set.
	 */
	public function get_access_token() {
		$access_token = get_option( self::ACCESS_TOKEN_OPTION, '' );

		if ( ! is_string( $access_token ) ) {
			return '';
		}

		return trim( $access_token );
	}

	/**
	 * Determine whether the Cloudways access token is available.
	 *
	 * @return bool True when an access token is present, false otherwise.
	 */
	public function has_credentials() {
		return '' !== $this->get_access_token();
	}

	/**
	 * Remove the saved Cloudways access token.
	 *
	 * @return void
	 */
	public function clear_credentials() {
		delete_option( self::ACCESS_TOKEN_OPTION );
	}
}