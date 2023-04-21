<?php

if (!defined('ABSPATH')) {

    exit;
}


/**
 * main class
 */
if (!class_exists('Wp_Flashcard_Process')) {

    class Wp_Flashcard_Process {

        /**
         * construct
         */
        function __construct() {
            //enqueue scripts
            add_action('wp', array($this, 'init_scripts_callback'));
            // add favoutite to cookie
            add_action('wp_ajax_add_flashcard_to_favourite', array($this, 'add_flashcard_to_favourite'));
            add_action('wp_ajax_nopriv_add_flashcard_to_favourite', array($this, 'add_flashcard_to_favourite'));
            //remove favourite from cookie
            add_action('wp_ajax_remove_flashcard_from_favourite', array($this, 'remove_flashcard_from_favourite'));
            add_action('wp_ajax_nopriv_remove_flashcard_from_favourite', array($this, 'remove_flashcard_from_favourite'));
            //get starred terms
            add_action('wp_ajax_get_starred_terms', array($this, 'get_starred_terms'));
            add_action('wp_ajax_nopriv_get_starred_terms', array($this, 'get_starred_terms'));
            //get all terms
            add_action('wp_ajax_get_all_terms', array($this, 'get_all_terms'));
            add_action('wp_ajax_nopriv_get_all_terms', array($this, 'get_all_terms'));
            //enable shuffle card
            add_action('wp_ajax_enable_shuffle_cards', array($this, 'enable_shuffle_cards'));
            add_action('wp_ajax_nopriv_enable_shuffle_cards', array($this, 'enable_shuffle_cards'));
            //disable shuffle card
            add_action('wp_ajax_disabled_shuffle_cards', array($this, 'disabled_shuffle_cards'));
            add_action('wp_ajax_nopriv_disabled_shuffle_cards', array($this, 'disabled_shuffle_cards'));
            //enable switch card 
            add_action('wp_ajax_enable_switch_cards', array($this, 'enable_switch_cards'));
            add_action('wp_ajax_nopriv_enable_switch_cards', array($this, 'enable_switch_cards'));
            //disable switch card 
            add_action('wp_ajax_disabled_switch_cards', array($this, 'disabled_switch_cards'));
            add_action('wp_ajax_nopriv_disabled_switch_cards', array($this, 'disabled_switch_cards'));
            //get card post
            add_action('wp_ajax_get_post_card', array($this, 'get_post_card'));
            add_action('wp_ajax_nopriv_get_post_card', array($this, 'get_post_card'));
            // get all post cards
            add_action('wp_ajax_get_all_post_cards', array($this, 'get_all_post_cards'));
            add_action('wp_ajax_nopriv_get_all_post_cards', array($this, 'get_all_post_cards'));
            //update and insert card item
            add_action('wp_ajax_update_card_set', array($this, 'update_card_set'));
            add_action('wp_ajax_nopriv_update_card_set', array($this, 'update_card_set'));
            //load more cards
            add_action('wp_ajax_load_more_cards', array($this, 'load_more_cards'));
            add_action('wp_ajax_nopriv_load_more_cards', array($this, 'load_more_cards'));
            // remove card data
            add_action('wp_ajax_remove_card_data', array($this, 'remove_card_data'));
            add_action('wp_ajax_nopriv_remove_card_data', array($this, 'remove_card_data'));
        }

        /**
         * enqueue scripts on wp action
         */
        function init_scripts_callback() {
            global $post;

            if ((!empty($post->post_type) && $post->post_type == 'mpcs-course')) {
                //fix memberpress course issue
                add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts_memberpress_callback'), 999);
            } else {
                // normal enqueue scripts
                add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts_callback'));
            }
        }

        /**
         * enqueue scripts for memberpress course
         */
        function wp_enqueue_scripts_memberpress_callback() {
            wp_enqueue_style('owl-carousel-style', WP_FLASHCARD_URL . 'assets/css/owl.carousel.min.css');
            wp_enqueue_style('owl-carousel-theme-style', WP_FLASHCARD_URL . 'assets/css/owl.theme.default.min.css');
            wp_enqueue_style('animate-style', WP_FLASHCARD_URL . 'assets/css/animate.min.css');
            wp_enqueue_style('scrollbar-style', WP_FLASHCARD_URL . 'assets/css/jquery.scrollbar.css');
            wp_enqueue_style('flashcard-style', WP_FLASHCARD_URL . 'assets/css/flashcard.css');

            wp_enqueue_script('flip-script', WP_FLASHCARD_URL . 'assets/js/jquery.flip.min.js', array('jquery'), '');
            wp_enqueue_script('owl-carousel-script', WP_FLASHCARD_URL . 'assets/js/owl.carousel.min.js', array('jquery'), '');
            wp_enqueue_script('scrollbar-script', WP_FLASHCARD_URL . 'assets/js/jquery.scrollbar.min.js', array('jquery'), '');
            wp_enqueue_script('flashcard-script', WP_FLASHCARD_URL . 'assets/js/flashcard.js', array('jquery'), '');
            wp_localize_script('flashcard-script', 'obj', array('ajax_url' => admin_url('admin-ajax.php'), 'hide_loader' => get_option('wp_flashcard_card_hide_loader')));
        }

        /**
         * enqueue scripts
         */
        function wp_enqueue_scripts_callback() {

            wp_register_style('owl-carousel-style', WP_FLASHCARD_URL . 'assets/css/owl.carousel.min.css');
            wp_register_style('owl-carousel-theme-style', WP_FLASHCARD_URL . 'assets/css/owl.theme.default.min.css');
            wp_register_style('animate-style', WP_FLASHCARD_URL . 'assets/css/animate.min.css');
            wp_register_style('scrollbar-style', WP_FLASHCARD_URL . 'assets/css/jquery.scrollbar.css');
            wp_register_style('flashcard-style', WP_FLASHCARD_URL . 'assets/css/flashcard.css');

            wp_register_script('flip-script', WP_FLASHCARD_URL . 'assets/js/jquery.flip.min.js', array('jquery'), '', true);
            wp_register_script('owl-carousel-script', WP_FLASHCARD_URL . 'assets/js/owl.carousel.min.js', array('jquery'), '', true);
            wp_register_script('scrollbar-script', WP_FLASHCARD_URL . 'assets/js/jquery.scrollbar.min.js', array('jquery'), '', true);
            wp_register_script('flashcard-script', WP_FLASHCARD_URL . 'assets/js/flashcard.js', array('jquery'), '', true);
            wp_localize_script('flashcard-script', 'obj', array('ajax_url' => admin_url('admin-ajax.php'), 'hide_loader' => get_option('wp_flashcard_card_hide_loader')));

        }

        /**
         * add favourite to cookie
         */
        function add_flashcard_to_favourite() {
            $return['text'] = 'fail';
            $return['starred_data'] = 0;
            if (!empty($_POST['card_id']) && !empty($_POST['set_id'])) {
                $card_id = sanitize_text_field($_POST['card_id']);
                $set_id = sanitize_text_field($_POST['set_id']);
                $expire = strtotime('+2 years');

                $card_data = array();
                //get old cookie data
                if (!empty($_COOKIE['flashcard_' . $set_id]) && $_COOKIE['flashcard_' . $set_id] !== 'null') {
                    $card_data = json_decode(stripslashes($_COOKIE['flashcard_' . $set_id]), true);
                }
                //add new favourite card data
                $card_data[] = $card_id;
                //   array_push($card_data, $card_id);
                $cookie_name = 'flashcard_' . $set_id;

                if (!empty(json_encode($card_data)) && json_encode($card_data) !== 'null') {
                    setcookie($cookie_name, json_encode($card_data), $expire, '/');
                    $return['text'] = 'success';
                }
                // get starred term text                
                $return['starred_data'] = count($card_data);
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * remove favourite from cookie
         */
        function remove_flashcard_from_favourite() {
            $return['text'] = __('No Results Found', 'wp-flashcard');
            $return['redirect_to_all'] = 0;
            $return['starred_data'] = 0;
            if (!empty($_POST['card_id']) && !empty($_POST['set_id'])) {
                $card_id = sanitize_text_field($_POST['card_id']);
                $set_id = sanitize_text_field($_POST['set_id']);
                $expire = strtotime('+2 years');

                $card_data = array();
                //get old cookie data
                if (!empty($_COOKIE['flashcard_' . $set_id]) && $_COOKIE['flashcard_' . $set_id] !== 'null') {
                    $card_data = json_decode(stripslashes($_COOKIE['flashcard_' . $set_id]), true);
                }
                $new_card_data = $card_data;
                foreach ($new_card_data as $key => $value) {
                    if ($card_id == $value) {
                        unset($card_data[$key]);
                        setcookie('flashcard_' . $set_id, json_encode($card_data), $expire, '/');
                    }
                }

                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = array();
                    if (!empty($card_data)) {
                        $data = $card_data;
                    }
                    $all_data = $this->get_flashcard_terms($set_id);
                    if (empty($data) || count($data) == 0) {
                        $data = $all_data;
                        $return['redirect_to_all'] = 1;
                        $starred = 2;
                        $obj = $this;
                    } else {
                        $starred = 1;
                    }

                    $all_data_count = count($all_data);
                    $starred_term_count = 0;

                    //randomize array
                    $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);

                    $shuffle_class = 'shuffle-not-active';
                    if ($flashcard_shuffle == 'yes') {
                        shuffle($data);
                        $shuffle_class = 'shuffle-active';
                    }
                    //get old cookie data to switch
                    $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                    $switch_class = 'switch-not-active';
                    $switched = 0;
                    if ($flashcard_switch == 'yes') {
                        $switch_class = 'switch-active';
                        $switched = 1;
                    }

                    // get starred term text
                    $starred_term_text = $this->get_starred_term_text($starred_term_count);
                    // get all term text
                    $all_data_text = $this->get_all_term_text($all_data_count);
                    $current_post_id = reset($data);
                    ob_start();
                    include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                    $return['text'] = ob_get_clean();
                } else {
                    $return['text'] = 'success';
                    $return['starred_data'] = count($card_data);
                }
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * get flashcard terms from repeater by card id
         * @param type $set_id
         * @return type
         */
        function get_flashcard_terms($set_id) {
            global $wpdb;
            $post_per_page = get_option('wp_flashcard_max_card_number') ? get_option('wp_flashcard_max_card_number') : -1;
            $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';

            $sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id";
            if (!empty(get_option('wp_flashcard_max_card_number'))) {
                $post_per_page = get_option('wp_flashcard_max_card_number');
                $sql .= " Limit 0,$post_per_page";
            }

            $results = $wpdb->get_results($sql);

            if (!empty($results)) {
                $data = array();
                foreach ($results as $result) {
                    $data[] = $result->id;
                }
            }
            return $data;
        }

        /**
         * get flashcard terms from cookie by card id
         * @param type $set_id
         * @return type
         */
        function get_flashcard_terms_from_cookie($set_id) {
            $data = array();
            if (!empty($_COOKIE['flashcard_' . $set_id]) && $_COOKIE['flashcard_' . $set_id] !== 'null') {
                $data = json_decode(stripslashes($_COOKIE['flashcard_' . $set_id]), true);
            }
            return $data;
        }

        /**
         * get switch data from cookie
         * @param type $set_id
         * @return type
         */
        function get_switch_data_from_cookie($set_id) {
            $flashcard_switch = '';
            if (!empty($_COOKIE['flashcard_switch_' . $set_id]) && $_COOKIE['flashcard_switch_' . $set_id] !== 'null') {
                $flashcard_switch = $_COOKIE['flashcard_switch_' . $set_id];
            }
            return $flashcard_switch;
        }

        /**
         * get shuffle data from cookie
         * @param type $set_id
         * @return type
         */
        function get_shuffle_data_from_cookie($set_id) {
            $flashcard_shuffle = '';
            if (!empty($_COOKIE['flashcard_shuffle_' . $set_id]) && $_COOKIE['flashcard_shuffle_' . $set_id] !== 'null') {
                $flashcard_shuffle = $_COOKIE['flashcard_shuffle_' . $set_id];
            }
            return $flashcard_shuffle;
        }

        /**
         * get starred term text
         * @param type $starred_term_count
         * @return type
         */
        function get_starred_term_text($starred_term_count) {
            if ($starred_term_count > 1) {
                $starred_term_text = __('starred terms ', 'wp-flashcard');
            } else {
                $starred_term_text = __('starred term ', 'wp-flashcard');
            }
            return $starred_term_text;
        }

        /**
         * get all term text
         * @param type $all_data_count
         * @return type
         */
        function get_all_term_text($all_data_count) {
            if ($all_data_count > 1) {
                $all_data_text = __('terms ', 'wp-flashcard');
            } else {
                $all_data_text = __('term ', 'wp-flashcard');
            }
            return $all_data_text;
        }

        /**
         * get flashcard terms starred
         * @param type $set_id
         * @param type $card_id
         * @return string
         */
        function get_flashcard_starred_class($set_id, $card_id) {
            $class = 'removed-from-fav';
            if (!empty($_COOKIE['flashcard_' . $set_id]) && $_COOKIE['flashcard_' . $set_id] !== 'null') {
                $card_ids = json_decode(stripslashes($_COOKIE['flashcard_' . $set_id]), true);
                if (!empty($card_ids) && !empty($card_id)) {
                    foreach ($card_ids as $key => $value) {
                        if ($value == $card_id) {
                            $class = 'added-to-fav';
                            break;
                        }
                    }
                }
            }
            return $class;
        }

        /**
         * get starred terms
         */
        function get_starred_terms() {

            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $data = $this->get_flashcard_terms_from_cookie($set_id);
                if (!empty($data)) {
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                    $switched = 0;
                    $shuffle_class = 'shuffle-not-active';
                    $switch_class = 'switch-not-active';
                    // get all term text
                    $all_data_text = $this->get_all_term_text($all_data_count);
                    //get old cookie data to randumize
                    $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);
                    if ($flashcard_shuffle == 'yes') {
                        shuffle($data);
                        $shuffle_class = 'shuffle-active';
                    }
                    //get old cookie data to switch
                    $flashcard_switch = $this->get_switch_data_from_cookie($set_id);
                    if ($flashcard_switch == 'yes') {
                        $switch_class = 'switch-active';
                        $switched = 1;
                    }
                    $current_post_id = reset($data);
                    ob_start();
                    include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                    $return = ob_get_clean();
                }
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * get all terms
         */
        function get_all_terms() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $data = $this->get_flashcard_terms($set_id);
                if (!empty($data)) {
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);

                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);

                    // get starred term text
                    $starred_term_text = $this->get_starred_term_text($starred_term_count);
                    // get all term text
                    $all_data_text = $this->get_all_term_text($all_data_count);

                    $starred = 0;
                    $obj = $this;
                    //get old cookie data
                    $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);

                    $shuffle_class = 'shuffle-not-active';
                    if ($flashcard_shuffle == 'yes') {
                        shuffle($data);
                        $shuffle_class = 'shuffle-active';
                    }
                    //get old cookie data to switch
                    $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                    $switch_class = 'switch-not-active';
                    $switched = 0;
                    if ($flashcard_switch == 'yes') {
                        $switch_class = 'switch-active';
                        $switched = 1;
                    }
                    $current_post_id = reset($data);
                    ob_start();
                    include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                    $return = ob_get_clean();
                }
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * disable shuffle card
         */
        function enable_shuffle_cards() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['id']) && !empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $current_post_id = sanitize_text_field($_POST['id']);
                $fc_all = json_decode(str_replace("\\", "", $_POST['fc_all']));
                if (!empty($fc_all)) {
                    $data = $fc_all;
                }
                $index_data_before = array_keys($data, $current_post_id);
                $index_before = $index_data_before[0];

                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);

                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }
                //get old cookie data to switch
                $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                $switch_class = 'switch-not-active';
                $switched = 0;
                if ($flashcard_switch == 'yes') {
                    $switch_class = 'switch-active';
                    $switched = 1;
                }

                //randomize array
                shuffle($data);
                $shuffle_class = 'shuffle-active';

                $index_data_after = array_keys($data, $current_post_id);
                $index_after = $index_data_after[0];

                $out = array_splice($data, $index_after, 1);
                array_splice($data, $index_before, 0, $out);


                $expire = strtotime('+2 years');
                setcookie('flashcard_shuffle_' . $set_id, 'yes', $expire, '/');

                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * disable shuffle card
         */
        function disabled_shuffle_cards() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['id']) && !empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $current_post_id = sanitize_text_field($_POST['id']);
                $fc_all = json_decode(str_replace("\\", "", $_POST['fc_all']));
                if (!empty($fc_all)) {
                    $data = $fc_all;
                }
                $index_data_before = array_keys($data, $current_post_id);
                $index_before = $index_data_before[0];

                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);

                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }
                //get old cookie data to switch
                $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                $switch_class = 'switch-not-active';
                $switched = 0;
                if ($flashcard_switch == 'yes') {
                    $switch_class = 'switch-active';
                    $switched = 1;
                }
                $index_data_after = array_keys($data, $current_post_id);
                $index_after = $index_data_after[0];

                $out = array_splice($data, $index_after, 1);
                array_splice($data, $index_before, 0, $out);

                $expire = strtotime('+2 years');
                setcookie('flashcard_shuffle_' . $set_id, 'no', $expire, '/');
                $shuffle_class = 'shuffle-not-active';

                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * enable switch card
         */
        function enable_switch_cards() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['id']) && !empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $current_post_id = sanitize_text_field($_POST['id']);
                $fc_all = json_decode(str_replace("\\", "", $_POST['fc_all']));
                if (!empty($fc_all)) {
                    $data = $fc_all;
                }
                $index_data_before = array_keys($data, $current_post_id);
                $index_before = $index_data_before[0];
                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }

                //get old cookie data to randomize array
                $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);

                $shuffle_class = 'shuffle-not-active';
                if ($flashcard_shuffle == 'yes') {

                    shuffle($data);
                    $shuffle_class = 'shuffle-active';
                }
                $index_data_after = array_keys($data, $current_post_id);
                $index_after = $index_data_after[0];

                $out = array_splice($data, $index_after, 1);
                array_splice($data, $index_before, 0, $out);

                $expire = strtotime('+2 years');
                setcookie('flashcard_switch_' . $set_id, 'yes', $expire, '/');

                $switched = 1;
                $switch_class = 'switch-active';
                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * disable switch card
         */
        function disabled_switch_cards() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['id']) && !empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                $current_post_id = sanitize_text_field($_POST['id']);
                $fc_all = json_decode(str_replace("\\", "", $_POST['fc_all']));
                if (!empty($fc_all)) {
                    $data = $fc_all;
                }
                $index_data_before = array_keys($data, $current_post_id);
                $index_before = $index_data_before[0];
                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);

                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }

                //get old cookie data to randomize array
                $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);

                $shuffle_class = 'shuffle-not-active';
                if ($flashcard_shuffle == 'yes') {

                    shuffle($data);
                    $shuffle_class = 'shuffle-active';
                }
                $index_data_after = array_keys($data, $current_post_id);
                $index_after = $index_data_after[0];

                $out = array_splice($data, $index_after, 1);
                array_splice($data, $index_before, 0, $out);
                $expire = strtotime('+2 years');
                setcookie('flashcard_switch_' . $set_id, 'no', $expire, '/');

                $switched = 0;
                $switch_class = 'switch-not-active';

                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * get next/prev post
         */
        function get_post_card() {
            $return = '';
            if (!empty($_POST['id']) && !empty($_POST['set_id'])) {
                $current_post_id = $_POST['id'];
                $set_id = $_POST['set_id'];
                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);

                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }
                //randomize array
                $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);
                $fc_all = json_decode(str_replace("\\", "", $_POST['fc_all']));
                if (!empty($fc_all)) {
                    $data = $fc_all;
                }
                $shuffle_class = 'shuffle-not-active';
                if ($flashcard_shuffle == 'yes') {
                    if (empty($data)) {
                        shuffle($data);
                    }
                    $shuffle_class = 'shuffle-active';
                }
                //get old cookie data to switch
                $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                $switch_class = 'switch-not-active';
                $switched = 0;
                if ($flashcard_switch == 'yes') {
                    $switch_class = 'switch-active';
                    $switched = 1;
                }

                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        /**
         * start over button
         */
        function get_all_post_cards() {
            $return = __('No Results Found', 'wp-flashcard');
            if (!empty($_POST['set_id'])) {
                $set_id = sanitize_text_field($_POST['set_id']);
                if (!empty($_POST['starred']) && sanitize_text_field($_POST['starred']) == 1) {
                    $data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data = $this->get_flashcard_terms($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = 0;
                    $starred = 1;
                } else {
                    $data = $this->get_flashcard_terms($set_id);
                    $all_data = $data;
                    $starred_data = $this->get_flashcard_terms_from_cookie($set_id);
                    $all_data_count = count($all_data);
                    $starred_term_count = count($starred_data);
                    $starred = 0;
                    $obj = $this;
                }

                // get starred term text
                $starred_term_text = $this->get_starred_term_text($starred_term_count);
                // get all term text
                $all_data_text = $this->get_all_term_text($all_data_count);

                //get old cookie data
                $flashcard_shuffle = $this->get_shuffle_data_from_cookie($set_id);

                $shuffle_class = 'shuffle-not-active';
                if ($flashcard_shuffle == 'yes') {
                    shuffle($data);
                    $shuffle_class = 'shuffle-active';
                }
                //get old cookie data to switch
                $flashcard_switch = $this->get_switch_data_from_cookie($set_id);

                $switch_class = 'switch-not-active';
                $switched = 0;
                if ($flashcard_switch == 'yes') {
                    $switch_class = 'switch-active';
                    $switched = 1;
                }
                $current_post_id = reset($data);
                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-content-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        function update_card_set() {
            $return = '';
            if (!empty($_POST['set_id'])) {
                global $wpdb;
                $screen = get_current_screen();
                $post_per_page = 50;
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                $set_id = $_POST['set_id'];
                $card_id = (!empty($_POST['card_id'])) ? $_POST['card_id'] : '';
                $foreground_word = (!empty($_POST['foreground_word'])) ? esc_attr($_POST['foreground_word']) : '';
                $background_word = (!empty($_POST['background_word'])) ? esc_attr($_POST['background_word']) : '';
                if (!empty($foreground_word) || !empty($background_word)) {
                    if (!empty($card_id)) {
                        $result = $wpdb->update($table_flashcard_set_process, array('foreground_card' => $foreground_word, 'background_card' => $background_word), array('id' => $card_id, 'flashcard_id' => $set_id), array('%s', '%s'), array('%d', '%d'));
                    } else {
                        $wpdb->insert($table_flashcard_set_process, array('flashcard_id' => $set_id, 'foreground_card' => $foreground_word, 'background_card' => $background_word), array('%d', '%s', '%s'));
                    }

                    $sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id Limit 0,$post_per_page";
                    $results = $wpdb->get_results($sql);
                    $total_sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id";
                    $total_results = $wpdb->get_results($total_sql);

                    ob_start();
                    include_once WP_FLASHCARD_PATH . 'includes/admin/views/load-more-cards.php';
                    $return = ob_get_clean();
                }
            }
            wp_send_json($return);
            wp_die();
        }

        function load_more_cards() {
            global $wpdb;
            $return = '';
            if (!empty($_POST['paged']) && !empty($_POST['set_id'])) {
                $set_id = $_POST['set_id'];
                $paged = intval($_POST['paged']);
                $post_per_page = 50;
                $limit = ($paged - 1) * $post_per_page;
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                $sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id Limit $limit,$post_per_page";
                $results = $wpdb->get_results($sql);
                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/admin/views/load-more-cards-html.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

        function remove_card_data() {
            $return = '';
            if (!empty($_POST['card_id']) && !empty($_POST['set_id'])) {
                global $wpdb;
                $screen = get_current_screen();
                $post_per_page = 50;
                $set_id = $_POST['set_id'];
                $card_id = $_POST['card_id'];
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                $wpdb->delete($table_flashcard_set_process, array('id' => $card_id, 'flashcard_id' => $set_id), array('%d', '%d'));
                $sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id Limit 0,$post_per_page";
                $results = $wpdb->get_results($sql);
                $total_sql = "select * from $table_flashcard_set_process where flashcard_id= $set_id";
                $total_results = $wpdb->get_results($total_sql);

                ob_start();
                include_once WP_FLASHCARD_PATH . 'includes/admin/views/load-more-cards.php';
                $return = ob_get_clean();
            }
            wp_send_json($return);
            wp_die();
        }

    }

    $instance = new Wp_Flashcard_Process();
}