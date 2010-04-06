/**
 * Handles adding tasks
 * Expects a global variable workbox_handler as a reference to itself
 * which is required for executing events in global scope where `this`
 * is lost.
 * @requires jQuery (tested with 1.4.[012])
 * @requires notification.js
 * @requires list.js
 * @requires profile.js
*/
function Workbox() {
    this.groups_auto = [];

    this.set_share_list = function () {
        // create list of users to share and move up current user
        var share_with = TEMPLATES.followers.clone(),
            current_user = share_with
                .find('input.u' + profile_handler
                    .CURRENT_USER.id).attr('checked', 'checked')
                .parent().parent()
                .prependTo(share_with);
        $('.share .input', TEMPLATES.workbox)
            .text(current_user.find('span').text());

        share_with
            .appendTo('.share', TEMPLATES.workbox);
        $('.share input', TEMPLATES.workbox).bind('click', manage_share);
    };

    this.get_all_groups = function () {
        // update autocomplete
        $.ajax({
            url: PATHS.groups,
            type: 'POST',
            async: true,
            cache: false,
            dataType: 'json',
            timeout: 3000,
            global: false,
            error: function(request, textStatus, error) {
                // fail silently
                return false;
            },
            success: function(data, textStatus, request) {
                delete workbox_handler.groups_auto;
                workbox_handler.groups_auto = [];

                if (data.results.length > 0) {
                    for (var i in data.results) {
                        workbox_handler.groups_auto.push(data
                            .results[i].name);
                    }
                }
                $('textarea', TEMPLATES.workbox)
                    .autocomplete(workbox_handler.groups_auto);
            }
        });
    };


    /**
     * Handles form submission. Creates task and refreshes one of the lists.
     */
    $('input[type="submit"]', TEMPLATES.workbox).click(function () {
        var form_data = $('form', TEMPLATES.workbox).serialize(),
            target = $(this);
        $.ajax({
            type: 'POST',
            url: TEMPLATES.workbox.find('form').attr('action'),
            data: form_data + '&' + target.attr('name') + '=1' +
                '&t=' + list_handler.type,
            beforeSend: function () {
                TEMPLATES.spinwheel.show();
                notif_handler.start();
            },
            error: function (response, text_status, error) {
                TEMPLATES.spinwheel.hide();
                if (target.attr('name') === 'add') {
                    notif_handler.add(2, 'Failed to add task');
                } else {
                    notif_handler.add(2, 'Failed to plan task');
                }
            },
            success: function (response) {
                list_handler.update_groups(response.groups);
                if (response.planned) {
                    list_handler.expect(1);
                    notif_handler.add(3, 'Task planned');
                } else {
                    list_handler.expect(0);
                    notif_handler.add(0);
                }
                list_handler.get_lists();
                TEMPLATES.spinwheel.hide();
            }
        });
        return false;
    });

    /**
     * Manages the sharing
     */
    function manage_share() {
        var s_obj = $('.share .input', TEMPLATES.workbox),
            s_text = s_obj.text(),
            the_input = $(this),
            new_text = the_input.next().html(),
            this_in_regex = new RegExp('([ ]|^)' + new_text + '([ ]|$)'),
            this_in = this_in_regex.exec(s_text);

        if (!the_input.is(':checked') &&
            the_input.parents('ul').find(':checked').length <= 0) {
            notif_handler.add(2);
            return false;
        }

        if (the_input.is(':checked')) {
            if (this_in === null) {
                s_obj.text(s_text + ' ' + new_text);
            }
        } else {
            s_obj.text(s_text.replace(this_in_regex, ' '));
        }
    }

    /**
    * Updates the priority on click
    */
    $('.priority .p', TEMPLATES.workbox).click(function () {
        if ($(this).hasClass('s')) {
            $(this).parents('.priority').find('input').val('3');
            $(this).removeClass('s');
        }
        else {
            $(this).parents('.priority').find('.p')
                .removeClass('s');
            $(this).parents('.priority').find('input')
                .val($(this).find('.img').attr('alt'));
            $(this)
                .addClass('s');
        }
        return false;
    });

    /**
     * Clears the workbox
     */
    $('.clear', TEMPLATES.workbox).click(function () {
        $('textarea', TEMPLATES.workbox)[0].value = '';
        $('input[name="due"]', TEMPLATES.workbox).val(WORKBOX.due);
        $('.share .input', TEMPLATES.workbox).html(profile_handler
            .CURRENT_USER.username);
        $('.share input', TEMPLATES.workbox).attr('checked', '');
        $('.share input', TEMPLATES.workbox).eq(0).attr('checked', 'checked');
        $('.priority input', TEMPLATES.workbox).val(WORKBOX.priority);
        $('.priority .p', TEMPLATES.workbox).removeClass('s');
        return false;
    });

    // init stuff
    TEMPLATES.spinwheel.appendTo(TEMPLATES.workbox).hide();
    TEMPLATES.workbox.prependTo('#content');
    this.get_all_groups();
}
