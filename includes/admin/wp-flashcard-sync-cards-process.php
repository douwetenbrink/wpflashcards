<?php

if (!class_exists('WP_Async_Request', false)) {
    include_once WP_FLASHCARD_PATH . 'includes/admin/wp-async-request.php';
}
if (!class_exists('WP_Background_Process', false)) {
    include_once WP_FLASHCARD_PATH . 'includes/admin/wp-background-process.php';
}
if (!class_exists('WP_Flashcard_Sync_Cards_Process')) {

    class WP_Flashcard_Sync_Cards_Process extends WP_Background_Process {

        /**
         * @var string
         */
        protected $action = 'sync_all_flashcards_process';

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
            $csv_data = $data['csv_data'];
            $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';

            if (!empty($csv_data) && !empty($set_id)) {
                foreach ($csv_data as $key => $csv_data_value) {
                    if ($key != 0) {
                        $foreground = esc_attr($csv_data_value[0]);
                        $background = esc_attr($csv_data_value[1]);
                        $wpdb->insert($table_flashcard_set_process, array('flashcard_id' => $set_id, 'foreground_card' => $foreground, 'background_card' => $background), array('%d', '%s', '%s'));

                        update_post_meta($set_id, 'flashcard_short_code', "[flashcard_set id='" . $set_id . "']");
                    }
                }
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
            delete_option('sync_start');
            update_option('sync_end', 'yes', 'no');
        }

    }

}
new WP_Flashcard_Sync_Cards_Process();
