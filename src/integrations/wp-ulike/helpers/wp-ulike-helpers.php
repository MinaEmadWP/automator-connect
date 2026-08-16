<?php

namespace Automator_Connect\Integrations\WP_Ulike;

/**
 * Class WP_Ulike_Helpers
 * 
 * Provides helper methods used by the WP ULike integration.
 * 
 * @package Automator_Connect\Integrations\WP_Ulike
 */
class WP_Ulike_Helpers {

	/**
	 * Get registered post types for the trigger dropdown.
	 *
	 * The first option allows the trigger to fire for any post type.
	 *
	 * @return array
	 */
	public function get_post_types() {

		$options = array(
			array(
				'text'  => esc_html__( 'Any post type', 'automator-connect' ),
				'value' => '-1',
			),
		);

		$post_types = get_post_types( array(), 'objects' );

		foreach ( $post_types as $post_type ) {

			if ( ! $this->is_post_type_valid( $post_type ) ) {
				continue;
			}

			$options[] = array(
				'text'  => esc_html( $post_type->labels->singular_name ),
				'value' => $post_type->name,
			);
		}

		return $options;
	}

	/**
	 * Determine whether a post type should be available in the dropdown.
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 *
	 * @return bool
	 */
	private function is_post_type_valid( $post_type ) {

		$invalid_post_types = array(
			'attachment',
			'uo-action',
			'uo-closure',
			'uo-trigger',
			'uo-recipe',
			'customize_changeset',
			'custom_css',
			'wp_global_styles',
			'wp_template',
			'wp_template_part',
			'wp_block',
			'user_request',
			'oembed_cache',
			'revision',
			'wp_navigation',
			'nav_menu_item',
		);

		return ! in_array( $post_type->name, $invalid_post_types, true )
			&& ! empty( $post_type->name )
			&& ! empty( $post_type->labels->singular_name );
	}
}