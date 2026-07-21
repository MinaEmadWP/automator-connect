<?php

namespace Automator_Connect\Integrations\Cloudways;

use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Caller;
use Exception;
use Uncanny_Automator\Recipe\Action;

/**
 * Class Cloudways_Create_App_Backup
 *
 * Start a Cloudways application backup process.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Create_App_Backup extends Action {

    /**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'CLOUDWAYS';

    /**
	 * Action code.
	 */
	private const ACTION_CODE = 'cloudways_create_app_backup';

	/**
	 * Server field option code.
	 */
	private const SERVER_ID = 'SERVER_ID';

	/**
	 * Application field option code.
	 */
	private const APP_ID = 'APP_ID';

	/**
	 * Operation ID token.
	 */
	private const OPERATION_ID_TOKEN = 'OPERATION_ID';

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
		$this->set_action_meta( self::APP_ID );

		$this->set_sentence(
			sprintf(
				esc_html__( 'Start a Cloudways backup for {{application:%1$s}}', 'automator-connect' ),
				$this->get_action_meta()
			)
		);

		$this->set_readable_sentence(
			esc_html__( 'Start a Cloudways application backup process', 'automator-connect' )
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
					'option_code' => self::APP_ID,
					'label'       => esc_html__( 'Application', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_app_options(),
					'placeholder' => esc_html__( 'Select An Application', 'automator-connect' ),
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

		$app_id = absint(
			Automator()->parse->text(
				$action_meta[ self::APP_ID ] ?? '',
				$recipe_id,
				$user_id,
				$args
			)
		);

		if ( 0 === $app_id ) {
			throw new Exception( esc_html__( 'Cloudways application is missing.', 'automator-connect' ) );
		}

		$server_id = $this->get_server_id( $app_id );

		if ( 0 === $server_id ) {
			throw new Exception( esc_html__( 'Cloudways server could not be resolved from the selected application.', 'automator-connect' ) );
		}

		$body = array(
			'server_id' => $server_id,
			'app_id'    => $app_id,
		);

		$response = $this->get_caller()->start_app_backup_process( $body );

		if ( ! is_array( $response ) || empty( $response['operation_id'] ) ) {
			throw new Exception( esc_html__( 'Cloudways did not return an operation ID.', 'automator-connect' ) );
		}

		$operation_id = sanitize_text_field( (string) $response['operation_id'] );

		$this->hydrate_tokens(
			array(
				self::OPERATION_ID_TOKEN => $operation_id,
			)
		);

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
	 * Get the application dropdown options.
	 *
	 * @return array
	 */
	private function get_app_options() {
		$helpers = $this->get_item_helpers();

		if ( ! is_object( $helpers ) || ! method_exists( $helpers, 'get_app_options' ) ) {
			return array();
		}

		return (array) $helpers->get_app_options();
	}

    /**
	 * Get the server ID from the application ID.
	 *
     * @param int|string $app_id Application ID.
	 * 
     * @return int Server ID, or 0 when not found.
	 */
	private function get_server_id( $app_id ) {
		$helpers = $this->get_item_helpers();

		if ( ! is_object( $helpers ) || ! method_exists( $helpers, 'get_server_id_from_app_id' ) ) {
			return 0;
		}

		return $helpers->get_server_id_from_app_id( $app_id );
	}
}
