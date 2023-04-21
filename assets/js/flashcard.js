jQuery(window).on('load', function () {
    jQuery('.full-page').hide();
    jQuery('.flashcard_set-container').show();
    register_card();
    //favourite button
    jQuery(document).on('click', '.removed-from-fav', function (e) {
        e.preventDefault();
        var star = jQuery(this);
        var set = star.closest('.flashcard_set');
        var set_id = star.attr('data-set_id');
        var card_id = star.attr('data-card_id');
        var starred = 0;
        var starred_text = '';
        var add_text = star.parent().find('.fav-tooltip').attr('data-add');
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

//        star.css('color', '#ffc107');
        star.parent().find('.UITooltip').css('opacity', '1');
        if (!obj.hide_loader)
            star.parent().find('.loader-container').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'add_flashcard_to_favourite',
                'card_id': card_id,
                'set_id': set_id,
                'starred': starred,
            },
            success: function (data) {
                if (!obj.hide_loader)
                    star.parent().find('.loader-container').css('display', 'none');
                star.parent().find('.fav-tooltip').html(add_text);
                if (data.text == 'success') {
                    set.find('.loader-container').hide();
                    star.addClass('added-to-fav').removeClass('removed-from-fav');
                    if (data.starred_data >= 1) {
                        set.find('.starred-term-container').show();
                        if (data.starred_data > 1) {
                            starred_text = ' ' + set.find('.starred-term-text').attr('data-plural');
                        } else {
                            starred_text = ' ' + set.find('.starred-term-text').attr('data-single');
                        }
                        set.find('.starred-term-count').html(data.starred_data);
                        set.find('.starred-term-text').html(starred_text);
                    } else {
                        set.find('.starred-term-container').hide();
                    }
                }
                setTimeout(function () {
                    star.parent().find('.UITooltip').css('opacity', '0');
                }, 2000);
            },
        });
    });
    jQuery(document).on('click', '.added-to-fav', function (e) {
        e.preventDefault();
        var star = jQuery(this);
        var set = star.closest('.flashcard_set');
        var card_id = star.attr('data-card_id');
        var set_id = star.attr('data-set_id');
        var remove_text = star.parent().find('.fav-tooltip').attr('data-remove');
        var starred_text = '';
        var starred = 0;

        if (set.attr('action') == 'starred') {
            starred = 1;
            if (!obj.hide_loader)
                set.find('.full-screen').css('display', 'flex');
        } else {
            if (!obj.hide_loader)
                star.parent().find('.loader-container').css('display', 'flex');
        }

        star.parent().find('.UITooltip').css('opacity', '1');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'remove_flashcard_from_favourite',
                'card_id': card_id,
                'set_id': set_id,
                'starred': starred,
            },
            success: function (data) {
                if (data) {
                    if (data.text == 'success') {
                        star.parent().find('.loader-container').css('display', 'none');
                        star.parent().find('.fav-tooltip').html(remove_text);
                        star.addClass('removed-from-fav').removeClass('added-to-fav');

                        if (data.starred_data >= 1) {
                            set.find('.starred-term-container').show();
                            if (data.starred_data > 1) {
                                starred_text = ' ' + set.find('.starred-term-text').attr('data-plural');
                            } else {
                                starred_text = ' ' + set.find('.starred-term-text').attr('data-single');
                            }
                            set.find('.starred-term-count').html(data.starred_data);
                            set.find('.starred-term-text').html(starred_text);
                        } else {
                            set.find('.starred-term-container').hide();
                        }
                    } else {
                        if (data.redirect_to_all == 1) {
                            set.attr('action', '');
                        }
                        if (data.text) {
                            set.html(data.text);
                        }
                        set.find('.flashcard_set-container').show();
                        if (!obj.hide_loader)
                            set.find('.full-screen').css('display', 'none');
                        register_card(set);
                    }
                } else {
                    if (data.redirect_to_all == 1) {
                        set.attr('action', '');
                    }
                    set.find('.flashcard_set-container').show();
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    register_card(set);
                    set.find('.fc-btns-container').hide();
                }
                setTimeout(function () {
                    star.parent().find('.UITooltip').css('opacity', '0');
                }, 2000);
            },
        });
    });
    //shuffle button
    jQuery(document).on('click', '.shuffle-not-active', function (e) {
        e.preventDefault();
        var shuffle_btn = jQuery(this);
        var starred = 0;
        var set = shuffle_btn.closest('.flashcard_set');
        var set_id = shuffle_btn.attr('data-set_id');
        var current_id = shuffle_btn.attr('data-current_id');
        var fc_all = set.find('.fc-prev').attr('data-fc_all');
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        shuffle_btn.css('background-color', '#ffcd1f');
        shuffle_btn.parent().find('.UITooltip').css('opacity', '1');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'enable_shuffle_cards',
                'set_id': set_id,
                'id': current_id,
                'starred': starred,
                'fc_all': fc_all,
            },
            success: function (data) {
                if (data) {
                    set.find('.card-fc-item').fadeOut(500, function () {
                        set.html(data);
                        set.find('.flashcard_set-container').show(0, function () {
                            jQuery(this).fadeIn(300, function () {
                                register_card(set);
                            });

                        });

                    });
                }
                if (!obj.hide_loader)
                    set.find('.full-screen').css('display', 'none');
                // register_card();
                shuffle_btn.css('background-color', '#fff');
                shuffle_btn.addClass('shuffle-active').removeClass('shuffle-not-active');
                shuffle_btn.parent().find('.UITooltip').css('opacity', '0');
            },
        });
    });
    jQuery(document).on('click', '.shuffle-active', function (e) {
        e.preventDefault();
        var shuffle_btn = jQuery(this);
        var starred = 0;
        var set = shuffle_btn.closest('.flashcard_set');
        var set_id = shuffle_btn.attr('data-set_id');
        var current_id = shuffle_btn.attr('data-current_id');
        var fc_all = set.find('.fc-prev').attr('data-fc_all');
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        shuffle_btn.parent().find('.UITooltip').css('opacity', '1');
        shuffle_btn.css('background-color', '#ffcd1f');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'disabled_shuffle_cards',
                'set_id': set_id,
                'id': current_id,
                'starred': starred,
                'fc_all': fc_all,
            },
            success: function (data) {
                if (data) {
                    set.find('.card-fc-item').fadeOut(500, function () {
                        set.html(data);
                        set.find('.flashcard_set-container').show(0, function () {
                            jQuery(this).fadeIn(300, function () {
                                register_card(set);
                            });

                        });
                    });
                }
                if (!obj.hide_loader)
                    set.find('.full-screen').css('display', 'none');
                // register_card();
                shuffle_btn.css('background-color', '#fff');
                shuffle_btn.addClass('shuffle-not-active').removeClass('shuffle-active');
                shuffle_btn.parent().find('.UITooltip').css('opacity', '0');
            },
        });
    });
    //switch button
    jQuery(document).on('click', '.switch-not-active', function (e) {
        e.preventDefault();
        var switch_btn = jQuery(this);
        var starred = 0;
        var set = switch_btn.closest('.flashcard_set');
        var set_id = switch_btn.attr('data-set_id');
        var current_id = switch_btn.attr('data-current_id');
        var fc_all = set.find('.fc-prev').attr('data-fc_all');

        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        switch_btn.parent().find('.UITooltip').css('opacity', '1');
        switch_btn.css('background-color', '#ffcd1f');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'enable_switch_cards',
                'set_id': set_id,
                'id': current_id,
                'starred': starred,
                'fc_all': fc_all,
            },
            success: function (data) {

                set.find('.card-fc-item').fadeOut(500, function () {
                    if (data) {
                        set.html(data);
                    }
                    set.find('.flashcard_set-container').show(0, function () {
                        jQuery(this).fadeIn(300, function () {
                            if (!obj.hide_loader)
                                set.find('.full-screen').css('display', 'none');
                            switch_btn.css('background-color', '#fff');
                            switch_btn.addClass('switch-active').removeClass('switch-not-active');
                            switch_btn.parent().find('.UITooltip').css('opacity', '0');
                            register_card(set);
                        });

                    });
                });
            },
        });
    });
    jQuery(document).on('click', '.switch-active', function (e) {
        e.preventDefault();
        var switch_btn = jQuery(this);
        var starred = 0;
        var set = switch_btn.closest('.flashcard_set');
        var set_id = switch_btn.attr('data-set_id');
        var current_id = switch_btn.attr('data-current_id');
        var fc_all = set.find('.fc-prev').attr('data-fc_all');
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        switch_btn.parent().find('.UITooltip').css('opacity', '1');
        switch_btn.css('background-color', '#ffcd1f');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'disabled_switch_cards',
                'set_id': set_id,
                'id': current_id,
                'starred': starred,
                'fc_all': fc_all,
            },
            success: function (data) {

                set.find('.card-fc-item').fadeOut(500, function () {
                    if (data) {
                        set.html(data);
                    }
                    set.find('.flashcard_set-container').show(0, function () {
                        jQuery(this).fadeIn(300, function () {
                            if (!obj.hide_loader)
                                set.find('.full-screen').css('display', 'none');
                            switch_btn.css('background-color', '#fff');
                            switch_btn.addClass('switch-not-active').removeClass('switch-active');
                            switch_btn.parent().find('.UITooltip').css('opacity', '0');
                            register_card(set);
                        });
                    });
                });
            },
        });
    });
    // start over button
    jQuery(document).on('click', '.start-over-flashcard', function (e) {
        e.preventDefault();
        var set_id = jQuery(this).attr('data-set_id');
        var set = jQuery(this).closest('.flashcard_set');
        var starred = 0;
        if (set.attr('action') == 'starred') {
            starred = 1;
        }
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'get_all_post_cards',
                'set_id': set_id,
                'starred': starred,
            },
            success: function (data) {
                if (data) {
                    set.find('.card-fc-item').fadeOut(500, function () {
                        set.html(data);
                        set.find('.flashcard_set-container').show(0, function () {
                            jQuery(this).fadeIn(300, function () {
                                register_card(set);

                            });
                        });
                    });
                }
                if (!obj.hide_loader)
                    set.find('.full-screen').css('display', 'none');
                //register_card();
            },
        });
    });
    // starred tems btns
    jQuery(document).on('click', '.starred-term-flashcard', function (e) {
        e.preventDefault();
        var set_id = jQuery(this).attr('data-set_id');
        var set = jQuery(this).closest('.flashcard_set');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'get_starred_terms',
                'set_id': set_id,
            },
            success: function (data) {
                set.find('.full-screen').css('display', 'none');
                set.find('.starred-term-container').hide();
                set.find('.all-term-container').show();
                if (data) {
                    set.attr('action', 'starred');
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    //  register_card();
                }
            },
        });
    });
    //all terms button
    jQuery(document).on('click', '.all-term-flashcard', function (e) {
        e.preventDefault();
        var set_id = jQuery(this).attr('data-set_id');
        var set = jQuery(this).closest('.flashcard_set');
        if (!obj.hide_loader)
            set.find('.full-screen').css('display', 'flex');
        jQuery.ajax({
            type: "POST",
            url: obj.ajax_url,
            data: {
                'action': 'get_all_terms',
                'set_id': set_id,
            },
            success: function (data) {
                set.find('.full-screen').css('display', 'none');
                set.find('.all-term-container').hide();
                set.find('.starred-term-container').show();
                if (data) {
                    set.attr('action', '');
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    //register_card();
                }
            },
        });
    });

    function swipe_left(el) {
        // e.preventDefault();
        var set = el.closest('.flashcard_set');
        var id = set.find('.fc-next').attr('data-id');
        var current_id = set.find('.fc-next').attr('data-current_id');
        var set_id = set.find('.fc-next').attr('data-set_id');
        var fc_all = set.find('.fc-next').attr('data-fc_all');
        var starred = 0;
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        if (id) {
            if (!obj.hide_loader)
                set.find('.full-screen').css('display', 'flex');
            jQuery.ajax({
                type: "POST",
                url: obj.ajax_url,
                data: {
                    'action': 'get_post_card',
                    'id': id,
                    'set_id': set_id,
                    'starred': starred,
                    'fc_all': fc_all,
                },
                success: function (data) {
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    //register_card();
                },
            });
        } else {
            if (!set.find('.fc-next').hasClass('click_disabled')) {
                set.find('.card-fc-item').hide();
                set.find('.card-note').show();
                set.find('.fc-prev').attr('data-id', current_id);
                set.find('.fc-prev').removeClass('click_disabled');
                register_card();
                set.find('.fc-next').addClass('click_disabled');
            }
        }
    }

    function swipe_right(el) {
        var set = el.closest('.flashcard_set');
        var id = set.find('.fc-prev').attr('data-id');
        var set_id = set.find('.fc-prev').attr('data-set_id');
        var fc_all = set.find('.fc-prev').attr('data-fc_all');
        var starred = 0;
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        if (id) {
            if (!obj.hide_loader)
                set.find('.full-screen').css('display', 'flex');
            jQuery.ajax({
                type: "POST",
                url: obj.ajax_url,
                data: {
                    'action': 'get_post_card',
                    'id': id,
                    'set_id': set_id,
                    'starred': starred,
                    'fc_all': fc_all,
                },
                success: function (data) {
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    // register_card();
                },
            });
        }
    }

    jQuery(document).on('click', '.fc-next', function (e) {
        e.preventDefault();
        var next_arrow = jQuery(this);
        var set = next_arrow.closest('.flashcard_set');
        var id = jQuery(this).attr('data-id');
        var current_id = jQuery(this).attr('data-current_id');
        var set_id = jQuery(this).attr('data-set_id');
        var fc_all = jQuery(this).attr('data-fc_all');
        var starred = 0;
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        if (id) {
            if (!obj.hide_loader)
                set.find('.full-screen').css('display', 'flex');
            jQuery.ajax({
                type: "POST",
                url: obj.ajax_url,
                data: {
                    'action': 'get_post_card',
                    'id': id,
                    'set_id': set_id,
                    'starred': starred,
                    'fc_all': fc_all,
                },
                success: function (data) {
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
//                    register_card();
                },
            });
        } else {
            set.find('.card-fc-item').hide();
            set.find('.card-note').show();
            set.find('.fc-prev').attr('data-id', current_id);
            set.find('.fc-prev').removeClass('click_disabled');
            register_card(set);
            jQuery(this).addClass('click_disabled');
        }
    });

    jQuery(document).on('click', '.fc-prev', function (e) {
        e.preventDefault();
        var next_arrow = jQuery(this);
        var set = next_arrow.closest('.flashcard_set');
        var id = jQuery(this).attr('data-id');
        var set_id = jQuery(this).attr('data-set_id');
        var fc_all = jQuery(this).attr('data-fc_all');
        var starred = 0;
        if (set.attr('action') == 'starred') {
            starred = 1;
        }

        if (id) {
            if (!obj.hide_loader)
                set.find('.full-screen').css('display', 'flex');
            jQuery.ajax({
                type: "POST",
                url: obj.ajax_url,
                data: {
                    'action': 'get_post_card',
                    'id': id,
                    'set_id': set_id,
                    'starred': starred,
                    'fc_all': fc_all,
                },
                success: function (data) {
                    if (data) {
                        set.find('.card-fc-item').fadeOut(500, function () {
                            set.html(data);
                            set.find('.flashcard_set-container').show(0, function () {
                                jQuery(this).fadeIn(300, function () {
                                    register_card(set);

                                });
                            });
                        });
                    }
                    if (!obj.hide_loader)
                        set.find('.full-screen').css('display', 'none');
                    //register_card();
                },
            });
        }
    });

    // re inintiate carasoul and flip
    function register_card(set) {
        jQuery('audio').each(function () {
            var audio = jQuery(this)[0];
            audio.pause();
        });

        if (set) {
            set.find('.fc-carousel').owlCarousel({
                loop: false,
                margin: 10,
                nav: false,
                items: 1,
                dots: false,
                onDragged: function (event) {
                    console.log(event.relatedTarget._drag.direction, jQuery(this));
                    if (event.relatedTarget._drag.direction == 'left') {
                        swipe_left(set);
                    } else if (event.relatedTarget._drag.direction == 'right') {
                        swipe_right(set);

                    }

                },
            });
            set.find(".fc-item:not(.card-note) .card-item").flip({
                axis: "x",
                trigger: "click",
                speed: '250',
                autoSize: false
            });
            //scrollbar
            set.find('.card-text').scrollbar();

        } else {
            jQuery('.fc-carousel').owlCarousel({
                loop: false,
                margin: 10,
                nav: false,
                items: 1,
                dots: false,
                onDragged: function (event) {
                    console.log(event.currentTarget.closest('.flashcard_set'), event.relatedTarget._drag, jQuery(this)[0].$element);
                    if (event.relatedTarget._drag.direction == 'left') {
                        swipe_left(jQuery(this)[0].$element);
                    } else if (event.relatedTarget._drag.direction == 'right') {
                        swipe_right(jQuery(this)[0].$element);

                    }

                },
            });
            jQuery(".fc-item:not(.card-note) .card-item").flip({
                axis: "x",
                trigger: "click",
                speed: '250',
                autoSize: false
            });
            //scrollbar
            jQuery('.flashcard_set .card-text').scrollbar();


        }
        // stop click 
        jQuery('.audio-container').click(function (e) {
            e.stopPropagation();
        });
        jQuery('.video-container').click(function (e) {
            e.stopPropagation();
        });

        jQuery('.audio-video-container').click(function (e) {
            e.stopPropagation();
        });

        jQuery(".fc-item:not(.card-note) .card-item").on('flip:done', function (e) {

            jQuery('audio').each(function () {
                var audio = jQuery(this)[0];
                audio.pause();
            });

            jQuery('video').each(function () {
                var audio = jQuery(this)[0];
                audio.pause();
            });

            if (jQuery(this).find(".front").css("z-index") == 0 || jQuery(this).find(".back").css("z-index") == 0) {
                if (jQuery(this).find(".front").css("z-index") == 0) {
                    var iframe_src = jQuery(this).find(".front iframe").attr('src');
                    jQuery(this).find(".front iframe").attr('src', iframe_src);
                } else if (jQuery(this).find(".back").css("z-index") == 0) {
                    var iframe_src = jQuery(this).find(".back iframe").attr('src');
                    jQuery(this).find(".back iframe").attr('src', iframe_src);
                }
            }
        });
    }


    //check for card width
    jQuery('.flashcard_set').each(function () {
        var width = jQuery(this).width();
        if (width <= 420) {
            jQuery(this).addClass('nav-new-line');
        }
    });

});

