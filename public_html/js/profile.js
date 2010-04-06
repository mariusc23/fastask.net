/**
 * Handles profiles
 * Expects a global variable profile_handler as a reference to itself
 * which is required for executing events in global scope where `this`
 * is lost.
 * @requires jQuery (tested with 1.4.[012])
 * @requires notification.js
*/
function Profile() {
    // find the form
    this.PROFILE_FORM = $('form', TEMPLATES.profile);
    this.CURRENT_USER = {
        'id': 0,
        'username': 'fetching...',
        'email': ''
    };

    /**
     * Handles the profile save.
     */
    $('.save', TEMPLATES.profile).click(function () {
        var form_data = profile_handler.PROFILE_FORM.serialize();
        $.ajax({
            type: 'POST',
            url: TEMPLATES.profile.find('form').attr('action'),
            data: form_data,
            beforeSend: function () {
                $('.loading', TEMPLATES.profile).show();
                notif_handler.start();
            },
            error: function (response, text_status, error) {
                $('.loading', TEMPLATES.profile).hide();
                notif_handler.add(2, 'Could not update your profile');
            },
            success: function (response) {
                if ($('input[name="password_confirm"]').val().length > 0) {
                    notif_handler.add(4);
                } else {
                    notif_handler.add(4, 'Profile updated');
                }
                $('input[name="password"]', TEMPLATES.profile).val('');
                $('input[name="password_confirm"]', TEMPLATES.profile).val('');
                $('input[name="current_password"]', TEMPLATES.profile).val('');
                $('input[name="change_password"]', TEMPLATES.profile).val('');
                $('.loading', TEMPLATES.profile).hide();
            }
        });
        return false;
    });

    /**
     * Goes through the steps of changing password.
     */
    $('.submit', TEMPLATES.profile).click(function () {
        var steps = profile_handler.PROFILE_FORM.find('.steps').children(),
            current_step = steps.index(steps.filter('.on')),
            change_password = profile_handler.PROFILE_FORM
                    .find('input[name="change_password"]').val();
        if (current_step === 0) {
            $('input[name="current_password"]',
                profile_handler.PROFILE_FORM).val(change_password);
            $('.info', TEMPLATES.profile).hide();
            $('.lstep', TEMPLATES.profile).html('New password: ');
        } else if (current_step === 1) {
            $(this).val('save');
            $('input[name="password"]',
                profile_handler.PROFILE_FORM).val(change_password);
            $('.info', TEMPLATES.profile).hide();
            $('.lstep', TEMPLATES.profile).html('Confirm new password: ');
        } else if (current_step === 2) {
            $('input[name="password_confirm"]',
                profile_handler.PROFILE_FORM).val(change_password);
            $('.save', TEMPLATES.profile).click();
            current_step = -1;
            $('.lstep', TEMPLATES.profile).html('Change password: ');
            $('.info', TEMPLATES.profile).show();
        }
        $('input[name="change_password"]',
            profile_handler.PROFILE_FORM).val('');
        steps
            .removeClass('on')
            .eq(current_step + 1).addClass('on');
        return false;
    });

    // Need to return false for Chrome
    $('form', TEMPLATES.profile).submit(function () {
        return false;
    });

    /**
     * Shortcut for pressing enter --> calls Next>
     */
    $('input[name="change_password"]', TEMPLATES.profile).keyup(function (e) {
        if (e.keyCode === 13) {
            $('.submit', TEMPLATES.profile).click();
            return false;
        }
    });

    /**
     * Gets and builds the list of users in JSON
     */
    this.get_users = function () {
        $.ajax({
            type: 'GET',
            url: PATHS.users,
            dataType: 'json',
            error: function (response, text_status, error) {
                alert('Error getting users.');
                return false;
            },
            success: function (response, textStatus, request) {
                var html_f, i, CURRENT_USER;
                TEMPLATES.followers
                    .children()
                        .remove()
                        .end()
                    .html('');
                for (i in response.users) {
                    if (response.users[i].current) {
                        CURRENT_USER = response.users[i];
                    }
                    html_f = TEMPLATES.follower.clone();
                    html_f.find('input')
                        .val(response.users[i].id)
                        .attr('class', 'u' + response.users[i].id)
                    ;
                    html_f.find('span').html(response.users[i].username);
                    TEMPLATES.followers.append(html_f);
                }
                TEMPLATES.profile.children('.title')
                    .prepend(CURRENT_USER.username);
                TEMPLATES.profile.find('input[name="name"]')
                    .val(CURRENT_USER.name);
                TEMPLATES.profile.find('input[name="email"]')
                    .val(CURRENT_USER.email);

                // update current user and workbox
                profile_handler.CURRENT_USER = CURRENT_USER;
                workbox_handler.set_share_list();
            }
        });
    };

    // init stuff
    this.get_users();
    TEMPLATES.profile.appendTo('#content');
}
