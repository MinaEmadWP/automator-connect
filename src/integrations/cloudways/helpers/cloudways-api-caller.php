<?php

namespace Automator_Connect\Integrations\Cloudways;

use Exception;

/**
 * Class Cloudways_Api_Caller
 *
 * Exposes Cloudways operations to the rest of the Automator Connect plugin.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Api_Caller {

	/**
	 * Servers endpoint.
	 *
	 * @var string
	 */
	private const SERVERS_ENDPOINT = 'server';

	/**
	 * Applications endpoint.
	 *
	 * @var string
	 */
	private const APPS_ENDPOINT = 'app';

	/**
	 * Applications take-backups endpoint.
	 *
	 * @var string
	 */
	 private const APPS_TAKEBACKUP_ENDPOINT = 'app/manage/takeBackup';

	/**
	 * Operations endpoint.
	 *
	 * @var string
	 */
	private const OPERATIONS_ENDPOINT = 'operation';

	/**
	 * API client.
	 *
	 * @var Cloudways_Api_Client
	 */
	private $client;

	/**
	 * Constructor.
	 *
	 * @param Cloudways_Api_Client $client Cloudways API client.
	 */
	public function __construct( Cloudways_Api_Client $client ) {
		$this->client = $client;
	}

	/**
	 * Get the list of servers.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function list_servers() {
		return $this->client->get( self::SERVERS_ENDPOINT );
	}

	/**
	 * Start the add application process.
	 *
	 * The returned response is expected to contain an operation identifier.
	 *
	 * @param array $body Request payload.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function start_add_app_process( array $body ) {
		return $this->client->post( self::APPS_ENDPOINT, $body );
	}

	/**
	 * Start the remove application process.
	 *
	 * The returned response is expected to contain an operation identifier.
	 *
	 * @param int|string $app_id App ID.
	 * @param array  $query_args  Optional query arguments.
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function start_remove_app_process( $app_id, array $query_args ) {
		return $this->client->delete( self::APPS_ENDPOINT, $app_id, $query_args );
	}

	/**
	 * Initiate an application backup operation.
	 *
	 * The returned response is expected to contain an operation identifier.
	 *
	 * @param array $body Request payload.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function start_app_backup_process( array $body ) {
		return $this->client->post( self::APPS_TAKEBACKUP_ENDPOINT, $body );
	}

	/**
	 * Get the status of a Cloudways operation.
	 *
	 * @param int|string $operation_id Operation identifier.
	 *
	 * @return array Decoded API response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function get_operation_status( $operation_id ) {

		return $this->client->get(
			self::OPERATIONS_ENDPOINT,
			$operation_id 
		);
	}
}
