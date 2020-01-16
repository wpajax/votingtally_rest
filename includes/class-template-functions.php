<?php
/**
 * Template functions for displaying Tally's.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Enqueue
 */
class Template_Functions {

	/**
	 * Retrieve the most popular posts.
	 *
	 * @param string $post_type The post type to retrieve stats for.
	 * @param int    $posts_per_page The number of items to retrieve.
	 * @param string $order ASC or DESC order.
	 *
	 * @return mixed Object on return, false on failure.
	 */
	public static function get_popular_posts( $post_type, $posts_per_page = 10, $order = 'DESC' ) {
		global $wpdb;
		$post_type      = sanitize_text_field( $post_type );
		$posts_per_page = absint( $posts_per_page );
		$orderby        = sanitize_sql_orderby( 'rating ' . $order );

		// Try to retrieve the cache. Cache by namespace (e.g., votingtally_posts_ASC_24).
		$cache_key = sprintf(
			'votingtally_%s_%s_%d',
			$post_type,
			$order,
			$posts_per_page
		);
		$cache     = wp_cache_get( $cache_key );
		if ( $cache ) {
			return $cache;
		}

		$tablename = Create_Voting_Table::get_tablename();
		$query     = "select * from {$tablename} WHERE post_type = %s order by {$orderby} LIMIT {$posts_per_page}";
		$query     = $wpdb->prepare( $query, $post_type );
		$results   = $wpdb->get_results( $query );

		if ( $results ) {
			foreach ( $results as &$result ) {
				$result->permalink = get_permalink( $result->content_id );
				$result->title     = get_the_title( $result->content_id );
			}
			wp_cache_set( $cache_key, $results, '', 600 ); // Cache for 10 minutes.
			return $results;
		}
		return false;
	}

	/**
	 * Retrieve Up-voted Posts for User.
	 *
	 * @param int $user_id The User ID to retrieve posts for.
	 *
	 * @return mixed Object on return, false on failure.
	 */
	public static function get_recent_votes_for_user( $user_id ) {
		global $wpdb;
		$user_id = absint( $user_id );

		// Try to retrieve the cache. Cache by namespace (e.g., votingtally_posts_ASC_24).
		$cache_key = sprintf(
			'votingtally_user_%d',
			$user_id
		);
		$cache     = wp_cache_get( $cache_key );
		if ( $cache ) {
			return $cache;
		}

		$tablename = Create_User_Table::get_tablename();
		$query     = "select * from {$tablename} WHERE user_id = %d order by id DESC LIMIT 20";
		$query     = $wpdb->prepare( $query, $user_id );
		$results   = $wpdb->get_results( $query );

		if ( $results ) {
			foreach ( $results as &$result ) {
				$result->permalink = get_permalink( $result->post_id );
				$result->title     = get_the_title( $result->post_id );
			}
			wp_cache_set( $cache_key, $results, '', 600 ); // Cache for 10 minutes.
			return $results;
		}
		return false;
	}
}
