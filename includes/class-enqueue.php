<?php
/**
 * Outputs the scripts necessary to initialize the interface.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Enqueue
 */
class Enqueue {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Output the Voting Tallery scripts/styles.
	 */
	public function enqueue_scripts() {
		if ( ! is_singular() || ! is_user_logged_in() ) {
			return;
		}
		wp_enqueue_script(
			'votingtally',
			VOTINGTALLY_URL . 'js/votingtally.js',
			array( 'jquery' ),
			VOTINGTALLY_VERSION,
			true
		);
		wp_localize_script(
			'votingtally',
			'votingtally',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'loading'       => VOTINGTALLY_URL . 'images/loading.svg',
				'vote_recorded' => __( 'Thanks! Your vote has been recorded.', 'votingtally' ),
				'vote_error'    => __( 'There was a problem recording your vote.', 'votingtally' ),
			)
		);
		wp_enqueue_style(
			'votingtally',
			VOTINGTALLY_URL . 'css/votingtally.css',
			array(),
			VOTINGTALLY_VERSION,
			'all'
		);
	}
}
