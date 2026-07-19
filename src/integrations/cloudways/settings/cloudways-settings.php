<?php

namespace Automator_Connect\Integrations\Cloudways;

use Exception;
use Uncanny_Automator\Settings\Premium_Integration_Settings;

/**
 * Class Cloudways_Settings
 *
 * Premium integration settings page for the third-party Cloudways integration.
 * 
 * @package Automator_Connect\Integrations\Cloudways
 */
class Cloudways_Settings extends Premium_Integration_Settings {

	/**
	 * Integration code.
	 */
	private const INTEGRATION_CODE = 'CLOUDWAYS';

	/**
	 * Integration name.
	 */
	private const INTEGRATION_NAME = 'Cloudways';

	/**
	 * Settings page ID.
	 */
	private const SETTINGS_ID = 'cloudways';

	/**
	 * Cloudways API client.
	 *
	 * @var Cloudways_Api_Client
	 */
	private $client;

	/**
	 * Cloudways API credentials.
	 *
	 * @var Cloudways_Api_Credentials
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

		$this->register_option( Cloudways_Api_Credentials::EMAIL_OPTION );
		$this->register_option( Cloudways_Api_Credentials::API_KEY_OPTION );

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
			<?php echo esc_html__( 'Your Cloudways account is connected.', 'automator-connect' ); ?>
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

		$link = wp_nonce_url( $this->get_settings_page_url() . '&disconnect=1', 'ac_cloudways_disconnect' );

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
			$this->get_client()->authenticate();
		} catch ( Exception $e ) {
			$this->get_credentials()->clear_credentials();

			$this->add_alert(
				array(
					'type'    => 'error',
					'heading' => esc_html__( 'Unable to connect to Cloudways', 'automator-connect' ),
					'content' => sprintf(
						/* translators: %s: error message. */
						esc_html__( 'The provided Cloudways credentials could not be validated. Error: %s', 'automator-connect' ),
						esc_html( $e->getMessage() )
					),
				)
			);

			return;
		}

		$this->add_alert(
			array(
				'type'    => 'success',
				'heading' => esc_html__( 'Cloudways Account connected successfully', 'automator-connect' ),
				'content' => esc_html__( 'Your Cloudways credentials have been validated and saved.', 'automator-connect' ),
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

		check_admin_referer( 'ac_cloudways_disconnect' ); // Die on failure/missing nonce.
	
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
				'id'       => Cloudways_Api_Credentials::EMAIL_OPTION,
				'value'    => $this->get_credentials()->get_email(),
				'label'    => esc_html__( 'Cloudways email', 'automator-connect' ),
				'required' => true,
			)
		);

		$this->text_input(
			array(
				'id'       => Cloudways_Api_Credentials::API_KEY_OPTION,
				'value'    => $this->get_credentials()->get_api_key(),
				'label'    => esc_html__( 'Cloudways API key', 'automator-connect' ),
				'required' => true,
				'class'    => 'uap-spacing-top',
			)
		);
	}

	/**
	 * Get the Cloudways API client from the injected dependencies.
	 *
	 * @return Cloudways_Api_Client
	 *
	 * @throws Exception When the client dependency is missing.
	 */
	private function get_client() {
		if ( $this->client instanceof Cloudways_Api_Client ) {
			return $this->client;
		}

		if ( empty( $this->dependencies[1] ) || ! $this->dependencies[1] instanceof Cloudways_Api_Client ) {
			throw new Exception( 'Cloudways API client dependency is missing.' );
		}

		$this->client = $this->dependencies[1];

		return $this->client;
	}

	/**
	 * Get the Cloudways API credentials from the injected dependencies.
	 *
	 * @return Cloudways_Api_Credentials
	 *
	 * @throws Exception When the credentials dependency is missing.
	 */
	private function get_credentials() {
		if ( $this->credentials instanceof Cloudways_Api_Credentials ) {
			return $this->credentials;
		}

		if ( empty( $this->dependencies[2] ) || ! $this->dependencies[2] instanceof Cloudways_Api_Credentials ) {
			throw new Exception( 'Cloudways API credentials dependency is missing.' );
		}

		$this->credentials = $this->dependencies[2];

		return $this->credentials;
	}
}
