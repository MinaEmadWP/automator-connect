<?php

namespace Automator_Connect\Integrations\Kinsta;

use Exception;
use Uncanny_Automator\Recipe\Action;

/**
 * Class Kinsta_Delete_Site
 *
 * Delete a Kinsta site.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_Delete_Site extends Action {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'KINSTA';

	/**
	 * Action code.
	 */
	private const ACTION_CODE = 'KINSTA_DELETE_SITE';

	/**
	 * Site ID field option code.
	 */
	private const SITE_ID = 'SITE_ID';

	/**
	 * Message token.
	 */
	private const MESSAGE_TOKEN = 'MESSAGE';

	/**
	 * Status code token.
	 */
	private const STATUS_CODE_TOKEN = 'STATUS_CODE';

	/**
	 * Operation ID token.
	 */
	private const OPERATION_ID_TOKEN = 'OPERATION_ID';

	/**
	 * Kinsta API caller.
	 *
	 * @var Kinsta_Api_Caller
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
		$this->set_action_meta( self::SITE_ID );

		$this->set_sentence(
			sprintf(
				esc_html__( 'Start deleting the Kinsta {{site:%1$s}}', 'automator-connect' ),
				$this->get_action_meta()
			)
		);

		$this->set_readable_sentence(
			esc_html__( 'Start deleting a Kinsta site', 'automator-connect' )
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
			Automator()->helpers->recipe->field->select(
				array(
					'option_code' => self::SITE_ID,
					'label'       => esc_html__( 'Site', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_site_options(),
					'placeholder' => esc_html__( 'Select a site', 'automator-connect' ),
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
			self::MESSAGE_TOKEN => array(
				'name' => esc_html__( 'Message', 'automator-connect' ),
				'type' => 'text',
			),
			self::STATUS_CODE_TOKEN => array(
				'name' => esc_html__( 'Status code', 'automator-connect' ),
				'type' => 'text',
			),
			self::OPERATION_ID_TOKEN => array(
				'name' => esc_html__( 'Operation ID', 'automator-connect' ),
				'type' => 'text',
			),
		);
	}

	/**
	 * Process the action.
	 *
	 * @param int   $user_id     User ID.
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

		$site_id = sanitize_key(
			(string) Automator()->parse->text(
				$action_meta[ self::SITE_ID ] ?? '',
				$recipe_id,
				$user_id,
				$args
			)
		);

		if ( '' === $site_id ) {
			throw new Exception( esc_html__( 'Kinsta site ID is missing.', 'automator-connect' ) );
		}

		$response = $this->get_caller()->delete_site( $site_id );

		if (
			! is_array( $response ) ||
			empty( $response['message'] ) ||
			! isset( $response['status'] ) ||
			empty( $response['operation_id'] )
		) {
			throw new Exception( esc_html__( 'Kinsta returned an invalid response.', 'automator-connect' ) );
		}

		$message      = sanitize_text_field( (string) $response['message'] );
		$status_code  = absint( $response['status'] );
		$operation_id = sanitize_text_field( (string) $response['operation_id'] );

		$this->hydrate_tokens(
			array(
				self::MESSAGE_TOKEN      => $message,
				self::STATUS_CODE_TOKEN  => $status_code,
				self::OPERATION_ID_TOKEN => $operation_id,
			)
		);

		return true;
	}

	/**
	 * Get the Kinsta API caller.
	 *
	 * @return Kinsta_Api_Caller
	 *
	 * @throws Exception When the caller dependency is missing.
	 */
	private function get_caller() {
		if ( $this->caller instanceof Kinsta_Api_Caller ) {
			return $this->caller;
		}

		if ( empty( $this->dependencies[1] ) || ! ( $this->dependencies[1] instanceof Kinsta_Api_Caller ) ) {
			throw new Exception( 'Kinsta API caller dependency is missing.' );
		}

		$this->caller = $this->dependencies[1];

		return $this->caller;
	}

	/**
	 * Get the site dropdown options.
	 *
	 * @return array
	 */
	private function get_site_options() {
		$helpers = $this->get_item_helpers();

		if ( ! is_object( $helpers ) || ! method_exists( $helpers, 'get_site_options' ) ) {
			return array();
		}

		return (array) $helpers->get_site_options();
	}
}