<?php
$obj = new Wp_Flashcard_Process();
$prev_post_id = $next_post_id = '';
$array_values = array();
$array_values = array_values($data);
if (!empty($array_values[(array_search($current_post_id, $array_values) - 1)])) {
    $prev_post_id = $array_values[(array_search($current_post_id, $array_values) - 1)];
}
if (!empty($array_values[(array_search($current_post_id, $array_values) + 1)])) {
    $next_post_id = $array_values[(array_search($current_post_id, $array_values) + 1)];
}

if (empty($next_post_id))
    $next_post_id = '';
if (empty($prev_post_id))
    $prev_post_id = '';
$current_index = array_search($current_post_id, $array_values) + 1;
$prev_class = '';
if (empty($prev_post_id))
    $prev_class = 'click_disabled';

$fc_all = htmlspecialchars(json_encode($array_values), ENT_QUOTES, 'UTF-8');
?>
<div class="loader-container full-page"  style="display:none;">
    <div class="loader"></div>
</div>
<div class="loader-container full-screen" style="display:none;">
    <div class="loader"></div>
</div>
<div class="flashcard_set-container" style="display:none">
    <div class="fc-carousel fc-theme fc-loaded fc-drag">
        <?php include('flashcard-items-html.php'); ?>
    </div>
    <div class="nav-container">
        <div class="fc-nav">

            <button type="button" role="presentation" class="fc-prev <?php echo $prev_class; ?>" data-fc_all='<?php echo $fc_all; ?>' data-id="<?php echo $prev_post_id; ?>" data-set_id="<?php echo $set_id; ?>">
                <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="arrow-left" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-arrow-left fa-w-14 fa-2x">
                    <path fill="currentColor" d="M229.9 473.899l19.799-19.799c4.686-4.686 4.686-12.284 0-16.971L94.569 282H436c6.627 0 12-5.373 12-12v-28c0-6.627-5.373-12-12-12H94.569l155.13-155.13c4.686-4.686 4.686-12.284 0-16.971L229.9 38.101c-4.686-4.686-12.284-4.686-16.971 0L3.515 247.515c-4.686 4.686-4.686 12.284 0 16.971L212.929 473.9c4.686 4.686 12.284 4.686 16.971-.001z" class=""></path>
                </svg>
            </button>
            <div class="navControl progressIndex">
                <span class="UIText">
                    <span class="current-cards-slider-pages"><?php echo $current_index; ?></span>/<span class="cards-slider-pages"><?php echo (count($data)); ?></span></span>
            </div>
            <button type="button" role="presentation" class="fc-next" data-current_id="<?php echo $current_post_id; ?>" data-fc_all='<?php echo $fc_all; ?>' data-id="<?php echo $next_post_id; ?>" data-set_id="<?php echo $set_id; ?>">
                <svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-arrow-right fa-w-14 fa-2x"><path fill="currentColor" d="M218.101 38.101L198.302 57.9c-4.686 4.686-4.686 12.284 0 16.971L353.432 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h341.432l-155.13 155.13c-4.686 4.686-4.686 12.284 0 16.971l19.799 19.799c4.686 4.686 12.284 4.686 16.971 0l209.414-209.414c4.686-4.686 4.686-12.284 0-16.971L235.071 38.101c-4.686-4.687-12.284-4.687-16.97 0z" class=""></path></svg>
            </button>
        </div>
        <div class="fc-btns-container">
            <div class="shuffle-btn-container">
                <div class="loader-container" style="display:none;">
                    <div class="loader"></div>
                </div>
                <a href="#" class="shuffle-btn <?php echo $shuffle_class; ?>" data-current_id="<?php echo $current_post_id; ?>" data-set_id="<?php echo $set_id; ?>">
                    <span class="UIButton-wrapper">
                        <svg id="shuffle" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.1825 7.17L3.7125 2.7C3.3225 2.31 2.6925 2.31 2.3025 2.7C1.9125 3.09 1.9125 3.72 2.3025 4.11L6.7625 8.57L8.1825 7.17ZM12.9425 2.85L14.1325 4.04L2.2925 15.88C1.9025 16.27 1.9025 16.9 2.2925 17.29C2.6825 17.68 3.3125 17.68 3.7025 17.29L15.5525 5.46L16.7425 6.65C17.0525 6.96 17.5925 6.74 17.5925 6.29V2.5C17.5925 2.22 17.3725 2 17.0925 2H13.3025C12.8525 2 12.6325 2.54 12.9425 2.85ZM12.4225 11.41L11.0125 12.82L14.1425 15.95L12.9425 17.15C12.6325 17.46 12.8525 18 13.3025 18H17.0925C17.3725 18 17.5925 17.78 17.5925 17.5V13.71C17.5925 13.26 17.0525 13.04 16.7425 13.36L15.5525 14.55L12.4225 11.41Z"></path>
                        </svg>
                    </span>
                </a>
                <div class="UITooltip UITooltip--micro UITooltip--includeArrowInBounds"><span><?php echo esc_html__('Shuffle', 'wp-flashcard'); ?></span></div>

            </div>
            <div class="switch-btn-container">
                <div class="loader-container" style="display:none;">
                    <div class="loader"></div>
                </div>
                <a href="#" class="switch-btn <?php echo $switch_class; ?>" data-current_id="<?php echo $current_post_id; ?>" data-set_id="<?php echo $set_id; ?>">
                    <span class="UIButton-wrapper">
                        <svg id="switch" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.35893 13.3489L4.14893 16.1389C4.45893 16.4589 4.99893 16.2389 4.99893 15.7889V13.9989H11.0089C11.5589 13.9989 12.0089 13.5489 12.0089 12.9989C12.0089 12.4489 11.5589 11.9989 11.0089 11.9989H4.99893V10.2089C4.99893 9.75892 4.45893 9.53892 4.14893 9.85892L1.35893 12.6489C1.16893 12.8389 1.16893 13.1589 1.35893 13.3489ZM15.0189 5.99892H9.00893C8.45893 5.99892 8.00893 6.44892 8.00893 6.99892C8.00893 7.54892 8.45893 7.99892 9.00893 7.99892H15.0189V9.78892C15.0189 10.2389 15.5589 10.4589 15.8689 10.1389L18.6489 7.34892C18.8389 7.14892 18.8389 6.83892 18.6489 6.63892L15.8689 3.84892C15.5589 3.52892 15.0189 3.75892 15.0189 4.19892V5.99892Z"></path>
                        </svg>
                    </span>
                </a>
                <div class="UITooltip UITooltip--micro UITooltip--includeArrowInBounds"><span><?php echo esc_html__('Switch front and back side', 'wp-flashcard'); ?></span></div>
            </div>
        </div>
    </div>
</div>