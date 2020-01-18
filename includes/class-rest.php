<?php
/**
 * Captures the Ajax Calls.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Enqueue
 */
class Rest {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		// Rest API.
		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );
	}

	/**
	 * Register the REST endpoints this plugin needs.
	 */
	public function register_rest_endpoints() {
		register_rest_route(
			'votingtally/v1',
			'/record_vote/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'rest_record_vote' ),
			)
		);
		register_rest_route(
			'votingtally/v1',
			'/get_posts/(?P<post_type>[a-zA-Z]+)/(?P<posts_per_page>\d+)/(?P<order>[A-Z]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'rest_get_posts' ),
			)
		);
	}

	/**
	 * Capture the Recorded Vote.
	 *
	 * @param array $request Request array passed via Ajax.
	 */
	public function rest_record_vote( $request ) {
		global $current_user;
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array() );
		}

		// Retrieve the vote.
		$vote = absint( $request['vote'] );

		// Retrieve the post ID.
		$post_id   = absint( $request['post_id'] );
		$post_type = get_post_type( $post_id );

		// Get the current site information.
		global $current_blog, $wpdb;
		$site_id = 1;
		$blog_id = 1;
		if ( is_multisite() ) {
			$site_id = absint( $current_blog->site_id );
			$blog_id = absint( $current_blog->blog_id );
		}

		// Check if the user has already voted.
		$user_table  = Create_User_Table::get_tablename();
		$user_result = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$user_table} where user_id = %d AND post_id = %d",
				$current_user->ID,
				$post_id
			)
		);
		if ( $user_result ) {
			wp_send_json_error(
				array(
					'message' => __( 'You have already voted!', 'votingtally' ),
				)
			);
		}

		// Get the post rating.
		$post_rating = $this->get_post_stats( $post_id );
		$tablename   = Create_Voting_Table::get_tablename();
		if ( ! $post_rating ) {
			// Insert new row because it doesn't exist.
			$wpdb->insert(
				$tablename,
				array(
					'site_id'    => absint( $site_id ),
					'blog_id'    => absint( $blog_id ),
					'content_id' => absint( $post_id ),
					'post_type'  => sanitize_text_field( $post_type ),
				),
				array( '%d', '%d', '%d', '%s' )
			);
			$post_rating              = new \stdClass();
			$post_rating->up_votes    = 0;
			$post_rating->down_votes  = 0;
			$post_rating->total_votes = 0;
		}
		$post_rating->total_votes = $post_rating->up_votes + $post_rating->down_votes;
		// Make sure results aren't negative.
		$post_rating->up_votes    = $post_rating->up_votes < 0 ? 0 : $post_rating->up_votes;
		$post_rating->down_votes  = $post_rating->down_votes < 0 ? 0 : $post_rating->down_votes;
		$post_rating->total_votes = $post_rating->total_votes < 0 ? 0 : $post_rating->total_votes;

		// Let's get the total of up and down votes.
		if ( 1 === $vote ) {
			$post_rating->up_votes += 1;
			$vote                   = true;
		} else {
			$post_rating->down_votes += 1;
			$vote                     = false;
		}

		// Update the post.
		$wpdb->update(
			$tablename,
			array(
				'up_votes'   => $post_rating->up_votes,
				'down_votes' => $post_rating->down_votes,
			),
			array(
				'site_id'    => $site_id,
				'blog_id'    => $blog_id,
				'content_id' => $post_id,
			),
			array( '%d', '%d', '%d' )
		);

		// Record the vote for the user.
		$user_table = Create_User_Table::get_tablename();
		$wpdb->insert(
			$user_table,
			array(
				'user_id' => $current_user->ID,
				'post_id' => $post_id,
				'vote'    => $vote,
			),
			array( '%d', '%d', '%d' )
		);

		// Spiffy, now let's calculate the total ratings for everything and update.
		$total_items = $wpdb->get_var( $wpdb->prepare( "select count( id ) from {$tablename} where site_id = %d and blog_id = %d and post_type = '%s'", $site_id, $blog_id, $post_type ) );

		$results = $wpdb->get_row( $wpdb->prepare( "select SUM(up_votes + down_votes) as total_votes, SUM( ( up_votes * 5 + down_votes * 1 ) / {$total_items} ) as ratings_sum  from {$tablename} where site_id = %d and blog_id = %d and post_type = %s and content_id = %d", $site_id, $blog_id, $post_type, $post_id ) );

		$args = array(
			'total_items'    => $total_items,
			'total_votes'    => $results->total_votes,
			'average_rating' => $results->ratings_sum / $total_items,
			'average_votes'  => $results->total_votes / $total_items,
		);

		$sql = $wpdb->prepare( "UPDATE {$tablename} set rating = ( ( {$args['average_votes']} * {$args['average_rating']} ) + ( ( up_votes + down_votes ) * ( up_votes * 5 + down_votes * 1 ) / ( up_votes + down_votes ) ) ) / {$args['average_votes']} + up_votes + down_votes where site_id = %d and blog_id = %d and post_type = %s and content_id = %d", $site_id, $blog_id, $post_type, $post_id );
		$wpdb->query( $sql );
		wp_send_json_success();
	}

	/**
	 * Get rating stats for a post ID.
	 *
	 * @param int $post_id The Post ID to retrieve stats for.
	 *
	 * @return mixed false on failure, object on success.
	 */
	private function get_post_stats( $post_id = 0 ) {
		global $current_blog, $wpdb;
		$site_id = 1;
		$blog_id = 1;
		if ( is_multisite() ) {
			$site_id = $current_blog->site_id;
			$blog_id = $current_blog->blog_id;
		}
		$tablename = Create_Voting_Table::get_tablename();
		$sql       = "select * from {$tablename} where content_id = %d and site_id = %d and blog_id = %d";
		$results   = $wpdb->get_row( $wpdb->prepare( $sql, $post_id, $site_id, $blog_id ) ); // phpcs:ignore
		if ( $results ) {
			return $results;
		}
		return false;
	}

	/**
	 * Get the popular posts.
	 *
	 * @param array $request Request array passed via Ajax.
	 */
	public function rest_get_posts( $request ) {
		$posts = Template_Functions::get_popular_posts(
			$request['post_type'],
			$request['posts_per_page'],
			$request['order']
		);
		if ( $posts ) {
			wp_send_json_success( $posts );
		}
		wp_send_json_error(
			array(
				'message' => __( 'Could not find any posts to display', 'votingtally' ),
			)
		);
	}
}
