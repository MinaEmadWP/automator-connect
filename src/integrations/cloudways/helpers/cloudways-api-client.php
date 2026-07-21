<?php

namespace Automator_Connect\Integrations\Cloudways;

use Exception;

/**
 * Class Cloudways_Api_Client
 *
 * Handles only authentication and HTTP communication with the Cloudways API.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Api_Client {

	/**
	 * Cloudways API base URL.
	 *
	 * @var string
	 */
	const BASE_URL = 'https://api.cloudways.com/api/v2';

	/**
	 * OAuth access token endpoint relative to the API base URL.
	 *
	 * @var string
	 */
	const AUTH_ENDPOINT = 'oauth/access_token';

	/**
	 * Default request timeout.
	 *
	 * @var int
	 */
	const DEFAULT_TIMEOUT = 15;

	/**
	 * Credentials handler.
	 *
	 * @var Cloudways_Api_Credentials
	 */
	private $credentials;

	/**
	 * Constructor.
	 *
	 * @param Cloudways_Api_Credentials $credentials Credentials handler.
	 */
	public function __construct( Cloudways_Api_Credentials $credentials ) {
		$this->credentials = $credentials;
	}

	/**
	 * Perform a GET request.
	 *
	 * @param string $endpoint   Relative API endpoint.
	 * @param string $path   	 Optional path parameter.
	 * @param array  $query_args Optional query arguments.
	 * @param array  $args       Optional request arguments.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function get( $endpoint, $path = '', array $query_args = array(), array $args = array() ) {
		$args['method']     = 'GET';
		$args['query_args'] = $query_args;
		$args['path']       = $path;

		return $this->request( $endpoint, $args );
	}

	/**
	 * Perform a POST request.
	 *
	 * @param string $endpoint Relative API endpoint.
	 * @param array  $body     Request body.
	 * @param array  $args     Optional request arguments.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function post( $endpoint, array $body = array(), array $args = array() ) {
		$args['method'] = 'POST';
		$args['body']   = $body;

		return $this->request( $endpoint, $args );
	}

	/**
	 * Perform a PUT request.
	 *
	 * @param string $endpoint       Relative API endpoint.
	 * @param array  $body           Request body.
	 * @param array  $query_args     Optional query arguments.
	 * @param array  $args           Optional request arguments.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function put( $endpoint, array $body = array(), array $query_args = array(), array $args = array() ) {
		$args['method'] = 'PUT';
		$args['query_args'] = $query_args;
		$args['body']   = $body;

		return $this->request( $endpoint, $args );
	}


	/**
	 * Perform a DELETE request.
	 *
	 * @param string $endpoint       Relative API endpoint.
	 * @param string $path   		 Optional path parameter.
	 * @param array  $query_args     Optional query arguments.
	 * @param array  $args           Optional request arguments.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the request fails.
	 */
	public function delete( $endpoint, $path = '', array $query_args = array(), array $args = array() ) {
		$args['method'] = 'DELETE';
		$args['query_args'] = $query_args;
		$args['path']   = $path;

		return $this->request( $endpoint, $args );
	}

	/**
	 * Perform a Cloudways API request.
	 *
	 * This method automatically authenticates if no cached access token exists.
	 * If Cloudways responds with an authentication-related status code, the
	 * client clears the cached token, re-authenticates once, and retries.
	 *
	 * All request-shaping options — method, body, query_args, path, timeout —
	 * are passed via the single $args array. This keeps every caller's
	 * invocation shape identical ( $endpoint, $args ), so there's no
	 * positional slot for an argument to be silently mismatched into.
	 *
	 * @param string $endpoint Relative API endpoint.
	 * @param array  $args     Optional request arguments. Supported keys:
	 *                         'method', 'body', 'query_args', 'path', 'timeout'.
	 *
	 * @return array Decoded response.
	 *
	 * @throws Exception When the request fails.
	 */
	private function request( $endpoint, array $args = array() ) {
		$endpoint = $this->normalize_endpoint( $endpoint );
		$method   = isset( $args['method'] ) ? strtoupper( (string) $args['method'] ) : 'GET';
		$timeout  = isset( $args['timeout'] ) ? absint( $args['timeout'] ) : $this->get_timeout();

		if ( $timeout < 1 ) {
			$timeout = $this->get_timeout();
		}

		$path       = isset( $args['path'] ) ? (string) $args['path'] : '';
		$body       = isset( $args['body'] ) && is_array( $args['body'] ) ? $args['body'] : array();
		$query_args = isset( $args['query_args'] ) && is_array( $args['query_args'] ) ? $args['query_args'] : array();

		$url 		  = $this->build_url( $endpoint, $path, $query_args );
		$access_token = $this->get_access_token();
		$headers	  = $this->build_headers( $access_token );

		$request_args = $this->build_request_args(
			$method,
			$timeout,
			$headers,
			$body
		);

		$response      = $this->send_request( $url, $request_args );
		$response_code = (int) wp_remote_retrieve_response_code( $response );
		$response_body = (string) wp_remote_retrieve_body( $response );

		// If authentication fails, refresh the access token and retry the request once.
        if ( $this->is_authentication_error( $response_code ) ) {
			$this->credentials->clear_access_token();

			$access_token             = $this->authenticate();
			$request_args['headers']  = $this->build_headers( $access_token );

			$response                 = $this->send_request( $url, $request_args );
			$response_code            = (int) wp_remote_retrieve_response_code( $response );
			$response_body            = (string) wp_remote_retrieve_body( $response );
		}

		$decoded_body = $this->decode_response_body(
			$response_body,
			esc_html__('Cloudways returned an invalid JSON response.', 'automator-connect' )
		);

		if ( $response_code < 200 || $response_code >= 300 ) {
			$message = $this->extract_error_message(
				$decoded_body,
				$response_body,
				esc_html__('Cloudways API request failed.', 'automator-connect' )
			);

			throw new Exception( $message, $response_code );
		}

		return $decoded_body;
	}

	/**
	 * Authenticate with Cloudways and cache the access token.
	 *
	 * @return string Access token.
	 *
	 * @throws Exception When authentication fails or the response is invalid.
	 */
	public function authenticate() {
		if ( ! $this->credentials->has_credentials() ) {
			throw new Exception( esc_html__( 'Cloudways API credentials are missing.', 'automator-connect' ) );
		}

		$request_args = array(
			'timeout' => $this->get_timeout(),
			'headers' => array(
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
			),
			'method'  => 'POST',
			'body'    => array(
				'email'      => $this->credentials->get_email(),
				'api_key'    => $this->credentials->get_api_key(),
                'grant_type' => 'password',
			),
		);

		$response      = $this->send_request( $this->get_auth_url(), $request_args );
		$response_code = (int) wp_remote_retrieve_response_code( $response );
		$response_body = (string) wp_remote_retrieve_body( $response );

		$decoded_body = $this->decode_response_body(
			$response_body,
			esc_html__('Cloudways returned an invalid JSON response during authentication.', 'automator-connect' )
		);

		if ( $response_code < 200 || $response_code >= 300 ) {
			$message = $this->extract_error_message(
				$decoded_body,
				$response_body,
				esc_html__('Cloudways authentication failed.', 'automator-connect' )
			);

			throw new Exception( $message, $response_code );
		}

		if ( empty( $decoded_body['access_token'] ) || ! is_string( $decoded_body['access_token'] ) ) {
			throw new Exception( esc_html__('Cloudways did not return a valid access token.', 'automator-connect' ), $response_code );
		}

		$access_token = trim( $decoded_body['access_token'] );

		if ( '' === $access_token ) {
			throw new Exception( esc_html__('Cloudways returned an empty access token.', 'automator-connect' ), $response_code );
		}

		$expires_in = isset( $decoded_body['expires_in'] ) ? absint( $decoded_body['expires_in'] ) : HOUR_IN_SECONDS;

		$stored = $this->credentials->set_access_token( $access_token,  $expires_in - 60 );

		if ( ! $stored ) {
			throw new Exception( esc_html__('Cloudways access token could not be cached.', 'automator-connect' ), $response_code );
		}

		return $access_token;
	}

	/**
	 * Return a valid access token, authenticating if necessary.
	 *
	 * @return string Access token.
	 *
	 * @throws Exception When authentication fails.
	 */
	private function get_access_token() {
		$access_token = $this->credentials->get_access_token();

		if ( '' !== $access_token ) {
			return $access_token;
		}

		return $this->authenticate();
	}

	/**
	 * Build the request URL.
	 *
	 * @param string $endpoint   Relative endpoint.
	 * @param string $path   Optional path parameter.
	 * @param array  $query_args Optional query arguments..
	 *
	 * @return string
	 */
	private function build_url( $endpoint, $path = '', array $query_args = array() ) {
		$url = trailingslashit( $this->get_base_url() ) .  $endpoint;
        $path = trim( (string) $path );

		if ( '' !== $path ) {
            $url = trailingslashit( $url ) . $path;
        }
        
        if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}

		return $url;
	}

	/**
	 * Build the request headers.
	 *
	 * @param string $access_token Access token.
	 *
	 * @return array
	 */
	private function build_headers( $access_token ) {
		return array(
			'Accept'       => 'application/json',
			'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
			'Authorization' => 'Bearer ' . $access_token,
		);
	}

	/**
	 * Build the request arguments for WordPress HTTP API.
	 *
	 * @param string $method  HTTP method.
	 * @param int    $timeout Request timeout.
	 * @param array  $headers HTTP headers.
	 * @param array  $body    Request body.
	 *
	 * @return array
	 */
	private function build_request_args( $method, $timeout, array $headers, array $body = array() ) {
		$request_args = array(
			'timeout' => $timeout,
			'headers' => $headers,
			'method'  => $method,
		);

		if ( ! empty( $body ) ) {
			$request_args['body'] = $body;
		}

		return $request_args;
	}

	/**
	 * Send the HTTP request.
	 *
	 * @param string $url          Request URL.
	 * @param array  $request_args Request arguments.
	 *
	 * @return array
	 *
	 * @throws Exception When the request fails at transport level.
	 */
	private function send_request( $url, array $request_args ) {
		$response = wp_remote_request( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		return $response;
	}

	/**
	 * Decode a JSON response body.
	 *
	 * @param string $body            Raw response body.
	 * @param string $context_message Message used when JSON decoding fails.
	 *
	 * @return array
	 *
	 * @throws Exception When the response is not valid JSON.
	 */
	private function decode_response_body( $body, $context_message ) {
		$body = trim( (string) $body );

		if ( '' === $body ) {
			return array();
		}

		$decoded_body = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			throw new Exception( $context_message );
		}

		if ( ! is_array( $decoded_body ) ) {
			return array();
		}

		return $decoded_body;
	}

	/**
	 * Extract a meaningful error message from a Cloudways response.
	 *
	 * @param array  $decoded_body    Decoded response body.
	 * @param string $raw_body        Raw response body.
	 * @param string $default_message Default fallback message.
	 *
	 * @return string
	 */
	private function extract_error_message( array $decoded_body, $raw_body, $default_message = '' ) {
		foreach ( array( 'error_description', 'message', 'error' ) as $field ) {
			if ( ! empty( $decoded_body[ $field ] ) && is_string( $decoded_body[ $field ] ) ) {
				return trim( wp_strip_all_tags( $decoded_body[ $field ] ) );
			}
		}

		$raw_body = trim( wp_strip_all_tags( (string) $raw_body ) );

		if ( '' !== $raw_body ) {
			return $raw_body;
		}

		return $default_message;
	}

	/**
	 * Determine whether the response code most likely indicates an authentication issue.
	 *
	 * @param int $response_code Response code.
	 *
	 * @return bool
	 */
	private function is_authentication_error( $response_code ) {
		return 401 === (int) $response_code;
	}

	/**
	 * Normalize the endpoint.
	 *
	 * @param string $endpoint Relative endpoint.
	 *
	 * @return string
	 */
	private function normalize_endpoint( $endpoint ) {
		return ltrim( (string) $endpoint, '/' );
	}

	/**
	 * Get the Cloudways API base URL.
	 *
	 * @return string
	 */
	private function get_base_url() {
		/**
		 * Filter the Cloudways API base URL.
		 *
		 * @param string $base_url Cloudways API base URL.
		 */
		return (string) apply_filters( 'ac_cloudways_api_base_url', self::BASE_URL );
	}

	/**
	 * Get the Cloudways OAuth token URL.
	 *
	 * @return string
	 */
	private function get_auth_url() {
		/**
		 * Filter the Cloudways OAuth token URL.
		 *
		 * @param string $auth_url Cloudways OAuth token URL.
		 */
		return (string) apply_filters(
			'ac_cloudways_auth_token_url',
			trailingslashit( $this->get_base_url() ) . $this->normalize_endpoint( self::AUTH_ENDPOINT )
		);
	}
    
    /**
	 * Get the Cloudways API request timeout.
	 *
	 * @return int
	 */
    private function get_timeout() {
        /**
         * Filters the Cloudways API request timeout.
         *
         * @param int $timeout Timeout in seconds.
         */
        return (int) apply_filters( 'ac_cloudways_api_timeout', self::DEFAULT_TIMEOUT );
    }
}
