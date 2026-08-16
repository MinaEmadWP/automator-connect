<?php

namespace Automator_Connect\Integrations\WP_Ulike;

use Uncanny_Automator\Recipe\Trigger;

/**
 * Class WP_Ulike_User_Unlikes_Comment
 *
 * Fires when a user unlikes a comment through WP ULike.
 * 
 * @package Automator_Connect\Integrations\WP_Ulike
 */
class WP_Ulike_User_Unlikes_Comment extends Trigger {

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
	const TRIGGER_CODE = 'USER_UNLIKES_COMMENT';

	/**
	 * Comment ID token code.
	 *
	 * @var string
	 */
	const COMMENT_ID = 'COMMENT_ID';

	/**
	 * Comment content token code.
	 *
	 * @var string
	 */
	const COMMENT_CONTENT = 'COMMENT_CONTENT';

	/**
	 * Comment URL token code.
	 *
	 * @var string
	 */
	const COMMENT_URL = 'COMMENT_URL';

	/**
	 * Comment post ID token code.
	 *
	 * @var string
	 */
	const COMMENT_POST_ID = 'COMMENT_POST_ID';

	/**
	 * User ID token code.
	 *
	 * @var string
	 */
	const USER_ID = 'USER_ID';

	/**
	 * WP ULike vote status for an unlike.
	 *
	 * @var string
	 */
	const UNLIKE_STATUS = 'unlike';

	/**
	 * WP ULike object type for comments.
	 *
	 * @var string
	 */
	const COMMENT_OBJECT_TYPE = 'comment';

	/**
	 * WP ULike comment module identifier.
	 *
	 * @var string
	 */
	const COMMENT_MODULE = 'ulike_comments';

	/**
	 * WP ULike hook argument containing the comment ID.
	 *
	 * @var int
	 */
	const ARG_COMMENT_ID = 0;

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
	 * WP ULike hook argument containing the module identifier.
	 *
	 * @var int
	 */
	const ARG_MODULE = 6;

	/**
	 * Set up the trigger.
	 *
	 * Defines the trigger identity, sentence and WP ULike hook.
	 *
	 * @return void
	 */
	protected function setup_trigger() {

		$this->set_integration( self::INTEGRATION_CODE );
		$this->set_trigger_code( self::TRIGGER_CODE );

		$this->set_sentence(
			esc_html__(
				'A user unlikes a comment',
				'automator-connect'
			)
		);

		$this->set_readable_sentence(
			esc_html__(
				'A user unlikes a comment',
				'automator-connect'
			)
		);

		$this->add_action( 'wp_ulike_after_process', 10, 9 );
	}

	/**
	 * Validate the trigger.
	 *
	 * WP ULike passes the vote data to the wp_ulike_after_process hook
	 * as nine arguments.
	 *
	 * For a comment unlike, the relevant arguments are:
	 *
	 * 0 - Comment ID.
	 * 2 - User ID.
	 * 3 - Vote status: like or unlike.
	 * 5 - Object type: comment.
	 * 6 - WP ULike module: ulike_comments.
	 *
	 * @param array $trigger   Trigger configuration.
	 * @param array $hook_args Arguments passed by WP ULike.
	 *
	 * @return bool
	 */
	public function validate( $trigger, $hook_args ) {

		// Make sure the expected WP ULike arguments are available.
		if (
			! isset(
				$hook_args[ self::ARG_COMMENT_ID ],
				$hook_args[ self::ARG_USER_ID ],
				$hook_args[ self::ARG_STATUS ],
				$hook_args[ self::ARG_OBJECT_TYPE ],
				$hook_args[ self::ARG_MODULE ]
			)
		) {
			return false;
		}

		// Only process actual unlikes, not likes.
		if ( self::UNLIKE_STATUS !== $hook_args[ self::ARG_STATUS ] ) {
			return false;
		}

		// Only process WP ULike votes associated with comments.
		if ( self::COMMENT_OBJECT_TYPE !== $hook_args[ self::ARG_OBJECT_TYPE ] ) {
			return false;
		}

		// Only process the WP ULike comments module.
		if ( self::COMMENT_MODULE !== $hook_args[ self::ARG_MODULE ] ) {
			return false;
		}

		$comment_id = absint( $hook_args[ self::ARG_COMMENT_ID ] );

		// Make sure the comment exists.
		if ( ! get_comment( $comment_id ) ) {
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
			'tokenId'   => self::COMMENT_ID,
			'tokenName' => esc_html__( 'Comment ID', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::COMMENT_CONTENT,
			'tokenName' => esc_html__( 'Comment Content', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::COMMENT_URL,
			'tokenName' => esc_html__( 'Comment URL', 'automator-connect' ),
			'tokenType' => 'text',
		);

		$tokens[] = array(
			'tokenId'   => self::COMMENT_POST_ID,
			'tokenName' => esc_html__( 'Comment Post ID', 'automator-connect' ),
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
	 * Populates token values using the comment ID and user ID supplied
	 * by WP ULike.
	 *
	 * @param array $trigger   Trigger configuration.
	 * @param array $hook_args Arguments passed by WP ULike.
	 *
	 * @return array
	 */
	public function hydrate_tokens( $trigger, $hook_args ) {

		$comment_id = absint( $hook_args[ self::ARG_COMMENT_ID ] );
		$user_id    = absint( $hook_args[ self::ARG_USER_ID ] );

		$comment = get_comment( $comment_id );

		return array(
			self::COMMENT_ID      => $comment_id,
			self::COMMENT_CONTENT => $comment ? $comment->comment_content : '',
			self::COMMENT_URL     => $comment ? get_comment_link( $comment_id ) : '',
			self::COMMENT_POST_ID => $comment ? absint( $comment->comment_post_ID ) : 0,
			self::USER_ID         => $user_id,
		);
	}
}