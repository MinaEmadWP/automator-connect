<?php

namespace Automator_Connect\Integrations\Cloudways;

use Exception;

/**
 * Class Cloudways_App_Helpers
 *
 * Provides helper methods for retrieving Cloudways data and building
 * dropdown options for the Cloudways integration.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_App_Helpers {

	/**
	 * Cloudways API caller.
	 *
	 * @var Cloudways_Api_Caller
	 */
	private $api_caller;

	/**
	 * Cached servers list.
	 *
	 * @var array|null
	 */
	private $servers = null;

	/**
	 * Constructor.
	 *
	 * @param Cloudways_Api_Caller $api_caller Cloudways API caller.
	 */
	public function __construct( Cloudways_Api_Caller $api_caller ) {
		$this->api_caller = $api_caller;
	}

	/**
	 * Retrieve the user's Cloudways servers.
	 *
	 * The response is cached for the duration of the current request so that
	 * server and application dropdowns reuse the same API response.
	 *
	 * @return array
	 */
	public function get_servers() {

		if ( null !== $this->servers ) {
			return $this->servers;
		}

		try {
			$response = $this->api_caller->list_servers();
		} catch ( Exception $e ) {
			$this->servers = array();

			return $this->servers;
		}

		if (
			! is_array( $response ) ||
			empty( $response['servers'] ) ||
			! is_array( $response['servers'] )
		) {
			$this->servers = array();

			return $this->servers;
		}

		$this->servers = $response['servers'];

		return $this->servers;
	}

	/**
	 * Build server select options.
	 *
	 * @return array
	 */
	public function get_server_options() {

		$options = array();

		foreach ( $this->get_servers() as $server ) {

			if (
				! is_array( $server ) ||
				empty( $server['id'] ) ||
				empty( $server['label'] )
			) {
				continue;
			}

			$options[] = array(
				'text'  => trim( (string) $server['label'] ),
				'value' => (int) $server['id'],
			);
		}

		return $options;
	}

	/**
	 * Build application select options.
	 *
	 * @return array
	 */
	public function get_app_options() {

		$options = array();

		foreach ( $this->get_servers() as $server ) {

			if (
				! is_array( $server ) ||
				empty( $server['label'] ) ||
				empty( $server['apps'] ) ||
				! is_array( $server['apps'] )
			) {
				continue;
			}

			$server_label = trim( (string) $server['label'] );

			foreach ( $server['apps'] as $app ) {

				if (
					! is_array( $app ) ||
					empty( $app['id'] ) ||
					empty( $app['label'] )
				) {
					continue;
				}

				$options[] = array(
					'text'  => $server_label . ' — ' . trim( (string) $app['label'] ),
					'value' => (int) $app['id'],
				);
			}
		}

		return $options;
	}

	/**

	* Get the server ID for a given application ID.
	*
	* @param int|string $app_id Application ID.
	*
	* @return int Server ID, or 0 when not found.
	*/
	public function get_server_id_from_app_id( $app_id ) {
		$app_id = absint( $app_id );
	
		if ( 0 === $app_id ) {
			return 0;
		}
	
		foreach ( $this->get_servers() as $server ) {
			if ( 
				empty( $server['id'] ) || 
				empty( $server['apps'] ) || 
				! is_array( $server['apps'] ) 
			) {
				continue;
			}
	
		foreach ( $server['apps'] as $app ) {
			if ( empty( $app['id'] ) ) {
				continue;
			}
	
			if ( absint( $app['id'] ) === $app_id ) {
				return absint( $server['id'] );
			}
		}

		}
	
			return 0;
		}

}