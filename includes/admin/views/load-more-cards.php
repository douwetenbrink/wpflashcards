<?php if ($screen->action != 'add') : ?>
    <p><?php _e('Click on save icon ', 'wp-flashcard'); ?>&nbsp;<img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/save.png">&nbsp;<?php _e(' to save card in this set', 'wp-flashcard') ?></p>
    <p><?php _e('Use the ', 'wp-flashcard'); ?>&nbsp;<img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/01.png">&nbsp;<?php _e(' to upload images, sounds or videos to your cards', 'wp-flashcard') ?></p>
    <p><?php echo __('To learn more about how to use the plugin, please see our documentation at: ', 'wp-flashcard') . 'https://www.wpflashcard.com/documentation'; ?></p>
    <table class="widefat flashcard_table">
        <thead>
            <tr>                    
                <th><?php esc_html_e('Card Front', 'wp-flashcard'); ?></th>
                <th><?php esc_html_e('Card Back', 'wp-flashcard'); ?></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="flashcard_table_sortable">    
            <?php include_once WP_FLASHCARD_PATH . 'includes/admin/views/load-more-cards-html.php'; ?>


        </tbody>

    </table>   
<?php else : ?>
    <p><?php _e('Save the set first to allow adding cards', 'wp-flashcard'); ?></p>
<?php endif ?>
<?php if (!empty($total_results) && !empty($results) && count($total_results) > count($results)) { ?>

    <div class="load-more-container">
        <input class="button button-primary"id="load_more_cards" data-set_id="<?php echo $set_id; ?>" data-posts-no="<?php echo count($total_results); ?>" type="button" data-loading="<?php _e('Loading....', 'wp-flashcard'); ?>" value="<?php _e('Show more', 'wp-flashcard'); ?>">
    </div>
<?php } ?>
<div class="loader-container full-page"  style="display:none;">
    <div class="loader"></div>
</div>