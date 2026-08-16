<?php

namespace Automator_Connect\Integrations\Kinsta;

use Exception;
use Uncanny_Automator\Recipe\Action;

/**
 * Class Kinsta_Create_Plain_Site
 *
 * Start a Kinsta plain site creation process.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_Create_Plain_Site extends Action {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'KINSTA';

	/**
	 * Action code.
	 */
	private const ACTION_CODE = 'KINSTA_CREATE_PLAIN_SITE';

	/**
	 * Site name field option code.
	 */
	private const SITE_NAME = 'SITE_NAME';

	/**
	 * Region field option code.
	 */
	private const REGION = 'REGION';

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
		$this->set_action_meta( self::SITE_NAME );

		$this->set_sentence(
			sprintf(
				esc_html__( 'Start creating a Kinsta plain site {{name:%1$s}} in {{region:%2$s}}', 'automator-connect' ),
				$this->get_action_meta(),
				self::REGION
			)
		);

		$this->set_readable_sentence(
			esc_html__( 'Start creating a Kinsta plain site', 'automator-connect' )
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
					'option_code' => self::SITE_NAME,
					'label'       => esc_html__( 'Site name', 'automator-connect' ),
					'required'    => true,
					'placeholder' => esc_html__( 'My site', 'automator-connect' ),
				)
			),
			Automator()->helpers->recipe->field->select(
				array(
					'option_code' => self::REGION,
					'label'       => esc_html__( 'Region', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_region_options(),
					'placeholder' => esc_html__( 'Select a region', 'automator-connect' ),
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

		$site_name = sanitize_text_field(
			(string) Automator()->parse->text(
				$action_meta[ self::SITE_NAME ] ?? '',
				$recipe_id,
				$user_id,
				$args
			)
		);

		$region = sanitize_key(
			(string) Automator()->parse->text(
				$action_meta[ self::REGION ] ?? '',
				$recipe_id,
				$user_id,
				$args
			)
		);

		if ( '' === $site_name ) {
			throw new Exception( esc_html__( 'Kinsta site name is missing.', 'automator-connect' ) );
		}

		if ( '' === $region ) {
			throw new Exception( esc_html__( 'Kinsta region is missing.', 'automator-connect' ) );
		}

		// The Caller gets the company ID from Credentials and adds it
        // to the 'company' key in the $body.
        $body = array(
			'display_name' => $site_name,
			'region'       => $region,
		);

		$response = $this->get_caller()->start_create_plain_site( $body );

		if (
			! is_array( $response ) ||
			empty( $response['message'] ) ||
			! isset( $response['status'] ) ||
			empty( $response['operation_id'] )
		) {
			throw new Exception( esc_html__( 'Kinsta returned an invalid response.', 'automator-connect' ) );
		}

		$message = sanitize_text_field( (string) $response['message'] );
		$status_code = absint( $response['status'] );
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
	 * Get the region dropdown options.
	 *
	 * @return array
	 */
	private function get_region_options() {
		$helpers = $this->get_item_helpers();

		if ( ! is_object( $helpers ) || ! method_exists( $helpers, 'get_region_options' ) ) {
			return array();
		}

		return (array) $helpers->get_region_options();
	}
}