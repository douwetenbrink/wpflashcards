  
<?php
$index = 0;
if (!empty($results)):
    foreach ($results as $result):
        $foreground = !empty($result->foreground_card) ? stripslashes($result->foreground_card) : '';
        $background = !empty($result->background_card) ? stripslashes($result->background_card) : '';
        ?>
        <tr style="cursor: pointer;">                            
            <td style="width: 40%;">
                <div class="upload-container">
                    <input required=""  class="keyboardInput source_foreground_word" data-set_id="<?php echo $set_id; ?>" data-card_id="<?php echo $result->id; ?>" style="width: 95%;" type="text" name="flashcard_foreground_word_<?php echo $index; ?>" value="<?php echo $foreground; ?>"/>
                    <a href="javascript:void(0)" title="<?php esc_html_e('Upload Image or Sound', 'wp-flashcard'); ?>" data-input-id="flashcard_foreground_img_<?php echo $index; ?>" class="flashcard_upload_button"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/01.png"></a> 
                </div>
            </td>
            <td style="width: 40%;">
                <div class="upload-container">
                    <input required="" class="source_background_word" data-set_id="<?php echo $set_id; ?>" data-card_id="<?php echo $result->id; ?>" style="width: 95%;" type="text" name="flashcard_background_word_<?php echo $index; ?>" value="<?php echo $background; ?>"/>
                    <a href="javascript:void(0)" title="<?php esc_html_e('Upload Image or Sound', 'wp-flashcard'); ?>" data-input-id="flashcard_background_img_<?php echo $index; ?>" class="flashcard_upload_button"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/01.png"></a>
                </div>
            </td>
            <td style="width: 10%;vertical-align: middle;">
                <a href="javascript:void(0)" title="<?php esc_html_e('Save', 'wp-flashcard'); ?>" data-set_id="<?php echo $set_id; ?>" data-card_id="<?php echo $result->id; ?>" class="save_card"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/save.png"></a>
            </td>
            <td style="width: 10%;vertical-align: middle;">
                <a href="javascript:void(0)" title="<?php esc_html_e('Add Card', 'wp-flashcard'); ?>" class="add_flashcard"><span class="dashicons dashicons-insert"></span></a>
                <a href="javascript:void(0)" title="<?php esc_html_e('Remove Card', 'wp-flashcard'); ?>" data-set_id="<?php echo $set_id; ?>" data-card_id="<?php echo $result->id; ?>" class="remove_flashcard"><span class="dashicons dashicons-remove"></span></a>                                
            </td>

        </tr>

        <?php
        $index++;
    endforeach;
else:
    ?>
    <tr style="cursor: pointer;">                        
        <td style="width: 40%;">
            <div class="upload-container">
                <input required=""  class="keyboardInput source_foreground_word" data-set_id="<?php echo $set_id; ?>" data-card_id="" style="width: 95%;" type="text" name="flashcard_foreground_word_0" value=""/>
                <a href="javascript:void(0)" title="<?php esc_html_e('Upload Image', 'wp-flashcard'); ?>" data-input-id="flashcard_foreground_img_<?php echo $index; ?>" class="flashcard_upload_button"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/01.png"></a>                                 
            </div>
        </td>
        <td style="width: 40%;">
            <div class="upload-container">
                <input required=""  class="source_background_word" data-set_id="<?php echo $set_id; ?>" data-card_id="" style="width: 95%;" type="text" name="flashcard_background_word_0" value=""/>
                <a href="javascript:void(0)" title="<?php esc_html_e('Upload Image', 'wp-flashcard'); ?>" data-input-id="flashcard_background_img_<?php echo $index; ?>" class="flashcard_upload_button"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/01.png"></a>
            </div>
        </td>
        <td style="width: 10%;vertical-align: middle;">
            <a href="javascript:void(0)" title="<?php esc_html_e('Save', 'wp-flashcard'); ?>" data-set_id="<?php echo $set_id; ?>" data-card_id="" class="save_card"><img src="<?php echo WP_FLASHCARD_URL; ?>assets/images/icons/save.png"></a>
        </td>
        <td style="width: 10%;vertical-align: middle;">
            <a href="javascript:void(0)" title="<?php esc_html_e('Add Card', 'wp-flashcard'); ?>" class="add_flashcard"><span class="dashicons dashicons-insert"></span></a>
            <a href="javascript:void(0)" title="<?php esc_html_e('Remove Card', 'wp-flashcard'); ?>" data-set_id="<?php echo $set_id; ?>" data-card_id="" class="remove_flashcard"><span class="dashicons dashicons-remove"></span></a>                                
        </td>
    </tr>
<?php
endif;
?>
