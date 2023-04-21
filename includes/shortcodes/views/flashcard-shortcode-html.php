<?php
if (!empty($data) && !empty($set_id)) {
    $width = esc_attr(get_option('wp_flashcard_width_setting')) . 'px';
    $card_item_width = "width:$width;";

    $height = esc_attr(get_option('wp_flashcard_height_setting'));
    if (!$height) {
        $height = 350;
        if (wp_is_mobile())
            $height = 378;
    }
    $height += 50;
    $card_item_height = "height: $height" . "px;";
    ?>
    <div class="flashcard_set" action="" style="<?php echo $card_item_width . $card_item_height; ?>">
        <?php
        $current_post_id = reset($data);
        include('flashcard-shortcode-content-html.php');
        ?>
    </div>
<?php } ?>

