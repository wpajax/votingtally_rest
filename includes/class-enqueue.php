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
				'rest_url' => rest_url( 'votingtally/v1' ),
				'loading'  => VOTINGTALLY_URL . 'images/loading.svg',
			)
		);
		/**
		 * Filter so others can easily override styles of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @param bool True to load the styles.
		 */
		$enqueue_styles = apply_filters( 'voting_tally_enqueue_styles', true );
		if ( $enqueue_styles ) {
			wp_enqueue_style(
				'votingtally',
				VOTINGTALLY_URL . 'css/votingtally.css',
				array(),
				VOTINGTALLY_VERSION,
				'all'
			);
		}
	}
}
