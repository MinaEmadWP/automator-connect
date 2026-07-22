<?php

namespace Automator_Connect\Integrations\Cloudways;

use Automator_Connect\Integrations\Cloudways\Cloudways_Api_Caller;
use Exception;
use Uncanny_Automator\Recipe\Action;

/**
 * Class Cloudways_Add_App
 *
 * Start a Cloudways application creation process.
 *
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Add_App extends Action {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'CLOUDWAYS';
	
	/**
	 * Action code.
	 */
	private const ACTION_CODE = 'CLOUDWAYS_ADD_APP';

	/**
	 * Server field option code.
	 */
	private const SERVER_ID = 'SERVER_ID';

	/**
	 * Application name field option code.
	 */
	private const APP_NAME = 'APP_NAME';

	/**
	 * Application type field option code.
	 */
	private const APP_TYPE = 'APP_TYPE';

	/**
	 * Application stack version option code.
	 */
	 private const STACK_VERSION = 'STACK_VERSION';

	/**
	 * Project ID field option code.
	 */
	private const PROJECT_NAME = 'PROJECT_NAME';

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
		$this->set_action_meta( self::SERVER_ID );

		$this->set_sentence(
			sprintf(
				esc_html__( 'Start adding a Cloudways application on {{server:%1$s}}', 'automator-connect' ),
				$this->get_action_meta()
			)
		);

		$this->set_readable_sentence(
			esc_html__( 'Start adding a Cloudways application', 'automator-connect' )
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
					'option_code' => self::SERVER_ID,
					'label'       => esc_html__( 'Server', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_server_options(),
					'placeholder' => esc_html__( 'Select a server', 'automator-connect' ),
				)
			),
			Automator()->helpers->recipe->field->text(
				array(
					'option_code' => self::APP_NAME,
					'label'       => esc_html__( 'Application name', 'automator-connect' ),
					'required'    => true,
					'placeholder' => esc_html__( 'My application', 'automator-connect' ),
				)
			),
			Automator()->helpers->recipe->field->select(
				array(
					'option_code' => self::APP_TYPE,
					'label'       => esc_html__( 'Application type', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_application_type_options(),
					'placeholder' => esc_html__( 'Select an application type', 'automator-connect' ),
				)
			),
			Automator()->helpers->recipe->field->select(
				array(
					'option_code' => self::STACK_VERSION,
					'label'       => esc_html__( 'Stack version', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->get_stack_version_options(),
					'placeholder' => esc_html__( 'Select a stack version', 'automator-connect' ),
				)
			),
			Automator()->helpers->recipe->field->text(
				array(
					'option_code' => self::PROJECT_NAME,
					'label'       => esc_html__( 'Project name', 'automator-connect' ),
					'required'    => false,
					'placeholder' => esc_html__( 'Optional', 'automator-connect' ),
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
	 * @param int   $user_id   User ID.
	 * @param array $action_data Action data.
	 * @param int   $recipe_id  Recipe ID.
	 * @param array $args       Action args.
	 * @param array $parsed     Parsed values.
	 *
	 * @return bool
	 *
	 * @throws Exception When the action cannot be completed.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$action_meta = isset( $action_data['meta'] ) && is_array( $action_data['meta'] ) ? $action_data['meta'] : array();

		$server_id  = absint( Automator()->parse->text( $action_meta[ self::SERVER_ID ] ?? '', $recipe_id, $user_id, $args ) );
		$app_label   = sanitize_text_field( (string) Automator()->parse->text( $action_meta[ self::APP_NAME ] ?? '', $recipe_id, $user_id, $args ) );
		$app_type   = sanitize_key( (string) Automator()->parse->text( $action_meta[ self::APP_TYPE ] ?? '', $recipe_id, $user_id, $args ) );
		$stack_version   = sanitize_key( (string) Automator()->parse->text( $action_meta[ self::STACK_VERSION ] ?? '', $recipe_id, $user_id, $args ) );
		$project_name = sanitize_text_field( (string) Automator()->parse->text( $action_meta[ self::PROJECT_NAME ] ?? '', $recipe_id, $user_id, $args ) );

		if ( 0 === $server_id ) {
			throw new Exception( esc_html__( 'Cloudways server is missing.', 'automator-connect' ) );
		}

		if ( '' === $app_label ) {
			throw new Exception( esc_html__( 'Cloudways application name is missing.', 'automator-connect' ) );
		}

		if ( '' === $app_type ) {
			throw new Exception( esc_html__( 'Cloudways application type is missing.', 'automator-connect' ) );
		}

		if ( '' === $stack_version ) {
			throw new Exception( esc_html__( 'Cloudways stack version is missing.', 'automator-connect' ) );
		}

		$body = array(
			'server_id' => $server_id,
			'app_label'  => $app_label,
			'application'  => $app_type,
            'stack_version' => $stack_version,
		);

		if ( '' !== $project_name ) {
			$body['project_name'] = $project_name;
		}

		$response = $this->get_caller()->start_add_app_process( $body );

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
			throw new Exception( 'Cloudways API caller dependency is missing.' );
		}

		$this->caller = $this->dependencies[1];

		return $this->caller;
	}

	/**
	 * Get the server dropdown options.
	 *
	 * @return array
	 */
	private function get_server_options() {
		$helpers = $this->get_item_helpers();

		if ( ! is_object( $helpers ) || ! method_exists( $helpers, 'get_server_options' ) ) {
			return array();
		}

		return (array) $helpers->get_server_options();
	}

	/**
	 * Get the application type dropdown options.
	 *
	 * @return array
	 */
	private function get_application_type_options() {
		return array(
			array(
				'text'  => esc_html__( 'WordPress', 'automator-connect' ),
				'value' => 'wordpress',
			),
			array(
				'text'  => esc_html__( 'WooCommerce', 'automator-connect' ),
				'value' => 'woocommerce',
			),
			array(
				'text'  => esc_html__( 'Wordpress Multisite', 'automator-connect' ),
				'value' => 'wordpressmu',
			),
			array(
				'text'  => esc_html__( 'Magento', 'automator-connect' ),
				'value' => 'magento',
			),
			array(
				'text'  => esc_html__( 'PHP', 'automator-connect' ),
				'value' => 'phpstack',
			),
			array(
				'text'  => esc_html__( 'Laravel', 'automator-connect' ),
				'value' => 'phplaravel',
			),
		);
	}

	/**
	 * Get the stack version dropdown options.
	 *
	 * @return array
	 */
	private function get_stack_version_options() {
		return array(
			array(
				'text'  => esc_html__( 'Lightning stack', 'automator-connect' ),
				'value' => 'v2',
			),
			array(
				'text'  => esc_html__( 'Hybrid stack', 'automator-connect' ),
				'value' => 'v1',
			),
		);
	}
}
