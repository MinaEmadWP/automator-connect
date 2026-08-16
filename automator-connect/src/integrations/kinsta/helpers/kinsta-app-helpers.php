<?php

namespace Automator_Connect\Integrations\Kinsta;

use Exception;

/**
 * Class Kinsta_App_Helpers
 *
 * Provides helper methods for retrieving Kinsta data and building
 * dropdown options for the Kinsta integration.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_App_Helpers {

	/**
	 * Kinsta API caller.
	 *
	 * @var Kinsta_Api_Caller
	 */
	private $api_caller;

	/**
	 * Cached available regions list.
	 *
	 * @var array|null
	 */
	private $available_regions = null;

	/**
	 * Cached company sites list.
	 *
	 * @var array|null
	 */
	private $company_sites = null;

	/**
	 * Constructor.
	 *
	 * @param Kinsta_Api_Caller $api_caller Kinsta API caller.
	 */
	public function __construct( Kinsta_Api_Caller $api_caller ) {
		$this->api_caller = $api_caller;
	}

	/**
	 * Retrieve the available Kinsta regions.
	 *
	 * The response is cached for the duration of the current request so that
	 * region dropdowns reuse the same API response.
	 *
	 * @return array
	 */
	public function get_available_regions() {

		if ( null !== $this->available_regions ) {
			return $this->available_regions;
		}

		try {
			$response = $this->api_caller->list_available_regions();
		} catch ( Exception $e ) {
			$this->available_regions = array();

			return $this->available_regions;
		}

		if (
			! is_array( $response ) ||
			empty( $response['company'] ) ||
			! is_array( $response['company'] ) ||
			empty( $response['company']['available_regions'] ) ||
			! is_array( $response['company']['available_regions'] )
		) {
			$this->available_regions = array();

			return $this->available_regions;
		}

		$this->available_regions = $response['company']['available_regions'];

		return $this->available_regions;
	}

	/**
	 * Build region select options.
	 *
	 * @return array
	 */
	public function get_region_options() {

		$options = array();

		foreach ( $this->get_available_regions() as $region ) {

			if (
				! is_array( $region ) ||
				empty( $region['region'] ) ||
				empty( $region['name'] )
			) {
				continue;
			}

			$options[] = array(
				'text'  => trim( (string) $region['name'] ),
				'value' => trim( (string) $region['region'] ),
			);
		}

		return $options;
	}

	/**
	 * Retrieve the Kinsta company sites.
	 *
	 * The response is cached for the duration of the current request so that
	 * site dropdowns reuse the same API response.
	 *
	 * @return array
	 */
	public function get_company_sites() {

		if ( null !== $this->company_sites ) {
			return $this->company_sites;
		}

		try {
			$response = $this->api_caller->list_sites();
		} catch ( Exception $e ) {
			$this->company_sites = array();

			return $this->company_sites;
		}

		if (
			! is_array( $response ) ||
			empty( $response['company'] ) ||
			! is_array( $response['company'] ) ||
			empty( $response['company']['sites'] ) ||
			! is_array( $response['company']['sites'] )
		) {
			$this->company_sites = array();

			return $this->company_sites;
		}

		$this->company_sites = $response['company']['sites'];

		return $this->company_sites;
	}

	/**
	 * Build site select options.
	 *
	 * @return array
	 */
	public function get_site_options() {

		$options = array();

		foreach ( $this->get_company_sites() as $site ) {

			if (
				! is_array( $site ) ||
				empty( $site['id'] ) ||
				empty( $site['display_name'] )
			) {
				continue;
			}

			$options[] = array(
				'text'  => trim( (string) $site['display_name'] ),
				'value' => trim( (string) $site['id'] ),
			);
		}

		return $options;
	}
}