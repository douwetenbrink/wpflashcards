<?php

if (!class_exists('WP_Async_Request', false)) {
    include_once WP_FLASHCARD_PATH . 'includes/admin/wp-async-request.php';
}
if (!class_exists('WP_Background_Process', false)) {
    include_once WP_FLASHCARD_PATH . 'includes/admin/wp-background-process.php';
}
if (!class_exists('WP_Flashcard_Sync_Cards_Integration')) {

    class WP_Flashcard_Sync_Cards_Integration extends WP_Background_Process {

        /**
         * @var string
         */
        protected $action = 'sync_all_flashcards_integration_process';

        /**
         * Task
         *
         * Override this method to perform any actions required on each
         * queue item. Return the modified item for further processing
         * in the next pass through. Or, return false to remove the
         * item from the queue.
         *
         * @param mixed $item Queue item to iterate over
         *
         * @return mixed
         */
        protected function task($data) {
            global $wpdb;
            $set_id = $data['set_id'];
            if (!empty($set_id)) {
                $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
                $cards_data = get_post_meta($set_id, 'falshcard_slides', true);
                update_post_meta($set_id, 'falshcard_slides_backup', $cards_data);
                if (is_array($cards_data)) {
                    foreach ($cards_data as $cards) {
                        $foreground = esc_attr($cards['foreground']);
                        $background = esc_attr($cards['background']);
                        $wpdb->insert($table_flashcard_set_process, array('flashcard_id' => $set_id, 'foreground_card' => $foreground, 'background_card' => $background), array('%d', '%s', '%s'));
                        update_post_meta($set_id, 'flashcard_short_code', "[flashcard_set id='" . $set_id . "']");
                    }
                }
                delete_post_meta($set_id, 'falshcard_slides');
            }
            return false;
        }

        /**
         * Complete
         *
         * Override if applicable, but ensure that the below actions are
         * performed, or, call parent::complete().
         */
        protected function complete() {
            parent::complete();
            delete_option('sync_cards_start');
            update_option('sync_cards_end', 'yes', 'no');
            update_option('sync_cards_msg', 'yes', 'no');
        }

    }

}
new WP_Flashcard_Sync_Cards_Integration();
