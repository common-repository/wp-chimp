<?php
/**
 * Lists: Table class
 *
 * @package WP_Chimp\Core\Lists
 * @since 0.1.0
 */

namespace WP_Chimp\Core\Lists;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

use function WP_Chimp\Core\get_the_mailchimp_api_key_status;
use function WP_Chimp\Core\is_lists_init;
use WP_Chimp\Core\Database;
use WP_Chimp\Core\Hooks_Trait;

/**
 * Class to register the custom, `chimp_lists`, table to store the MailChimp lists in the database.
 *
 * @since 0.1.0
 * @since 0.3.0 Adds the `$loader` property, and `set_loder` and `run` method.
 *
 * @property string $name
 * @property string $version
 */
class Database_Table extends Database {

	/**
	 * Use traits.
	 *
	 * @since 0.7.0
	 */
	use Hooks_Trait;

	/**
	 * Table name
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $name = 'chimp_lists';

	/**
	 * Database version.
	 *
	 * @since 0.1.0
	 * @var int
	 */
	protected $version = 201810100002; // 10 October 2018 ver. 02.

	/**
	 * Check if the database has just been upgraded.
	 *
	 * @since 0.5.0
	 * @var array
	 */
	protected $db_upgraded;

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 0.3.0
	 */
	public function run() {

		$this->get_hooks()->add_action( 'switch_blog', $this, 'switch_blog' );
		$this->get_hooks()->add_action( 'admin_init', $this, 'maybe_upgrade' );
		$this->get_hooks()->add_action( 'admin_notices', $this, 'upgrade_admin_notice' );

		register_activation_hook( WP_CHIMP_PLUGIN_FILE, [ $this, 'maybe_upgrade' ] ); // Create or Updatedatabase on activation_path.
	}

	/**
	 * Setup the database schema.
	 *
	 * @since 0.1.0
	 */
	protected function set_schema() {

		$this->schema = "
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			web_id varchar(20) NOT NULL DEFAULT '',
			list_id varchar(20) NOT NULL DEFAULT '',
			name varchar(200) NOT NULL DEFAULT '',
			stats LONGTEXT NOT NULL DEFAULT '',
			double_optin tinyint(1) NOT NULL DEFAULT '0',
			marketing_permissions tinyint(1) NOT NULL DEFAULT '0',
			merge_fields LONGTEXT NOT NULL DEFAULT '',
			interest_categories LONGTEXT NOT NULL DEFAULT '',
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_synced datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY list_id (list_id)
		";
	}

	/**
	 * Handle schema changes.
	 *
	 * @since 0.1.0
	 */
	protected function upgrade() {

		$upgraded = false;

		if ( version_compare( (int) $this->db_version, 201810100002, '<=' ) ) {

			$table_name = $this->table_name;
			$table_columns = $this->db->get_col( "DESC $table_name", 0 );

			/**
			 * ======================================================
			 * Upgraded database in version <= 0.4.0 to 0.5.0.
			 * ======================================================
			 */
			if ( in_array( 'id', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} MODIFY `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT;" );
			}

			if ( ! in_array( 'web_id', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} ADD `web_id` VARCHAR(20) NOT NULL DEFAULT '' AFTER `id`;" );
			}

			if ( ! in_array( 'merge_fields', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} ADD `merge_fields` LONGTEXT NOT NULL DEFAULT '' AFTER `double_optin`;" );
			}

			if ( ! in_array( 'interest_categories', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} ADD `interest_categories` LONGTEXT NOT NULL DEFAULT '' AFTER `merge_fields`;" );
			}

			/**
			 * ======================================================
			 * Upgraded database in version <= 0.5.0 to 0.6.0.
			 * ======================================================
			 */
			if ( in_array( 'subscribers', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$this->table_name} CHANGE `subscribers` `stats` LONGTEXT NOT NULL DEFAULT ''" );
			}

			if ( in_array( 'synced_at', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$this->table_name} CHANGE `synced_at` `date_synced` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
			}

			if ( ! in_array( 'marketing_permissions', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} ADD `marketing_permissions` tinyint(1) NOT NULL DEFAULT '0' AFTER `double_optin`;" );
			}

			if ( ! in_array( 'date_created', $table_columns, true ) ) {
				$this->db->query( "ALTER TABLE {$table_name} ADD `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `interest_categories`;" );
			}

			$upgraded = [
				'upgraded' => true,
				'version' => $this->version,
			];
		}

		if ( $upgraded ) {
			update_option( 'wp_chimp_lists_db_upgraded', $upgraded );
		}
	}

	/**
	 * Add an upgrade notice when the database has just been upgraded.
	 *
	 * @since 0.5.0
	 */
	public function upgrade_admin_notice() {

		if ( ! get_the_mailchimp_api_key_status() || is_lists_init() ) {
			return;
		}

		$db_upgraded = get_option( 'wp_chimp_lists_db_upgraded' );
		if ( ! isset( $db_upgraded['upgraded'] ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( true === (bool) $db_upgraded['upgraded'] && 'settings_page_wp-chimp' !== $screen->base ) :
			?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'The MailChimp List database has just been upgraded. Please go to the Settings page to re-synchronize the List', 'wp-chimp' ); ?></p>
			<p><a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=wp-chimp' ) ); ?>"><?php esc_html_e( 'Go to the Settings Page', 'wp-chimp' ); ?></a></p>
		</div>
			<?php
		endif;
	}
}
