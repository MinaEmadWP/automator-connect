<?php

namespace Automator_Connect\Integrations\Kinsta;

use Exception;

/**
 * Class Kinsta_Api_Caller
 *
 * Exposes Kinsta operations to the rest of the Automator Connect plugin.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_Api_Caller {

	/**
	 * API key validation endpoint.
	 *
	 * @var string
	 */
	private const VALIDATE_ENDPOINT = 'validate';

	/**
	 * Sites endpoint.
	 *
	 * @var string
	 */
	private const SITES_ENDPOINT = 'sites';

	/**
	 * Create plain Site endpoint.
	 *
	 * @var string
	 */
	private const CREATE_PLAIN_SITE_ENDPOINT = 'plain';

	/**
	 * Get operation status endpoint.
	 *
	 * @var string
	 */
	private const OPERATION_STATUS_ENDPOINT = 'operations';

	/**
	 * Kinsta API client.
	 *
	 * @var Kinsta_Api_Client
	 */
	private $client;

	/**
	 * Kinsta API credentials.
	 *
	 * @var Kinsta_Api_Credentials
	 */
	private $credentials;

	/**
	 * Constructor.
	 *
	 * @param Kinsta_Api_Client      $client      Kinsta API client.
	 * @param Kinsta_Api_Credentials $credentials Kinsta API credentials.
	 */
	public function __construct(
		Kinsta_Api_Client $client,
		Kinsta_Api_Credentials $credentials
	) {
		$this->client      = $client;
		$this->credentials = $credentials;
	}

	/**
	 * Validate the configured Kinsta API key.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function validate_api_key() {

		return $this->client->get( self::VALIDATE_ENDPOINT );
	}

	/**
	 * Retrieve the available Kinsta regions for the configured company.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the company ID is missing or the request fails.
	 */
	public function list_available_regions() {

		$company_id = $this->get_company_id();

		return $this->client->get( 'company/' . $company_id . '/available-regions' );
	}

	/**
	 * Start creating a Kinsta plain site.
	 *
	 * @param array $body Request body.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the company ID is missing or the request fails.
	 */
	public function start_create_plain_site( array $body ) {

		$company_id = $this->get_company_id();

		$body['company'] = $company_id;

		return $this->client->post( self::SITES_ENDPOINT . '/' . self::CREATE_PLAIN_SITE_ENDPOINT, $body );
	}

	/**
	 * Get the status information of a Kinsta operation.
	 *
	 * @param string $operation_id Operation ID.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function get_operation_status( $operation_id ) {

		return $this->client->get( self::OPERATION_STATUS_ENDPOINT . '/' . $operation_id );
	}

	/**
	 * Delete a Kinsta site.
	 *
	 * @param string $site_id Site ID.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function delete_site( $site_id ) {

		return $this->client->delete( self::SITES_ENDPOINT . '/' . $site_id );
	}

	/**
	 * Get the list of sites for the configured Kinsta company.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the company ID is missing or the request fails.
	 */
	public function list_sites() {

		$company_id = $this->get_company_id();

		return $this->client->get(
			self::SITES_ENDPOINT,
			array(
				'company' => $company_id,
			)
		);
	}

	/**
	 * Get the configured Kinsta company ID.
	 *
	 * @return string Company ID.
	 *
	 * @throws Exception When the company ID is missing.
	 */
	private function get_company_id() {

		$company_id = $this->credentials->get_company_id();

		if ( '' === $company_id ) {
			throw new Exception( esc_html__( 'Kinsta company ID is missing.', 'automator-connect' ) );
		}

		return $company_id;
	}
}
