<?php
/**
 * Shortcode for outputting posts.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Output
 */
class Shortcode {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_shortcode( 'votingtally', array( $this, 'shortcode_votingtally' ) );
		add_shortcode( 'votingtally_user', array( $this, 'shortcode_votingtally_user' ) );
	}

	/**
	 * Output the Voting Talley popular posts items.
	 *
	 * @param array $atts The shortcode attributes.
	 *
	 * @return string Shortcode content.
	 */
	public function shortcode_votingtally( $atts ) {
		if ( is_admin() ) {
			return '';
		}
		$atts        = shortcode_atts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'order'          => 'DESC',
			),
			$atts,
			'votingtally'
		);
		$body        = array(
			'post_type'      => $atts['post_type'],
			'posts_per_page' => $atts['posts_per_page'],
			'order'          => $atts['order'],
		);
		$endpoint = sprintf(
			'/votingtally/v1/get_posts/%s/%d/%s',
			$atts['post_type'],
			$atts['posts_per_page'],
			$atts['order']
		);
		$maybe_posts = wp_safe_remote_get(
			esc_url( rest_url( $endpoint ) )
		);
		if ( is_wp_error( $maybe_posts ) ) {
			return '';
		}
		$remote_body = json_decode( wp_remote_retrieve_body( $maybe_posts ) );
		if ( true === $remote_body->success && count( $remote_body->data ) > 0 ) {
			ob_start();
			printf(
				'<h2>%s</h2>',
				esc_html__( 'Popular Items', 'votingtally' )
			);
			echo '<ol>';
			foreach ( $remote_body->data as $post_data ) {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url( $post_data->permalink ),
					esc_html( $post_data->title )
				);
			}
			echo '</ol>';
			return ob_get_clean();
		}
		return '';
	}

	/**
	 * Output the recent posts a user has voted for.
	 *
	 * @param array $atts The shortcode attributes.
	 *
	 * @return string Shortcode content.
	 */
	public function shortcode_votingtally_user( $atts ) {
		if ( is_admin() || ! is_user_logged_in() ) {
			return '';
		}
		global $current_user;
		$user_id = $current_user->ID;
		$posts   = Template_Functions::get_recent_votes_for_user( $user_id );
		if ( $posts ) {
			ob_start();
			printf(
				'<h2>%s</h2>',
				esc_html__( 'Your Recent Votes', 'votingtally' )
			);
			echo '<ol>';
			foreach ( $posts as $post_data ) {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url( $post_data->permalink ),
					esc_html( $post_data->title )
				);
			}
			echo '</ol>';
			return ob_get_clean();
		}
		return '';
	}
}
