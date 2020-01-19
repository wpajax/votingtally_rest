<?php
/**
 * Outputs the Voting Tally Interface.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Output
 */
class Output {
	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'the_content', array( $this, 'maybe_output_interface' ) );
	}

	/**
	 * Output the Voting Tallery button interface.
	 *
	 * @param string $content The Post content.
	 *
	 * @return string Modified content.
	 */
	public function maybe_output_interface( $content ) {
		if ( ! is_singular() || ! is_user_logged_in() ) {
			return $content;
		}
		global $current_user;
		$vote = Helper_Functions::get_user_vote( get_queried_object_id(), $current_user->ID );
		ob_start();
		?>	
		<div class="voting-tally">
			<h5><?php esc_html_e( 'Rank This Post', 'votingtally' ); ?></h5>
			<button class="vote-upwards tally-button <?php echo 1 === $vote ? esc_attr( 'active' ) : ''; ?>" aria-label="<?php esc_attr_e( 'Vote this item up', 'votingtallery' ); ?>" data-nonce="<?php echo esc_html( wp_create_nonce( 'wp_rest' ) ); ?>" data-id="<?php echo absint( get_the_ID() ); ?>" data-action="1">
				<img src="<?php echo esc_url( VOTINGTALLY_URL . 'images/thumbs-up.png' ); ?>" alt="Thumbs Up Button" />
			</button>
			<button class="vote-downwards tally-button <?php echo 0 === $vote ? esc_attr( 'active' ) : ''; ?>" aria-label="<?php esc_attr_e( 'Vote this item down', 'votingtallery' ); ?>" data-nonce="<?php echo esc_html( wp_create_nonce( 'wp_rest' ) ); ?>" data-id="<?php echo absint( get_the_ID() ); ?>" data-action="0">
				<img src="<?php echo esc_url( VOTINGTALLY_URL . 'images/thumbs-down.png' ); ?>" alt="Thumbs Down Button" />
			</button>
			<div class=voting-tally-num-votes">
				<?php
				$num_votes = number_format( Helper_Functions::get_post_positive_votes( get_queried_object_id() ) );
				/* Translators: %s is the number of post likes */
				$message = sprintf( esc_html__( '%s Likes', 'votingtally' ), $num_votes );
				echo esc_html( $message );
				?>
			</div>
		</div>
		<?php
		return $content . ob_get_clean();
	}
}
