<?php

namespace Automator_Connect\Integrations\Kinsta;

use Exception;
use Uncanny_Automator\Settings\Premium_Integration_Settings;

/**
 * Class Kinsta_Settings
 *
 * Premium integration settings page for the third-party Kinsta integration.
 *
 * @package Automator_Connect\Integrations\Kinsta
 */
class Kinsta_Settings extends Premium_Integration_Settings {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'KINSTA';

	/**
	 * Integration name.
	 */
	private const INTEGRATION_NAME = 'Kinsta';

	/**
	 * Settings page ID.
	 */
	private const SETTINGS_ID = 'kinsta';

	/**
	 * Kinsta API caller.
	 *
	 * @var Kinsta_Api_Caller
	 */
	private $caller;

	/**
	 * Kinsta API credentials.
	 *
	 * @var Kinsta_Api_Credentials
	 */
	private $credentials;

	/**
	 * Whether the integration is connected.
	 *
	 * @var bool
	 */
	private $is_connected = false;

	/**
	 * Set the settings page properties.
	 *
	 * @return void
	 *
	 * @throws Exception When required settings cannot be registered.
	 */
	public function set_properties() {
		$this->set_id( self::SETTINGS_ID );
		$this->set_icon( self::INTEGRATION_CODE );
		$this->set_name( esc_html__( self::INTEGRATION_NAME, 'automator-connect' ) );

		// Optionally, set the integration as a third party (default), with no credits required (default).
		$this->set_is_third_party( true );
		$this->set_requires_credits( false );
		$this->register_option( Kinsta_Api_Credentials::API_KEY_OPTION );
		$this->register_option( Kinsta_Api_Credentials::COMPANY_ID_OPTION );

		add_action( 'init', array( $this, 'disconnect' ) );
	}

	/**
	 * Return the integration status.
	 *
	 * The settings page is considered connected only when both credentials are
	 * stored in the database and are not empty.
	 *
	 * @return string 'success' when connected, otherwise an empty string.
	 */
	public function get_status() {
		if ( $this->get_credentials()->has_credentials() ) {
			$this->is_connected = true;
			$this->set_status( 'success' );

			return parent::get_status();
		}

		$this->is_connected = false;
		$this->set_status( '' );

		return parent::get_status();
	}

	/**
	 * Output the settings page content.
	 *
	 * @return void
	 */
	public function output_panel_content() {
		if ( ! $this->is_connected ) {
			$this->output_connection_fields();

			return;
		}

		?>
		<p>
			<?php echo esc_html__( 'Your Kinsta account is connected.', 'automator-connect' ); ?>
		</p>
		<?php
	}

	/**
	 * Output the bottom-right action button.
	 *
	 * @return void
	 */
	public function output_panel_bottom_right() {
		if ( ! $this->is_connected ) {
			$this->submit_button( esc_html__( 'Connect Account', 'automator-connect' ) );

			return;
		}

		$link = wp_nonce_url( $this->get_settings_page_url() . '&disconnect=1', 'ac_kinsta_disconnect' );

		$this->redirect_button(
			esc_html__( 'Disconnect', 'automator-connect' ),
			$link
		);
	}

	/**
	 * Validate and store the settings after they are updated.
	 *
	 * @return void
	 */
	public function settings_updated() {
		try {
			$response = $this->get_caller()->validate_api_key();

			$company_id = isset( $response['company'] ) && is_string( $response['company'] )
				? sanitize_text_field( $response['company'] )
				: '';

			if ( '' === $company_id ) {
				throw new Exception( esc_html__( 'Kinsta did not return an associated company ID.', 'automator-connect' ) );
			}

			if ( ! $this->get_credentials()->set_company_id( $company_id ) ) {
				throw new Exception( esc_html__( 'The Kinsta company ID could not be saved.', 'automator-connect' ) );
			}
		} catch ( Exception $e ) {
			$this->get_credentials()->clear_credentials();

			$this->add_alert(
				array(
					'type'    => 'error',
					'heading' => esc_html__( 'Unable to connect to Kinsta', 'automator-connect' ),
					'content' => sprintf(
						/* translators: %s: API error message. */
						esc_html__( 'The provided Kinsta API key could not be validated. Error: %s', 'automator-connect' ),
						esc_html( $e->getMessage() )
					),
				)
			);

			return;
		}

		$this->add_alert(
			array(
				'type'    => 'success',
				'heading' => esc_html__( 'Kinsta Account connected successfully', 'automator-connect' ),
				'content' => esc_html__( 'Your Kinsta API key has been validated and saved.', 'automator-connect' ),
			)
		);
	}

	/**
	 * Disconnect the integration.
	 *
	 * @return void
	 */
	public function disconnect() {
		if ( ! $this->is_current_page_settings() ) {
			return;
		}

		if ( '1' !== automator_filter_input( 'disconnect' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'ac_kinsta_disconnect' ); // Die on failure/missing nonce.

		$this->get_credentials()->clear_credentials();

		wp_safe_redirect( $this->get_settings_page_url() );
		exit;
	}

	/**
	 * Output the credential input fields.
	 *
	 * @return void
	 */
	private function output_connection_fields() {
		$this->text_input(
			array(
				'id'       => Kinsta_Api_Credentials::API_KEY_OPTION,
				'value'    => $this->get_credentials()->get_api_key(),
				'label'    => esc_html__( 'Kinsta API key', 'automator-connect' ),
				'required' => true,
			)
		);
	}

	/**
	 * Get the Kinsta API caller from the injected dependencies.
	 *
	 * @return Kinsta_Api_Caller
	 *
	 * @throws Exception When the caller dependency is missing.
	 */
	private function get_caller() {
		if ( $this->caller instanceof Kinsta_Api_Caller ) {
			return $this->caller;
		}

		if ( empty( $this->dependencies[1] ) || ! $this->dependencies[1] instanceof Kinsta_Api_Caller ) {
			throw new Exception( 'Kinsta API caller dependency is missing.' );
		}

		$this->caller = $this->dependencies[1];

		return $this->caller;
	}

	/**
	 * Get the Kinsta API credentials from the injected dependencies.
	 *
	 * @return Kinsta_Api_Credentials
	 *
	 * @throws Exception When the credentials dependency is missing.
	 */
	private function get_credentials() {
		if ( $this->credentials instanceof Kinsta_Api_Credentials ) {
			return $this->credentials;
		}

		if ( empty( $this->dependencies[2] ) || ! $this->dependencies[2] instanceof Kinsta_Api_Credentials ) {
			throw new Exception( 'Kinsta API credentials dependency is missing.' );
		}

		$this->credentials = $this->dependencies[2];

		return $this->credentials;
	}
}