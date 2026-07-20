<?php

namespace Automator_Connect\Integrations\Cloudways;

use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Caller;
use Exception;
use Uncanny_Automator\Recipe\Action;

/**
 * Class Cloudways_Get_Operation_Status
 *
 * Get the status of a Cloudways operation.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Get_Operation_Status extends Action {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'CLOUDWAYS';

	/**
	 * Action code.
	 */
	private const ACTION_CODE = 'cloudways_get_operation_status';

	/**
	 * Operation ID field option code.
	 */
	private const OPERATION_ID = 'OPERATION_ID';

	/**
	 * Token: operation type.
	 */
	private const TOKEN_OPERATION_TYPE = 'OPERATION_TYPE';

	/**
	 * Token: server ID.
	 */
	private const TOKEN_OPERATION_SERVER_ID = 'OPERATION_SERVER_ID';

	/**
	 * Token: estimated time remaining.
	 */
	private const TOKEN_OPERATION_ESTIMATED_TIME_REMAINING = 'OPERATION_ESTIMATED_TIME_REMAINING';

	/**
	 * Token: frontend step number.
	 */
	private const TOKEN_OPERATION_FRONTEND_STEP_NUMBER = 'OPERATION_FRONTEND_STEP_NUMBER';

	/**
	 * Token: operation status.
	 */
	private const TOKEN_OPERATION_STATUS = 'OPERATION_STATUS';

	/**
	 * Token: is completed.
	 */
	private const TOKEN_OPERATION_IS_COMPLETED = 'OPERATION_IS_COMPLETED';

	/**
	 * Token: message.
	 */
	private const TOKEN_OPERATION_MESSAGE = 'OPERATION_MESSAGE';

	/**
	 * Token: app ID.
	 */
	private const TOKEN_OPERATION_APP_ID = 'OPERATION_APP_ID';

	/**
	 * Token: app label.
	 */
	private const TOKEN_OPERATION_APP_LABEL = 'OPERATION_APP_LABEL';

	/**
	 * Token: raw operation response.
	 */
	private const TOKEN_OPERATION_RAW_RESPONSE = 'OPERATION_RAW_RESPONSE';

	/**
	 * Cloudways API caller.
	 *
	 * @var Cloudways_Api_Caller
	 */
	private $caller;

	/**
	 * Set up the action.
	 *
	 * @return void
	 */
	protected function setup_action() {
		$this->set_integration( self::INTEGRATION_CODE );
		$this->set_action_code( self::ACTION_CODE );
		$this->set_action_meta( self::OPERATION_ID );

		$this->set_sentence(
			sprintf(
				esc_html__( 'Get the status of Cloudways operation {{operation ID:%1$s}}', 'automator-connect' ),
				$this->get_action_meta()
			)
		);

		$this->set_readable_sentence(
			esc_html__( 'Get the status of a Cloudways operation', 'automator-connect' )
		);

		$this->set_background_processing( false );
		$this->set_requires_user( false );
	}

	/**
	 * Return the action fields.
	 *
	 * @return array
	 */
	public function options() {
		return array(
			Automator()->helpers->recipe->field->text(
				array(
					'option_code' => self::OPERATION_ID,
					'label'       => esc_html__( 'Operation ID', 'automator-connect' ),
					'required'    => true,
					'placeholder' => esc_html__( 'Enter the operation ID or use a token', 'automator-connect' ),
				)
			),
		);
	}

	/**
	 * Define the tokens available to subsequent actions.
	 *
	 * @return array
	 */
	public function define_tokens() {
		return array(
			self::TOKEN_OPERATION_TYPE => array(
				'name' => esc_html__( 'Operation type', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_SERVER_ID => array(
				'name' => esc_html__( 'Server ID', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_ESTIMATED_TIME_REMAINING => array(
				'name' => esc_html__( 'Estimated time remaining', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_FRONTEND_STEP_NUMBER => array(
				'name' => esc_html__( 'Frontend step number', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_STATUS => array(
				'name' => esc_html__( 'Status', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_IS_COMPLETED => array(
				'name' => esc_html__( 'Is completed', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_MESSAGE => array(
				'name' => esc_html__( 'Message', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_APP_ID => array(
				'name' => esc_html__( 'App ID', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_APP_LABEL => array(
				'name' => esc_html__( 'App Label', 'automator-connect' ),
				'type' => 'text',
			),
			self::TOKEN_OPERATION_RAW_RESPONSE => array(
				'name' => esc_html__( 'Raw operation response', 'automator-connect' ),
				'type' => 'text',
			),
		);
	}

	/**
	 * Process the action.
	 *
	 * @param int   $user_id    User ID.
	 * @param array $action_data Action data.
	 * @param int   $recipe_id   Recipe ID.
	 * @param array $args        Action args.
	 * @param array $parsed      Parsed values.
	 *
	 * @return bool
	 *
	 * @throws Exception When the action cannot be completed.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$action_meta = isset( $action_data['meta'] ) && is_array( $action_data['meta'] ) ? $action_data['meta'] : array();

		$operation_id = trim(
			(string) Automator()->parse->text(
				$action_meta[ self::OPERATION_ID ] ?? '',
				$recipe_id,
				$user_id,
				$args
			)
		);

		if ( '' === $operation_id ) {
			throw new Exception( esc_html__( 'Cloudways operation ID is missing.', 'automator-connect' ) );
		}

		$response = $this->get_caller()->get_operation_status( absint( $operation_id ) );

		if ( ! is_array( $response ) ) {
			throw new Exception( esc_html__( 'Cloudways returned an invalid response for the operation status.', 'automator-connect' ) );
		}

		$operation = ! empty( $response['operation'] ) && is_array( $response['operation'] )
			? $response['operation']
			: $response;

		if ( empty( $operation ) ) {
			throw new Exception( esc_html__( 'Cloudways did not return any operation data.', 'automator-connect' ) );
		}

		$tokens = array();

		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_TYPE, $operation['type'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_SERVER_ID, $operation['server_id'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_ESTIMATED_TIME_REMAINING, $operation['estimated_time_remaining'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_FRONTEND_STEP_NUMBER, $operation['frontend_step_number'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_STATUS, $operation['status'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_IS_COMPLETED, $operation['is_completed'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_MESSAGE, $operation['message'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_APP_ID, $operation['app_id'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_APP_LABEL, $operation['app_label'] ?? '' );
		$this->set_token_if_not_empty( $tokens, self::TOKEN_OPERATION_RAW_RESPONSE, $operation );

		$this->hydrate_tokens( $tokens );

		return true;
	}

	/**
	 * Get the Cloudways API caller.
	 *
	 * @return Cloudways_Api_Caller
	 *
	 * @throws Exception When the caller dependency is missing.
	 */
	private function get_caller() {
		if ( $this->caller instanceof Cloudways_Api_Caller ) {
			return $this->caller;
		}

		if ( empty( $this->dependencies[1] ) || ! ( $this->dependencies[1] instanceof Cloudways_Api_Caller ) ) {
			throw new Exception( esc_html__( 'Cloudways API caller dependency is missing.', 'automator-connect' ) );
		}

		$this->caller = $this->dependencies[1];

		return $this->caller;
	}

	/**
	 * Set a token value when it is not empty.
	 *
	 * Keeps values like "0" and 0.
	 *
	 * @param array  $tokens Tokens array passed by reference.
	 * @param string $token  Token key.
	 * @param mixed  $value  Token value.
	 *
	 * @return void
	 */
	private function set_token_if_not_empty( array &$tokens, $token, $value ) {
		if ( null === $value ) {
			return;
		}

		if ( is_string( $value ) ) {
			if ( '' === trim( $value ) ) {
				return;
			}

			$value = sanitize_text_field( $value );

			$tokens[ $token ] = trim( $value );

			return;
		}

		if ( is_bool( $value ) ) {
			$tokens[ $token ] = $value ? 'true' : 'false';

			return;
		}

		if ( is_array( $value ) || is_object( $value ) ) {
			$encoded = wp_json_encode( $value );

			if ( false !== $encoded && '' !== $encoded ) {
				$tokens[ $token ] = wp_strip_all_tags( $encoded );
			}

			return;
		}

		$tokens[ $token ] = (string) $value;
	}
}
