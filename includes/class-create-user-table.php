<?php
/**
 * Creates and drops the table.
 *
 * @package votingtally
 */

namespace VotingTally\Includes;

/**
 * Class Create_Table
 */
class Create_User_Table {

	/**
	 * Holds the tablename for Voting Tally
	 *
	 * @var string $tablename
	 */
	private static $tablename = 'votingtally_users';

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		$this->create_table();
	}

	/**
	 * Retrieves the tablename for the plugin.
	 */
	public static function get_tablename() {
		global $wpdb;
		return $wpdb->base_prefix . self::$tablename;
	}
	/**
	 * Create Voting Tally table
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function create_table() {
		global $wpdb;
		$tablename = $wpdb->base_prefix . self::$tablename;

		$version = get_site_option( 'votingtallytable_user_version', '0' );
		if ( version_compare( $version, VOTINGTALLY_USER_TABLE_VERSION ) < 0 ) {
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}
			$sql = "CREATE TABLE {$tablename} (
				id INT(20) NOT NULL AUTO_INCREMENT,
				user_id INT (20) NOT NULL,
				post_id INT (20) NOT NULL,
				vote INT(2) NOT NULL,
				PRIMARY KEY  (id)
				) {$charset_collate};";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_site_option( 'votingtallytable_user_version', VOTINGTALLY_USER_TABLE_VERSION );
		}
	}
}
