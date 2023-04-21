<?php
if (!empty($data)) {
    $font_size = esc_attr(get_option('wp_flashcard_font_size')) ? get_option('wp_flashcard_font_size') : '30';
    $text_color = esc_attr(get_option('wp_flashcard_text_color'));
    $card_background_color = esc_attr(get_option('wp_flashcard_card_background_color'));

    global $wpdb;
    $table_flashcard_set_process = $wpdb->prefix . 'flashcard_set_process';
    $card_foreground = $card_background = '';
    $card_sql = "select * from $table_flashcard_set_process where id = $current_post_id  and flashcard_id = $set_id ";
    $card_data = $wpdb->get_results($card_sql);

    if (!empty($card_data)) {
        $card_foreground = html_entity_decode(stripslashes($card_data[0]->foreground_card));
        $card_background = html_entity_decode(stripslashes($card_data[0]->background_card));
    }


    if ($starred == 1) {
        $class = 'added-to-fav';
        $fav_text = __('Remove star', 'wp-flashcard');
    } else {
        if ($starred == 2) {
            $class = 'removed-from-fav';
            $fav_text = __('Star card for study later', 'wp-flashcard');
        } else {
            $class = $obj->get_flashcard_starred_class($set_id, $current_post_id);
            if ($class == 'added-to-fav') {
                $fav_text = __('Remove star', 'wp-flashcard');
            } elseif ($class == 'removed-from-fav') {
                $fav_text = __('Star card for study later', 'wp-flashcard');
            }
        }
    }

    if ($switched == 1) {
        $foreground = $card_background;
        $background = $card_foreground;
    } else {
        $foreground = $card_foreground;
        $background = $card_background;
    }

    $height = esc_attr(get_option('wp_flashcard_height_setting')) . 'px';
    $card_item_height = "height: $height";
    $image_style = "min-height:210px";
    ?>
    <div class="fc-item card-fc-item">
        <div class="item">
            <div id="card" class="card-item" style="<?php echo $card_item_height; ?>;">
                <div class="front" style="background-color:<?php echo $card_background_color; ?>;">
                    <div class="fav-container">
                        <div class="loader-container" style="display:none;">
                            <div class="loader"></div>
                        </div>
                        <a href="#" class="fav <?php echo $class; ?>" data-card_id='<?php echo $current_post_id; ?>'
                           data-set_id='<?php echo $set_id; ?>'>
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" role="img"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                 class="svg-inline--fa fa-star fa-w-18 fa-2x">
                                <path fill="currentColor"
                                      d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"
                                      class=""></path>
                            </svg>
                        </a>
                        <div class="UITooltip UITooltip--micro UITooltip--includeArrowInBounds fav-tooltip"
                             data-remove="<?php echo esc_attr__('Star card for study later', 'wp-flashcard'); ?>"
                             data-add="<?php echo esc_attr__('Remove star', 'wp-flashcard'); ?>">
                            <span><?php echo $fav_text; ?></span></div>
                    </div>
                    <?php $added_class = ''; ?>
                    <?php if (preg_match('/(\.jpg|\.png|\.bmp|\.gif|\.jpeg|\.png|\.tiff|\.tif|\.eps|\.raw|\.cr2|\.nef|\.orf|\.sr2)$/', $foreground)) { ?>
                        <div class="card-text card-text-img"><img src="<?php echo $foreground; ?>"
                                                                  style="<?php echo $image_style; ?>"></div>
                    <?php } else if (preg_match('/(\.mp3|\.ogg)$/', $foreground)) { ?>
                        <div class="audio-container">
                            <audio controls preload="auto" controlsList="nodownload" style="width:100%">
                                <?php
                                if (preg_match('/(\.mp3)$/', $foreground))
                                    $type = 'audio/mp3';
                                else
                                    $type = 'audio/ogg';
                                ?>
                                <source src="<?php echo $foreground; ?>" type="<?php echo $type; ?>">
                            </audio>
                        </div>
                    <?php } else if (preg_match('/(\.mp4)$/', $foreground) || strstr($foreground, 'vimeo') || strstr($foreground, 'youtube') || strstr($foreground, 'wistia')) { ?>
                        <div class="video-container">

                            <?php if (strstr($foreground, 'vimeo') || strstr($foreground, 'youtube') || strstr($foreground, 'wistia')): ?>

                                <iframe src="<?php echo $foreground; ?>?autoplay=0" style="width:70%;height:100%;"
                                        frameborder="0" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>
                            <?php else: ?>
                                <video style="width:70%;height:100%;" controls>
                                    <source src="<?php echo $foreground; ?>">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php } else { ?>
                        <?php
                        if (preg_match('/.(\.mp3|\.ogg|\.mp4)/', $foreground))
                            $added_class = 'audio-video-container';
                        ?>
                        <div class="card-text <?php echo $added_class;?>"
                             style="font-size:<?php echo $font_size; ?>px;color:<?php echo $text_color; ?>"><?php echo $foreground; ?></div>
                    <?php } ?>
                </div>
                <div class="back" style="background-color:<?php echo $card_background_color; ?>;">
                    <?php $added_class = ''; ?>
                    <?php if (preg_match('/(\.jpg|\.png|\.bmp|\.gif|\.jpeg|\.png|\.tiff|\.tif|\.eps|\.raw|\.cr2|\.nef|\.orf|\.sr2)$/', $background)) { ?>
                        <div class="card-text card-text-img "><img src="<?php echo $background; ?>"
                                                                   style="<?php echo $image_style; ?>"></div>
                    <?php } else if (preg_match('/(\.mp3|\.ogg)$/', $background)) { ?>
                        <div class="audio-container">
                            <audio controls preload="auto" controlsList="nodownload"
                                   style="width:100%">                                                            <?php
                                if (preg_match('/(\.mp3)$/', $background))
                                    $type = 'audio/mp3';
                                else
                                    $type = 'audio/ogg';
                                ?>
                                <source src="<?php echo $background; ?>" type="<?php echo $type; ?>">
                            </audio>
                        </div>
                    <?php } else if (preg_match('/(\.mp4)$/', $background) || strstr($background, 'vimeo') || strstr($background, 'youtube') || strstr($background, 'wistia')) { ?>
                        <div class="video-container">

                            <?php if (strstr($background, 'vimeo') || strstr($background, 'youtube') || strstr($background, 'wistia')): ?>

                                <iframe src="<?php echo $background; ?>?autoplay=0" style="width:70%;height:100%;"
                                        frameborder="0" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>
                            <?php else: ?>
                                <video style="width:70%;height:100%;" controls>
                                    <source src="<?php echo $background; ?>">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php } else { ?>
                        <?php
                        if (preg_match('/.(\.mp3|\.ogg|\.mp4)/', $background))
                            $added_class = 'audio-video-container';
                        ?>
                        <div class="card-text <?php echo $added_class;?>"
                             style="font-size:<?php echo $font_size; ?>px;color:<?php echo $text_color; ?>"><?php echo $background; ?></div>
                    <?php } ?>
                </div>
                <div class="loader-container full-screen" style="display:none;">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $next_style = 'display:none!important;';
    if (empty($next_post_id))
        $next_style = '';
    ?>
    <div class="fc-item card-note" style="<?php echo $next_style; ?>">
        <div class="item">
            <div id="card" class="card-item" style="<?php echo $card_item_height; ?>">
                <div class="front">
                    <div class="card-result-content">
                        <strong><?php echo esc_html__('Nice work', 'wp-flashcard'); ?>!</strong>
                        <span class="card-text"><?php echo esc_html__('You just studied ', 'wp-flashcard') . count($data) . esc_html__(' terms', 'wp-flashcard') ?>!</span>
                        <span><a href="#" class="fc-btn start-over-flashcard"
                                 data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Start over', 'wp-flashcard'); ?></a></span>
                        <?php
                        if ($starred != 1 && $starred_term_count >= 1) {
                            ?>
                            <span class="starred-term-container"><a href="#" class="fc-btn starred-term-flashcard"
                                                                    data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study ', 'wp-flashcard'); ?><span
                                            class="starred-term-count"><?php echo $starred_term_count . ' '; ?></span><span
                                            class="starred-term-text"
                                            data-single="<?php esc_attr_e('starred term ', 'wp-flashcard'); ?>"
                                            data-plural="<?php esc_attr_e('starred terms ', 'wp-flashcard'); ?>"><?php echo $starred_term_text; ?></span></a></span>
                            <span class="all-term-container" style="display:none;"><a href="#"
                                                                                      class="fc-btn all-term-flashcard"
                                                                                      data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study all ', 'wp-flashcard'); ?><span
                                            class="all-term-count"><?php echo $all_data_count . ' '; ?></span><?php echo $all_data_text; ?></a></span>

                            <?php
                        } elseif (!empty($action) && $action == 'starred' && $all_data_count >= 1 || $starred == 1) {
                            ?>
                            <span class="all-term-container"><a href="#" class="fc-btn all-term-flashcard"
                                                                data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study all ', 'wp-flashcard'); ?><span
                                            class="all-term-count"><?php echo $all_data_count . ' '; ?></span><?php echo $all_data_text; ?></a></span>
                            <span class="starred-term-container" style="display:none;"><a href="#"
                                                                                          class="fc-btn starred-term-flashcard"
                                                                                          data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study ', 'wp-flashcard'); ?><span
                                            class="starred-term-count"><?php echo $starred_term_count . ' '; ?></span><span
                                            class="starred-term-text"
                                            data-single="<?php esc_attr_e('starred term ', 'wp-flashcard'); ?>"
                                            data-plural="<?php esc_attr_e('starred terms ', 'wp-flashcard'); ?>"><?php echo $starred_term_text; ?></span></a></span>
                            <?php
                        } else {
                            ?>
                            <span class="all-term-container" style="display:none;"><a href="#"
                                                                                      class="fc-btn all-term-flashcard"
                                                                                      data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study all ', 'wp-flashcard'); ?><span
                                            class="all-term-count"><?php echo $all_data_count . ' '; ?></span><?php echo $all_data_text; ?></a></span>
                            <span class="starred-term-container" style="display:none;"><a href="#"
                                                                                          class="fc-btn starred-term-flashcard"
                                                                                          data-set_id="<?php echo $set_id; ?>"><?php echo esc_html__('Study ', 'wp-flashcard'); ?><span
                                            class="starred-term-count"><?php echo $starred_term_count . ' '; ?></span><span
                                            class="starred-term-text"
                                            data-single="<?php esc_attr_e('starred term ', 'wp-flashcard'); ?>"
                                            data-plural="<?php esc_attr_e('starred terms ', 'wp-flashcard'); ?>"><?php echo $starred_term_text; ?></span></a></span>

                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>