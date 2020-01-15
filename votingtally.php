<?php // phpcs:ignore
/**
 * Voting Tally Plugin
 *
 * @package   votingtally
 * @copyright Copyright(c) 2020, MediaRon LLC
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * Plugin Name: Voting Tally
 * Plugin URI: https://github.com/wpajax/votingtally
 * Description: A way to up-and-down-vote posts including post types.
 * Version: 1.0.0
 * Author: MediaRon LLC
 * Author URI: https://wpandajax.com
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: votingtally
 * Domain Path: languages
 */

define( 'VOTINGTALLY_VERSION', '1.0.0' );
define( 'VOTINGTALLY_TABLE_VERSION', '1.0.0' );
define( 'VOTINGTALLY_PLUGIN_NAME', 'Voting Tally' );
define( 'VOTINGTALLY_DIR', plugin_dir_path( __FILE__ ) );
define( 'VOTINGTALLY_URL', plugins_url( '/', __FILE__ ) );
define( 'VOTINGTALLY_SLUG', plugin_basename( __FILE__ ) );
define( 'VOTINGTALLY_FILE', __FILE__ );

// Setup the plugin auto loader.
require_once 'autoloader.php';

/**
 * The Voting Tally base class.
 */
class Voting_Tally {

	/**
	 * Voting_Tally instance.
	 *
	 * @var Voting_Tally $instance
	 */
	private static $instance = null;

	/**
	 * Return a class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class Constructor
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 20 );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Fired when the init action for WordPress is triggered.
	 */
	public function init() {
		load_plugin_textdomain( 'votingtally', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Fired when the plugins for WordPress have finished loading.
	 */
	public function plugins_loaded() {
		// Create the table.
		new VotingTally\Includes\Create_Table();

		// Output the Voting Talley interface.
		new VotingTally\Includes\Output();

		// Enqueue the necessary scripts/styles.
		new VotingTally\Includes\Enqueue();

		// Register Ajax Call.
		new VotingTally\Includes\Ajax();
	}
}
Voting_Tally::get_instance();
