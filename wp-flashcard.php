<?php
/**
 * Plugin Name: WP Flashcard
 * Description: A WordPress Flashcard Plugin
 * Version: 2.9.5
 * Author: WP Flashcard
 * Author URI: https://wpflashcard.com/
 * Text Domain: wp-flashcard
 */
if (!defined('ABSPATH')) {

    exit;
}
define('WP_FLASHCARD_URL', plugin_dir_url(__FILE__));
define('WP_FLASHCARD_PATH', plugin_dir_path(__FILE__));
define('WP_FLASHCARD_FILE', __FILE__);
define('WP_FLASHCARD_VERSION', '2.9.5');

define('EDD_FLASHCARD_SL_STORE_URL', 'https://wpflashcard.com/');
define('EDD_FLASHCARD_SL_ITEM_ID', 644);
define('EDD_FLASHCARD_LICENSE_PAGE', 'wp-flashcard-license');
define('EDD_FLASHCARD_ITEM_NAME', 'WP Flashcard - Basic 1 Site License');


if (!class_exists('EDD_SL_Plugin_Updater')) {
    // load our custom updater.
    include WP_FLASHCARD_PATH . 'includes/EDD_SL_Plugin_Updater.php';
}
/**
 * main class
 */
if (!class_exists('Wp_Flashcard')) {

    class Wp_Flashcard {

        /**
         * construct
         */
        function __construct() {
            add_action('admin_init', array($this, 'edd_sl_flashcard_plugin_updater'), 0);
            add_action('admin_notices', array($this, 'edd_flashcard_admin_notices'));
            $this->includes();
            //create tables
            register_activation_hook(WP_FLASHCARD_FILE, array($this, 'flashcard_plugin_activate'));
            register_deactivation_hook(WP_FLASHCARD_FILE, array($this, 'flashcard_plugin_deactivate'));
        }

        /**
         * includes
         */
        public function includes() {

            include_once WP_FLASHCARD_PATH . 'includes/admin/wp-flashcard-settings.php';

            include_once WP_FLASHCARD_PATH . 'includes/admin/wp-flashcard-import-settings.php';

            include_once WP_FLASHCARD_PATH . 'includes/admin/wp-flashcard-sync-cards-process.php';

            include_once WP_FLASHCARD_PATH . 'includes/admin/wp-flashcard-sync-cards-integration.php';

            include_once WP_FLASHCARD_PATH . 'includes/front/wp-flashcard-process.php';

            include_once WP_FLASHCARD_PATH . 'includes/shortcodes/flashcard-shortcode.php';
        }

        function flashcard_plugin_deactivate() {
            delete_option('sync_start');
            delete_option('sync_end');
            delete_option('sync_cards_start');
            delete_option('sync_cards_end');
            delete_option('sync_cards_msg');
            delete_option('edd_flashcard_license_key');
            delete_option('edd_flashcard_license_status');
            delete_transient('flashcard-cron-test-ok');
        }

        function edd_sl_flashcard_plugin_updater() {

            // retrieve our license key from the DB
            $license_key = trim(get_option('edd_flashcard_license_key'));

            // setup the updater
            $edd_updater = new EDD_SL_Plugin_Updater(EDD_FLASHCARD_SL_STORE_URL, __FILE__, array(
                'version' => WP_FLASHCARD_VERSION, // current version number
                'license' => $license_key, // license key (used get_option above to retrieve from DB)
                'item_id' => EDD_FLASHCARD_SL_ITEM_ID, // ID of the product
                'author' => 'WP Flashcard', // author of this plugin
                'beta' => false,
                    )
            );
            //create data
            $this->flashcard_plugin_activate();
        }

        function edd_flashcard_admin_notices() {
            if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {

                switch ($_GET['sl_activation']) {

                    case 'false':
                        $message = urldecode(sanitize_text_field($_GET['message']));
                        ?>
                        <div class="error">
                            <p><?php echo $message; ?></p>
                        </div>
                        <?php
                        break;

                    case 'true':
                    default:
                        // Developers can put a custom success message here for when activation is successful if they way.
                        break;
                }
            }
        }

        function flashcard_plugin_activate() {
            global $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $charset_collate = $wpdb->get_charset_collate();

            //image actions table
            $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_flashcard_set_process'") != $table_flashcard_set_process) {
                $sql = "CREATE TABLE " . $table_flashcard_set_process . " (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `flashcard_id` bigint(20) NOT NULL,
                        `foreground_card` longtext NOT NULL, 
                        `background_card` longtext NOT NULL,                                               
                        `created_at` datetime NOT NULL, 
			PRIMARY KEY (`id`)
		) $charset_collate;";
                dbDelta($sql);
            }
        }

    }

    $instance = new Wp_Flashcard();
}
