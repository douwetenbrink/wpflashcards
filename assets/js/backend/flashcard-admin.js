

var tableHtml = '<tr style="cursor: pointer;">';
tableHtml += '<td style="width: 40%;"><div class="upload-container">';
tableHtml += '<input required="" class="keyboardInput source_foreground_word" data-set_id="' + obj.set_id + '" style="width: 95%;" type="text" name="flashcard_foreground_word_flashcard_index" value=""/>';
tableHtml += '<a href="javascript:void(0)" title="' + obj.upload_title + '" data-input-id="flashcard_foreground_img" class="flashcard_upload_button"><img src="' + obj.upload_img + '"></a>';
tableHtml += '</div></td>';
tableHtml += '<td style="width: 40%;"> <div class="upload-container"><input required="" class="source_background_word" data-set_id="' + obj.set_id + '" style="width: 95%;" type="text" name="flashcard_background_word_flashcard_index" value=""/>';
tableHtml += '<a href="javascript:void(0)" title="' + obj.upload_title + '" data-input-id="flashcard_foreground_img" class="flashcard_upload_button"><img src="' + obj.upload_img + '"></a>';
tableHtml += '</div></td>';
tableHtml += '<td style="width: 10%;vertical-align: middle;">';
tableHtml += '<a href="javascript:void(0)" title="' + obj.save_card_title + '" data-set_id="' + obj.set_id + '" data-card_id="" class="save_card"><img src="' + obj.save_img + '"></a>';
tableHtml += '</td>';
tableHtml += '<td style="width: 10%;vertical-align: middle;">';
tableHtml += '<a href="javascript:void(0)" title="' + obj.add_card_title + '" class="add_flashcard"><span class="dashicons dashicons-insert"></span></a> ';
tableHtml += '<a href="javascript:void(0)" title="' + obj.remove_card_title + '" data-set_id="' + obj.set_id + '" data-card_id=""class="remove_flashcard"><span class="dashicons dashicons-remove"></span></a>';
tableHtml += '</td>';
tableHtml += '</tr>';


var custom_uploader;
var caller;
var paged = 1;

//check if word exist

jQuery(document).on('click', '.save_card', function () {
    var card_id = jQuery(this).attr('data-card_id');
    var set_id = jQuery(this).attr('data-set_id');
    var foreground_word = jQuery(this).closest('tr').find('.source_foreground_word').val();
    var background_word = jQuery(this).closest('tr').find('.source_background_word').val();
    if (foreground_word || background_word) {
        jQuery('.loader-container').show();
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'update_card_set',
                'card_id': card_id,
                'set_id': set_id,
                'background_word': background_word,
                'foreground_word': foreground_word,
            },
            success: function (data) {
                if (data) {
                    jQuery(".admin_flashcard_sets").html('');
                    jQuery(".admin_flashcard_sets").html(data);
                    paged = 1;
                }
                jQuery('.loader-container').hide();
            }
        });
    } else {
        alert("Card shouldn't be  empty");
    }

});
//show / hide flashcard section
jQuery(document).on('click', '.flashcard-header', function () {
    if (jQuery(this).next().is(':hidden')) {
//hide all open tables
        jQuery('.flashcard_table').slideUp(100);
        jQuery(this).next().slideDown(100);
    } else {
        jQuery(this).next().slideUp(100);
    }
});

//remove falshcard
jQuery(document).on('click', '.remove_flashcard', function (e) {
    e.preventDefault();
    var remove_btn = jQuery(this);
    var card_id = jQuery(this).attr("data-card_id");
    var set_id = jQuery(this).attr('data-set_id');
    if (card_id && set_id) {
        var data = {
            action: 'remove_card_data',
            'set_id': set_id,
            'card_id': card_id,
        };
        jQuery('.loader-container').show();
        jQuery.ajax({
            type: 'POST',
            url: obj.ajax_url,
            data: data,
            success: function (data) {
                if (data) {
                    jQuery(".admin_flashcard_sets").html('');
                    jQuery(".admin_flashcard_sets").html(data);
                    paged = 1;
                }
                jQuery('.loader-container').hide();

            },

        });
    } else {
        remove_btn.parent().parent().remove();

    }

});
//add more cards
jQuery(document).on('click', '.add_flashcard', function () {
//get last table
    var lastTabelIndex = jQuery('#flashcard_table_sortable tr').length;
    pretableHtml = tableHtml.replace(/flashcard_index_plus/g, parseInt(lastTabelIndex));
    jQuery('.flashcard_table tbody').append(pretableHtml.replace(/flashcard_index/g, parseInt(lastTabelIndex)));

});

jQuery(document).ready(function () {

    jQuery('#wp_flashcard_text_color,#wp_flashcard_card_background_color').wpColorPicker();

    jQuery(document).on('click', '.flashcard_upload_button', function (e) {
        e.preventDefault();

        var input = jQuery(this).parent().find('input'),
                custom_uploader = wp.media({
                    title: 'Insert',
                    library: {
                        type: 'image,audio,video'
                    },
                    button: {
                        text: 'Insert' // button label text
                    },
                    multiple: false
                }).on('select', function () { // it also has "open" and "close" events
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            input.val(attachment.url);

        }).open();

    });
    jQuery(document).on('click', '#load_more_cards', function (e) {
        e.preventDefault();
        var button = jQuery(this);
        var showMoreText = jQuery(this).val();
        var loadingText = jQuery(this).attr("data-loading");
        var posts = jQuery(this).attr("data-posts-no");
        var set_id = jQuery(this).attr('data-set_id');
        jQuery(this).attr("disabled", true).val(loadingText);
        paged++;
        var data = {
            action: 'load_more_cards',
            'set_id': set_id,
            paged: paged,
        };
        jQuery('.loader-container').show();
        jQuery.ajax({
            type: 'POST',
            url: obj.ajax_url,
            data: data,
            success: function (response) {
                if (response) {
                    jQuery("#flashcard_table_sortable").append(response);
                }
            }, complete: function () {
                jQuery('.loader-container').hide();
                button.attr("disabled", false).val(showMoreText);
                if (paged == 0) {
                    paged = 1;
                }
                if (posts <= (paged * 50)) {
                    button.hide();
                }
            }

        });
    });

});