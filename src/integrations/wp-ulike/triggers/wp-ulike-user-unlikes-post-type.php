<?php

namespace Automator_Connect\Integrations\WP_Ulike;

use Uncanny_Automator\Recipe\Trigger;

/**
 * Class WP_Ulike_User_Unlikes_Post_Type
 *
 * Fires when a user unlikes a post type through WP ULike.
 * 
 * @package Automator_Connect\Integrations\WP_Ulike
 */
class WP_Ulike_User_Unlikes_Post_Type extends Trigger {

	/**
	 * Integration code.
	 *
	 * @var string
	 */
	const INTEGRATION_CODE = 'WPULIKE';

	/**
	 * Trigger code.
	 *
	 * @var string
	 */
	const TRIGGER_CODE = 'USER_UNLIKES_POST_TYPE';

	/**
	 * Post type option code.
	 *
	 * @var string
	 */
	const POST_TYPE = 'POST_TYPE';

	/**
	 * Post ID token code.
	 *
	 * @var string
	 */
	const POST_ID = 'POST_ID';

	/**
	 * Post title token code.
	 *
	 * @var string
	 */
	const POST_TITLE = 'POST_TITLE';

	/**
	 * Post URL token code.
	 *
	 * @var string
	 */
	const POST_URL = 'POST_URL';

	/**
	 * User ID token code.
	 *
	 * @var string
	 */
	const USER_ID = 'USER_ID';

	/**
	 * Value used by WP ULike to indicate any post type.
	 *
	 * @var string
	 */
	const ANY_POST_TYPE = '-1';

	/**
	 * WP ULike vote status for an unlike.
	 *
	 * @var string
	 */
	const UNLIKE_STATUS = 'unlike';

	/**
	 * WP ULike object type for posts.
	 *
	 * @var string
	 */
	const POST_OBJECT_TYPE = 'post';

	/**
	 * WP ULike hook argument containing the post ID.
	 *
	 * @var int
	 */
	const ARG_POST_ID = 0;

	/**
	 * WP ULike hook argument containing the user ID.
	 *
	 * @var int
	 */
	const ARG_USER_ID = 2;

	/**
	 * WP ULike hook argument containing the vote status.
	 *
	 * @var int
	 */
	const ARG_STATUS = 3;

	/**
	 * WP ULike hook argument containing the object type.
	 *
	 * @var int
	 */
	const ARG_OBJECT_TYPE = 5;

	/**
	 * Helpers instance.
	 *
	 * @var WP_Ulike_Helpers
	 */
	protected $helpers;

	/**
	 * Set up the trigger.
	 *
	 * Defines the trigger identity, sentence, options and WP ULike hook.
	 *
	 * @return void
	 */
	protected function setup_trigger() {

		$this->helpers = array_shift( $this->dependencies );

		$this->set_integration( self::INTEGRATION_CODE );
		$this->set_trigger_code( self::TRIGGER_CODE );
		$this->set_trigger_meta( self::POST_TYPE );

		$this->set_sentence(
			sprintf(
				esc_html__(
					'A user unlikes {{a post type:%1$s}}',
					'automator-connect'
				),
				$this->get_trigger_meta()
			)
		);

		$this->set_readable_sentence(
			esc_html__(
				'A user unlikes {{a post type}}',
				'automator-connect'
			)
		);

		$this->add_action( 'wp_ulike_after_process', 10, 9 );
	}

	/**
	 * Define trigger options.
	 *
	 * Provides a post type selector so the trigger can either apply to
	 * any post type or be restricted to a specific post type.
	 *
	 * @return array
	 */
	public function options() {

		return array(
			Automator()->helpers->recipe->field->select(
				array(
					'option_code' => self::POST_TYPE,
					'label'       => esc_html__( 'Post type', 'automator-connect' ),
					'required'    => true,
					'options'     => $this->helpers->get_post_types(),
					'placeholder' => esc_html__( 'Select a post type', 'automator-connect' ),
				)
			),
		);
	}

	/**
	 * Validate the trigger.
	 *
	 * WP ULike passes the vote data to the wp_ulike_after_process hook
	 * as nine arguments.
	 *
	 * For a post unlike, the relevant arguments are:
	 *
	 * 0 - Post ID.
	 * 2 - User ID.
	 * 3 - Vote status: like or unlike.
	 * 5 - Object type: post.
	 *
	 * The actual WordPress post type is determined from the post ID, 
	 * not from the WP ULike object type.
	 *
	 * @param array $trigger   Trigger configuration.
	 * @param array $hook_args Arguments passed by WP ULike.
	 *
	 * @return bool
	 */
	public function validate( $trigger, $hook_args ) {

		// Make sure the trigger has a selected post type.
		if ( ! isset( $trigger['meta'][ self::POST_TYPE ] ) ) {
			return false;
		}

		// Make sure the expected WP ULike arguments are available.
		if (
			! isset(
				$hook_args[ self::ARG_POST_ID ],
				$hook_args[ self::ARG_USER_ID ],
				$hook_args[ self::ARG_STATUS ],
				$hook_args[ self::ARG_OBJECT_TYPE ]
			)
		) {
			return false;
		}

		// Only process actual unlikes, not likes.
		if ( self::UNLIKE_STATUS !== $hook_args[ self::ARG_STATUS ] ) {
			return false;
		}

		// Only process WP ULike votes associated with "post", as an object type.
		if ( self::POST_OBJECT_TYPE !== $hook_args[ self::ARG_OBJECT_TYPE ] ) {
			return false;
		}

		$post_id   = absint( $hook_args[ self::ARG_POST_ID ] );
		$post_type = get_post_type( $post_id );

		// Make sure the unliked post exists and has a valid post type.
		if ( ! $post_id || ! $post_type ) {
			return false;
		}

		$selected_post_type = $trigger['meta'][ self::POST_TYPE ];

		// If "Any post type" is selected, no post type comparison is needed.
		if ( self::ANY_POST_TYPE !== $selected_post_type && $selected_post_type !== $post_type ) {
			return false;
		}

		// If all conditions are met, trigger the recipe.
		return true;
	}

	/**
	 * Define trigger tokens.
	 *
	 * @param array $trigger Trigger configuration.
	 * @param array $tokens  Existing tokens.
	 *
	 * @return array
	 */
	public function define_tokens( $trigger, $tokens ) {

		$tokens[] = array(
			'tokenId'   => self::POST_ID,
			'tokenName' => esc_html__( 'Post ID', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::POST_TITLE,
			'tokenName' => esc_html__( 'Post Title', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::POST_URL,
			'tokenName' => esc_html__( 'Post URL', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::USER_ID,
			'tokenName' => esc_html__( 'User ID', 'automator-connect' ),
			'tokenType' => 'text',
		);

		return $tokens;
	}

	/**
	 * Hydrate trigger tokens.
	 *
	 * Populates token values using the post ID and user ID supplied by WP ULike.
	 *
	 * @param array $trigger   Trigger configuration.
	 * @param array $hook_args Arguments passed by WP ULike.
	 *
	 * @return array
	 */
	public function hydrate_tokens( $trigger, $hook_args ) {

		$post_id   = absint( $hook_args[ self::ARG_POST_ID ] );
		$user_id   = absint( $hook_args[ self::ARG_USER_ID ] );

		$post = get_post( $post_id );

		return array(
			self::POST_ID    => $post_id,
			self::POST_TITLE => $post ? $post->post_title : '',
			self::POST_URL   => $post ? get_permalink( $post_id ) : '',
			self::USER_ID    => $user_id,
		);
	}
}