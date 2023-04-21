<?php
if (!defined('ABSPATH')) {

    exit;
}


/**
 * main class
 */
if (!class_exists('Wp_Flashcard_Import_Settings')) {

    class Wp_Flashcard_Import_Settings {

        protected static $sync_all_flashcards_process;

        /**
         * construct
         */
        function __construct() {
            //configure settings page
            add_action('admin_init', array($this, 'admin_init_settings_fn_callback'));
        }

        /**
         * configure settings page
         */
        public function admin_init_settings_fn_callback() {
            register_setting("wp-flashcard-import-settings", __('WP Flashcard Import Settings', 'wp-flashcard'));
            if ((!defined('DISABLE_WP_CRON') || (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON !='true')) && (!defined('ALTERNATE_WP_CRON')|| (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON !='true')) && get_transient('flashcard-cron-test-ok')) {
                if (!empty($_FILES['wp_flashcard_import_file']) && !empty($_POST['wp_flashcard_set_name'])) {
                    if (empty(get_option('sync_start')) || get_option('sync_start') != 'yes') {
                        $this->handle_sync_all();
                    }
                }
            }
        }

        /**
         * manage background process
         */
        public function handle_sync_all() {
            $data = array();
            $csv_file = $_FILES['wp_flashcard_import_file'];
            $post_title = $_POST['wp_flashcard_set_name'];
            $type_data = explode('.', $csv_file['name']);
            if (!empty($type_data[1]) && $type_data[1] == 'csv') {
                $csv_to_array = array_map('str_getcsv', file($csv_file['tmp_name']));
                if (!empty($csv_to_array)) {
                    self::$sync_all_flashcards_process = new WP_Flashcard_Sync_Cards_Process();
                    $post_id = wp_insert_post(array(
                        'post_title' => $post_title,
                        'post_status' => 'publish',
                        'post_type' => 'flashcard_set'
                    ));
                    if (!empty($post_id)) {
                        $data['set_id'] = $post_id;
                        //add flashcard short code
                        update_post_meta($post_id, 'flashcard_short_code', "[flashcard_set id='" . $post_id . "']");

                        update_option('sync_start', 'yes', 'no');
                        $data['csv_data'] = $csv_to_array;
                        self::$sync_all_flashcards_process->push_to_queue($data);
                        self::$sync_all_flashcards_process->save()->dispatch();
                    }

                    wp_redirect(admin_url('edit.php?post_type=flashcard_set&page=wp-flashcard-import-setting-page'));
                    exit;
                } else {
                    delete_option('sync_start');
                    delete_option('sync_end');
                }
            } else {
                delete_option('sync_start');
                delete_option('sync_end');
                add_settings_error('import_type_setting_error', 'import_type_setting_error_data', __('Only .CSV file type allowed for import.', 'wp-flashcard'), 'error');
            }
        }

        /**
         * import setting page html
         */
        public static function wp_flashcard_import_setting_page_callback() {
            ?>
            <div class="wrap">
                <div id="icon-themes" class="icon32"></div>  
                <h2><?php echo esc_html__('WP Flashcard Import Settings', 'wp-flashcard'); ?></h2>  
                <?php
                settings_errors('import_type_setting_error');
                settings_errors('import_size_setting_error');
                ?>
                <form method="POST" action="options.php" enctype="multipart/form-data">  
                    <?php
                    settings_fields('wp-flashcard-import-settings');
                    do_settings_sections('wp-flashcard-import-settings');
                    $terms = get_terms(array('taxonomy' => 'card_category'));
                    ?>
                    <table class="widefat flashcard-import-container" style="margin-top: 20px;">
                        <tr>
                        <h2><?php echo esc_html__('Import Flashcard CSV File', 'wp-flashcard'); ?></h2>  
                        </tr>
                        <?php if (empty(get_option('sync_end')) && empty(get_option('sync_start'))) { ?>
                            <tr>
                                <th style="width: 30%;"><?php _e('Flashcard/Set name', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input required type="text" id="wp_flashcard_set_name" name="wp_flashcard_set_name" value=""> </td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Import File', 'wp-flashcard'); ?></th>
                                <td style="width: 70%">
                                    <input required type="file" id="wp_flashcard_import_file" name="wp_flashcard_import_file" accept=".csv" value="">
                                    <p><?php esc_html_e('Upload .CSV file format only', 'wp-flashcard'); ?></p>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php
                        if (!empty(get_option('sync_start'))) {
                            ?>
                            <tr class="importing-container">
                                <td style="width: 70%">
                                    <p class="status-message" style="color: #008ec2;"> <img src="<?php echo WP_FLASHCARD_URL . 'assets/images/preloader.gif'; ?>" class="tag-loader"/> <?php esc_html_e('Importing not finished yet', 'wp-flashcard'); ?>.....<?php esc_html_e('to check status please refresh page', 'wp-flashcard'); ?></p>
                                </td>
                            </tr>

                            <?php
                        } elseif (!empty(get_option('sync_end'))) {
                            delete_option('sync_end');
                            ?>
                            <tr class="importing-container">
                                <td style="width: 70%" colspan="2">
                                    <p class="end-message"><?php esc_html_e('All flashcards are completely imported', 'wp-flashcard'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php _e('Flashcard/Set name', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input required type="text" id="wp_flashcard_set_name" name="wp_flashcard_set_name" value=""> </td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Import File', 'wp-flashcard'); ?></th>
                                <td style="width: 70%">
                                    <input required type="file" id="wp_flashcard_import_file" name="wp_flashcard_import_file" accept=".csv" value=""> 
                                    <p><?php esc_html_e('Upload .CSV file format only', 'wp-flashcard'); ?></p>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </table>
                    <?php
                    if (empty(get_option('sync_end')) && empty(get_option('sync_start'))) {
                        submit_button(esc_html__('Import Flashcards', 'wp-flashcard'));
                    }
                    ?>  
                </form> 
            </div>
            <?php
        }

    }

    $instance = new Wp_Flashcard_Import_Settings();
}