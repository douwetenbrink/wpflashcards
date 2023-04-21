<?php
if (!defined('ABSPATH')) {

    exit;
}


/**
 * main class
 */
if (!class_exists('Wp_Flashcard_Settings')) {

    class Wp_Flashcard_Settings {

        protected static $sync_all_flashcards_integration_process;

        /**
         * construct
         */
        function __construct() {
            //create custom post type
            add_action('init', array($this, 'create_custom_post_types'));
            //add admin scripts
            add_action('admin_enqueue_scripts', array($this, 'flashcard_admin_scripts'));
            // manage columns in flashcard post type
            add_filter('manage_edit-flashcard_set_columns', array($this, 'list_flashcard_table'));
            add_action('manage_posts_custom_column', array($this, 'list_flashcard_table_values'), 10, 2);
            //save flashcard details 
            add_action("save_post", array($this, "save_flashcard_posts_details"));
            add_action('admin_menu', array($this, 'create_custom_menu_page'), 20);
            // add flashcard details
            add_action('admin_init', array($this, 'admin_init_fn_callback'));
            //remove post view action
            add_filter('post_row_actions', array($this, 'post_row_actions_callback'));
            // add custom meta on card category
            add_action('created_card_category', array($this, 'add_custom_term_meta'));
            add_action('edited_card_category', array($this, 'add_custom_term_meta'));
            //delete post
            add_action('deleted_post', array($this, 'deleted_post_callback'), 10, 2);
            // add admine notice for migration
            add_action('admin_notices', array($this, 'general_admin_notice'));
        }

        function deleted_post_callback($postid, $post) {
            if ($post->post_type == 'flashcard_set') {
                global $wpdb;
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                $wpdb->delete($table_flashcard_set_process, array('flashcard_id' => $postid), array('%d'));
            }
        }

        function general_admin_notice() {
            if (get_option('sync_cards_msg') == 'yes') {
                echo '<div class="notice notice-info is-dismissible">
             <p> <b>' . __('WP flashcard', 'wp-flashcard') . ': </b> ' . __('migration is finished', 'wp-flashcard') . '</p>
         </div>';
                delete_option('sync_cards_msg');
            } else if (get_option('sync_cards_start') == 'yes') {

                echo '<div class="notice notice-info is-dismissible">
             <p> <b>' . __('WP flashcard', 'wp-flashcard') . ': </b> ' . __('migration is processing ...', 'wp-flashcard') . '</p>
         </div>';
            }

            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON == 'true') {
                echo '<div class="notice notice-info"><p>' . sprintf(__('The %s constant is set to true. WP-Cron spawning is disabled.', 'wp-flashcard'), 'DISABLE_WP_CRON') . '</p></div>';
            }

            if (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON == 'true') {
                echo '<div class="notice notice-info"><p>' . sprintf(__('The %s constant is set to true.', 'wp-flashcard'), 'ALTERNATE_WP_CRON') . '</p></div>';
            }
            if (!get_transient('flashcard-cron-test-ok')) {
                global $wp_version;
                $sslverify = version_compare($wp_version, 4.0, '<');
                $doing_wp_cron = sprintf('%.22F', microtime(true));
                $cron_request = apply_filters('cron_request', array(
                    'url' => add_query_arg('doing_wp_cron', $doing_wp_cron, site_url('wp-cron.php')),
                    'key' => $doing_wp_cron,
                    'args' => array(
                        'timeout' => 3,
                        'blocking' => true,
                        'sslverify' => apply_filters('https_local_ssl_verify', $sslverify),
                    ),
                ));

                $cron_request['args']['blocking'] = true;

                $result = wp_remote_post($cron_request['url'], $cron_request['args']);
                if (is_wp_error($result)) {
                    echo '<div class="notice notice-info"><p>' . $result->get_error_message() . '</p></div>';
                } elseif (wp_remote_retrieve_response_code($result) >= 300) {
                    echo '<div class="notice notice-info"><p>' . sprintf(__('Unexpected HTTP response code: %s', 'wp-flashcard'), intval(wp_remote_retrieve_response_code($result))) . '</p></div>';
                } else {
                    set_transient('flashcard-cron-test-ok', 1, 3600);
                }
            }
        }

        /**
         * create custom post type
         */
        function create_custom_post_types() {
            //register new flashcard post type
            $flashcard_labels = array(
                'name' => _x('Flash Card Sets', 'Post type general name', 'wp-flashcard'),
                'singular_name' => _x('Flash Card Set', 'Post type singular name', 'wp-flashcard'),
                'menu_name' => _x('Flash Cards', 'Admin Menu text', 'wp-flashcard'),
                'name_admin_bar' => _x('Flash Cards', 'Add New on Toolbar', 'wp-flashcard'),
                'parent_item_colon' => __('Parent Flashcard Set', 'wp-flashcard'),
                //  'all_items' => __('All Flashcard Sets', 'wp-flashcard'),
                'view_item' => __('View Flashcard Set', 'wp-flashcard'),
                'add_new_item' => __('Add New Flashcard Set', 'wp-flashcard'),
                // 'add_new' => __('Add New Flashcard Set', 'wp-flashcard'),
                'edit_item' => __('Edit Flashcard Set', 'wp-flashcard'),
                'update_item' => __('Update Flashcard Set', 'wp-flashcard'),
                'search_items' => __('Search Flashcard Set', 'wp-flashcard'),
                'not_found' => __('Not Found', 'wp-flashcard'),
                'not_found_in_trash' => __('Not found in Trash', 'wp-flashcard'),
            );

            $flashcard_args = array(
                'labels' => $flashcard_labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'capability_type' => 'post',
                'has_archive' => true,
                'hierarchical' => false,
                'menu_position' => null,
                'menu_icon' => 'dashicons-admin-page',
                'supports' => array('title'),
            );

            if (get_option('edd_flashcard_license_status') != 'valid') {
                $flashcard_args['capabilities'] = array(
                    'create_posts' => false
                );
            }
            register_post_type('flashcard_set', $flashcard_args);

            //integrate old flashcard data
            if ((!defined('DISABLE_WP_CRON') || (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON != 'true')) && (!defined('ALTERNATE_WP_CRON') || (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON != 'true')) && get_transient('flashcard-cron-test-ok')) {
                global $wpdb;
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                if (get_option('edd_flashcard_license_status') == 'valid' && (empty(get_option('sync_cards_start')) || get_option('sync_cards_start') != 'yes') && (empty(get_option('sync_cards_end')) || get_option('sync_cards_end') != 'yes')) {
                    if ($wpdb->get_var("SHOW TABLES LIKE '$table_flashcard_set_process'") == $table_flashcard_set_process) {
                        $this->flashcard_plugin_integration();
                    }
                }
            }
        }

        function flashcard_plugin_integration() {
            $args = array(
                'post_type' => 'flashcard_set',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'falshcard_slides',
                        'compare' => 'IF EXIST',
                    )
                )
            );
            $card_posts = new WP_Query($args);
            if (!empty($card_posts->posts)) {
                $data = array();
                foreach ($card_posts->posts as $card_post) {
                    $data = array('set_id' => $card_post->ID);
                    self::$sync_all_flashcards_integration_process = new WP_Flashcard_Sync_Cards_Integration();
                    update_option('sync_cards_start', 'yes', 'no');
                    self::$sync_all_flashcards_integration_process->push_to_queue($data);
                    self::$sync_all_flashcards_integration_process->save()->dispatch();
                }
            }
        }

        /**
         * add flashcard Details
         */
        public function flashcard_details() {
            add_meta_box("falshcard-details", __('Flash Cards', 'wp-flashcard'), array($this, "flashcard_details_html"), "flashcard_set", "normal");
        }

        /**
         * add flashcard admin scripts
         * @global type $post_type
         * @param type $hook
         */
        public function flashcard_admin_scripts($hook) {
            global $post_type, $post;
            if (!empty($post_type) && $post_type == 'flashcard_set' || $hook == 'flashcard_set_page_wp-flashcard-setting-page') {
                wp_enqueue_media();
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_style('style-keyboard', WP_FLASHCARD_URL . 'assets/css/backend/keyboard.css');
                wp_enqueue_style('style-colorbox', WP_FLASHCARD_URL . 'assets/css/backend/colorbox.css');

                wp_enqueue_script('script-colorbox', WP_FLASHCARD_URL . 'assets/js/backend/jquery.colorbox-min.js');
                wp_enqueue_script('script-falshcard', WP_FLASHCARD_URL . 'assets/js/backend/flashcard-admin.js', array('wp-color-picker'));
                $set_id = 0;
                if (!empty($post->ID))
                    $set_id = $post->ID;

                $data = array(
                    'set_id' => $set_id,
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'upload_img' => WP_FLASHCARD_URL . 'assets/images/icons/01.png',
                    'save_img' => WP_FLASHCARD_URL . 'assets/images/icons/save.png',
                    'upload_title' => __('Upload Image, Sound or Video', 'wp-flashcard'),
                    'save_card_title' => __('Save', 'wp-flashcard'),
                    'add_card_title' => __('Add Card', 'wp-flashcard'),
                    'remove_card_title' => __('Remove Card', 'wp-flashcard'));
                wp_localize_script('script-falshcard', 'obj', $data);

                wp_enqueue_script('script-keyboard', WP_FLASHCARD_URL . 'assets/js/backend/keyboard.js');
            }

            wp_enqueue_style('style-importer', WP_FLASHCARD_URL . 'assets/css/backend/flashcard-importer.css');
        }

        /**
         * flashcard details html
         * @global type $post
         */
        public function flashcard_details_html() {
            global $post, $wpdb;
            ?>
            <div class="admin_flashcard_sets">        
                <?php
                $screen = get_current_screen();
                if (!empty($post->ID)) {
                    $set_id = $post->ID;
                    $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                    $total_sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id";
                    $sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id Limit 0,50";
                    $total_results = $wpdb->get_results($total_sql);
                    $results = $wpdb->get_results($sql);
                    include_once WP_FLASHCARD_PATH . 'includes/admin/views/load-more-cards.php';
                }
                ?>
            </div>
            <?php
            echo '<input type="hidden" name="noncename" value="' . wp_create_nonce(__FILE__) . '" />';
        }

        /**
         * manage columns in flashcard post type
         * @param array $column
         * @return type
         */
        public function list_flashcard_table($column) {
            $column['flashcard_short_code'] = __('ShortCode', 'wp-flashcard');
            return $column;
        }

        /**
         * manage columns in flashcard post type
         * @param type $column_name
         * @param type $post_id
         */
        public function list_flashcard_table_values($column_name, $post_id) {
            switch ($column_name) {
                case 'flashcard_short_code' :
                    echo!empty(get_post_meta($post_id, 'flashcard_short_code', true)) ? get_post_meta($post_id, 'flashcard_short_code', true) : '-';
                    break;
                default:
                    break;
            }
        }

        /**
         * save flashcard details
         * @global type $post
         * @param type $post_id
         */
        public function save_flashcard_posts_details($post_id) {
            global $post;

            if (!empty($post->post_type) && $post->post_type == "flashcard_set") {
                update_post_meta($post->ID, 'flashcard_short_code', "[flashcard_set id='" . $post->ID . "']");
            }
        }

        public function create_custom_menu_page() {
            if (get_option('edd_flashcard_license_status') == 'valid') {
                add_submenu_page('edit.php?post_type=flashcard_set', __('Flash Card Settings', 'wp-flashcard'), __('Flash Card Settings', 'wp-flashcard'), 'manage_options', 'wp-flashcard-setting-page', array($this, 'wp_flashcard_setting_page_callback'));
                add_submenu_page('edit.php?post_type=flashcard_set', __('Flash Card Import', 'wp-flashcard'), __('Flash Card Import', 'wp-flashcard'), 'manage_options', 'wp-flashcard-import-setting-page', array('Wp_Flashcard_Import_Settings', 'wp_flashcard_import_setting_page_callback'));
            } else {
                remove_submenu_page('edit.php?post_type=flashcard_set', 'post-new.php?post_type=flashcard_set');
            }
            add_submenu_page('edit.php?post_type=flashcard_set', __('Flash Card License', 'wp-flashcard'), __('Flash Card License', 'wp-flashcard'), 'manage_options', EDD_FLASHCARD_LICENSE_PAGE, array($this, 'edd_flashcard_license_page'), 5);
        }

        function edd_flashcard_license_page() {
            $license = get_option('edd_flashcard_license_key');
            $status = get_option('edd_flashcard_license_status');
            ?>
            <div class="wrap">
                <h2><?php esc_html_e('WP Flashcard License', 'wp-flashcard'); ?></h2>
                <form method="post" action="options.php">

                    <?php settings_fields('edd_flashcard_license'); ?>

                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('License Key', 'wp-flashcard'); ?>
                                </th>
                                <td>
                                    <input id="edd_flashcard_license_key" name="edd_flashcard_license_key" type="text" class="regular-text" value="<?php esc_attr_e($license); ?>" />
                                    <?php if ($status !== false && $status == 'valid') { ?>
                                        <label class="description" for="edd_flashcard_license_key" style="color:green;" ><?php esc_html_e('active', 'wp-flashcard'); ?></label>
                                    <?php }
                                    ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" valign="top">
                                    <?php esc_html_e('Activate License', 'wp-flashcard'); ?>
                                </th>
                                <td>
                                    <?php if ($status !== false && $status == 'valid') { ?>
                                        <?php wp_nonce_field('edd_flashcard_nonce', 'edd_flashcard_nonce'); ?>
                                        <input type="submit" class="button-secondary" name="edd_flashcard_license_deactivate" value="<?php esc_html_e('Deactivate License', 'wp-flashcard'); ?>"/>
                                        <?php
                                    } else {
                                        wp_nonce_field('edd_flashcard_nonce', 'edd_flashcard_nonce');
                                        ?>
                                        <input type="submit" class="button-secondary" name="edd_flashcard_license_activate" value="<?php esc_html_e('Activate License', 'wp-flashcard'); ?>"/>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <?php
            }

            public function admin_init_fn_callback() {
                add_meta_box("falshcard-details", __('Flash Cards', 'wp-flashcard'), array($this, "flashcard_details_html"), "flashcard_set", "normal");
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_max_card_number');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_width_setting');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_height_setting');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_font_size');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_text_color');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_card_background_color');
                register_setting('wp-flashcard-general-settings', 'wp_flashcard_card_hide_loader');


                // creates our settings in the options table
                register_setting('edd_flashcard_license', 'edd_flashcard_license_key'); //, array($this, 'edd_sanitize_license')
                if (get_option('edd_flashcard_license_status') != 'valid') {
                    global $submenu;
                    unset($submenu['edit.php?post_type=flashcard_set'][5]);
                }
                $this->edd_flashcard_activate_license();
                $this->edd_flashcard_deactivate_license();
            }

            function edd_sanitize_license($new) {
                $old = get_option('edd_flashcard_license_key');
                if ($old && $old != $new) {
                    delete_option('edd_flashcard_license_status'); // new license has been entered, so must reactivate
                }
                return $new;
            }

            function edd_flashcard_activate_license() {
                // listen for our activate button to be clicked
                if (!empty($_POST['edd_flashcard_license_activate'])) {
                    update_option('edd_flashcard_license_key', sanitize_text_field($_POST['edd_flashcard_license_key']));
                    // run a quick security check
                    if (!check_admin_referer('edd_flashcard_nonce', 'edd_flashcard_nonce')) {
                        return; // get out if we didn't click the Activate button
                    }
                    // retrieve the license from the database
                    $license = trim(get_option('edd_flashcard_license_key'));

                    // data to send in our API request
                    $api_params = array(
                        'edd_action' => 'activate_license',
                        'license' => $license,
                        'item_name' => urlencode(EDD_FLASHCARD_ITEM_NAME), // the name of our product in EDD
                        'url' => home_url()
                    );

                    // Call the custom API.
                    $response = wp_remote_post(EDD_FLASHCARD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

                    // make sure the response came back okay
                    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                        if (is_wp_error($response)) {
                            $message = $response->get_error_message();
                        } else {
                            $message = __('An error occurred, please try again.', 'wp-flashcard');
                        }
                    } else {

                        $license_data = json_decode(wp_remote_retrieve_body($response));

                        if (false === $license_data->success) {

                            switch ($license_data->error) {

                                case 'expired' :

                                    $message = sprintf(
                                            __('Your license key expired on %s.', 'wp-flashcard'), date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                                    );
                                    break;

                                case 'disabled' :
                                case 'revoked' :

                                    $message = __('Your license key has been disabled.', 'wp-flashcard');
                                    break;

                                case 'missing' :

                                    $message = __('Invalid license.', 'wp-flashcard');
                                    break;

                                case 'invalid' :
                                case 'site_inactive' :

                                    $message = __('Your license is not active for this URL.', 'wp-flashcard');
                                    break;

                                case 'item_name_mismatch' :

                                    $message = sprintf(__('This appears to be an invalid license key for %s.', 'wp-flashcard'), EDD_FLASHCARD_ITEM_NAME);
                                    break;

                                case 'no_activations_left':

                                    $message = __('Your license key has reached its activation limit.', 'wp-flashcard');
                                    break;

                                default :

                                    $message = __('An error occurred, please try again.', 'wp-flashcard');
                                    break;
                            }
                        }
                    }
                    // Check if anything passed on a message constituting a failure
                    if (!empty($message)) {
                        $base_url = admin_url('admin.php?page=' . EDD_FLASHCARD_LICENSE_PAGE);
                        $redirect = add_query_arg(array('sl_activation' => 'false', 'message' => urlencode($message)), $base_url);

                        wp_redirect($redirect);
                        exit();
                    }

                    // $license_data->license will be either "valid" or "invalid"

                    update_option('edd_flashcard_license_status', $license_data->license);

                    wp_redirect(admin_url('admin.php?page=' . EDD_FLASHCARD_LICENSE_PAGE));
                    exit();
                }
            }

            function edd_flashcard_deactivate_license() {

                // listen for our activate button to be clicked
                if (!empty($_POST['edd_flashcard_license_deactivate'])) {
                    update_option('edd_flashcard_license_key', sanitize_text_field($_POST['edd_flashcard_license_key']));
                    // run a quick security check
                    if (!check_admin_referer('edd_flashcard_nonce', 'edd_flashcard_nonce')) {
                        return; // get out if we didn't click the Activate button 
                    }
                    // retrieve the license from the database
                    $license = trim(get_option('edd_flashcard_license_key'));

                    // data to send in our API request
                    $api_params = array(
                        'edd_action' => 'deactivate_license',
                        'license' => $license,
                        'item_name' => urlencode(EDD_FLASHCARD_ITEM_NAME), // the name of our product in EDD
                        'url' => home_url()
                    );

                    // Call the custom API.
                    $response = wp_remote_post(EDD_FLASHCARD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
                    // make sure the response came back okay
                    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

                        if (is_wp_error($response)) {
                            $message = $response->get_error_message();
                        } else {
                            $message = __('An error occurred, please try again.', 'wp-flashcard');
                        }

                        $base_url = admin_url('admin.php?page=' . EDD_FLASHCARD_LICENSE_PAGE);
                        $redirect = add_query_arg(array('sl_activation' => 'false', 'message' => urlencode($message)), $base_url);

                        wp_redirect($redirect);
                        exit();
                    }

                    // decode the license data
                    $license_data = json_decode(wp_remote_retrieve_body($response));

                    // $license_data->license will be either "deactivated" or "failed"
                    if ($license_data->license == 'deactivated') {
                        delete_option('edd_flashcard_license_status');
                    }

                    wp_redirect(admin_url('admin.php?page=' . EDD_FLASHCARD_LICENSE_PAGE));
                    exit();
                }
            }

            public function wp_flashcard_setting_page_callback() {
                ?>
                <div class="wrap">
                    <div id="icon-themes" class="icon32"></div>  
                    <h2><?php echo esc_html__('WP Flashcard Settings', 'wp-flashcard'); ?></h2>  
                    <?php settings_errors(); ?>  
                    <form method="POST" action="options.php">  
                        <?php
                        settings_fields('wp-flashcard-general-settings');
                        do_settings_sections('wp-flashcard-general-settings');
                        ?>       
                        <table class="widefat" style="margin-top: 20px;">
                            <tr>
                            <h2><?php echo esc_html__('Card Settings', 'wp-flashcard'); ?></h2>  
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Cards Max Number', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="number" id="wp_flashcard_max_card_number" name="wp_flashcard_max_card_number" value="<?php echo esc_attr(get_option('wp_flashcard_max_card_number')); ?>"></td>
                            </tr>

                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Card Width', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="number" id="wp_flashcard_width_setting" name="wp_flashcard_width_setting" value="<?php echo esc_attr(get_option('wp_flashcard_width_setting')); ?>"> px</td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Card Height', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="number" id="wp_flashcard_height_setting" name="wp_flashcard_height_setting" value="<?php echo esc_attr(get_option('wp_flashcard_height_setting')); ?>"> px</td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Font Size', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="number" id="wp_flashcard_font_size" name="wp_flashcard_font_size" value="<?php echo esc_attr(get_option('wp_flashcard_font_size')); ?>"> px</td>
                            </tr>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e('Text Color', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="text" id="wp_flashcard_text_color" name="wp_flashcard_text_color" value="<?php echo esc_attr(get_option('wp_flashcard_text_color')); ?>"></td>
                            </tr>
                            <tr> 
                                <th style="width: 30%;"><?php esc_html_e('Card Background Color', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="text" id="wp_flashcard_card_background_color" name="wp_flashcard_card_background_color" value="<?php echo esc_attr(get_option('wp_flashcard_card_background_color')); ?>"></td>
                            </tr>
                            <tr> 
                                <th style="width: 30%;"><?php esc_html_e('Loader Icon', 'wp-flashcard'); ?></th>
                                <td style="width: 70%"><input type="checkbox" id="wp_flashcard_card_hide_loader" name="wp_flashcard_card_hide_loader" value="yes" <?php echo checked(esc_attr(get_option('wp_flashcard_card_hide_loader')), 'yes'); ?> ><?php esc_html_e('Hide', 'wp-flashcard'); ?></td>
                            </tr>

                        </table>
                        <?php submit_button(); ?>  
                    </form> 
                </div>
                <?php
            }

            public static function edd_flashcard_is_valid() {
                $license = trim(get_option('edd_flashcard_license_key'));

                $api_params = array(
                    'edd_action' => 'check_license',
                    'license' => $license,
                    'item_name' => urlencode(EDD_FLASHCARD_ITEM_NAME),
                    'url' => home_url()
                );

                // Call the custom API.
                $response = wp_remote_post(EDD_FLASHCARD_SL_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

                if (is_wp_error($response))
                    return false;

                $license_data = json_decode(wp_remote_retrieve_body($response));

                if ($license_data->license == 'valid') {
                    return true;
                }
                return false;
            }

            /**
             * 
             * @param type $action
             * @return type
             */
            function post_row_actions_callback($action) {
                if ('flashcard_set' == get_post_type()) {
                    unset($action['view']);
                }
                return $action;
            }

            function add_custom_term_meta($term_id) {
                update_term_meta($term_id, 'flashcard_short_code', "[flashcard_set id='" . $term_id . "']");
            }

        }

        $instance = new Wp_Flashcard_Settings();
    }
    
