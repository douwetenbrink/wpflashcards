<?php

add_shortcode('flashcard_set', 'flashcard_set');

function flashcard_set($atts) {
    $a = shortcode_atts(array(
        'id' => '',
        'starred' => ''
            ), $atts);

    $starred = 0;
    $switched = 0;
    $shuffle_class = 'shuffle-not-active';
    $switch_class = 'switch-not-active';

    $obj = new Wp_Flashcard_Process();
    $starred_data = array();
    $set_id = '';
    if (!empty($a['id'])) {
        $set_id = $a['id'];
        $data = $obj->get_flashcard_terms($set_id);
        $all_data = $data;
        $starred_data = $obj->get_flashcard_terms_from_cookie($set_id);
    }
    if (!empty($data)) {
        $all_data_count = $starred_term_count = 0;
        if (!empty($all_data)) {
            $all_data_count = count($all_data);
        }
        if (!empty($starred_data)) {
            $starred_term_count = count($starred_data);
        }
        // get starred term text
        $starred_term_text = $obj->get_starred_term_text($starred_term_count);
        // get all term text
        $all_data_text = $obj->get_all_term_text($all_data_count);

        //get old cookie data to randamize
        $flashcard_shuffle = $obj->get_shuffle_data_from_cookie($set_id);
        if ($flashcard_shuffle == 'yes') {
            shuffle($data);
            $shuffle_class = 'shuffle-active';
        }
        //get old cookie data to switch
        $flashcard_switch = $obj->get_switch_data_from_cookie($set_id);
        if ($flashcard_switch == 'yes') {
            $switch_class = 'switch-active';
            $switched = 1;
        }
        flashcard_enqueue_scripts_and_styles();
        ob_start();
        include WP_FLASHCARD_PATH . 'includes/shortcodes/views/flashcard-shortcode-html.php';
        return ob_get_clean();
    }
    return '';
}

function flashcard_enqueue_scripts_and_styles() {
    wp_enqueue_style('owl-carousel-style');
    wp_enqueue_style('owl-carousel-theme-style');
    wp_enqueue_style('animate-style');
    wp_enqueue_style('scrollbar-style');
    wp_enqueue_style('flashcard-style');

    wp_enqueue_script('flip-script');
    wp_enqueue_script('owl-carousel-script');
    wp_enqueue_script('scrollbar-script');
    wp_enqueue_script('flashcard-script');
    wp_localize_script('flashcard-script', 'obj', array('ajax_url' => admin_url('admin-ajax.php'), 'hide_loader' => get_option('wp_flashcard_card_hide_loader')));
}
