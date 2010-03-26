/**
 * Validates an email address
 */
function isValidEmail(in_test) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(in_test);
}

/**
 * Validates username
 */
function isValidUsername(in_test) {
    var trimmed = $.trim(in_test);
    if (trimmed !== in_test) {
        return false;
    }

    var pattern = new RegExp(/^([a-z]{3,50})$/i);
    return pattern.test(in_test);
}

/**
 * Returns a password strength score
 */
function passwordStrength(password) {
    var score = 0;

    //if password shorter than 6
    if (password.length < 6) return score;
    score++;

    //if password has both lower and uppercase characters
    if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;

    //if password has at least one number
    if (password.match(/\d+/)) score++;

    //if password has at least one special caracther
    if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) ) score++;

    //if password longer than 12
    if (password.length > 12) score++;

    return score;
}


$(document).ready(function() {
    var   in_username = $('input[name="username"]')
        , in_email = $('input[name="email"]')
        , in_password = $('input[name="password"]')
        , in_password_confirm = $('input[name="password_confirm"]')
        , password_indication = {
            'colors'   : ['#fff', '#aaa', '#666'],
            'text'     : ['Weak', 'Medium', 'Strong'],
            'strength' : [0, 0, 1, 1, 2, 2, 2],
        }
    ;
    /**
     * Init
     */
    $('#content .nojs').hide();
    $('#content .register').show();


    in_password.keyup(function () {
        var score = passwordStrength(in_password.val());
        if (score == 0) {
            in_password
                .removeClass()
                .addClass('invalid');
        } else {
            in_password
                .removeClass()
        }
        in_password.css('background', password_indication['colors'][
            password_indication['strength'][score]
        ]);
        in_password.prev().html(password_indication['text'][
            password_indication['strength'][score]
        ]);
    });

    in_password_confirm.keyup(function () {
        var pass = in_password_confirm.val()
        if (pass !== in_password.val()) {
            in_password_confirm
                .removeClass()
                .addClass('invalid');
        } else {
            in_password_confirm
                .removeClass();
        }
    });

    /**
     * Validates email
     */
    in_email.keyup(function () {
        if (!isValidEmail(in_email.val())) {
            in_email
                .removeClass()
                .addClass('invalid');
        } else {
            in_email
                .removeClass()
                .addClass('valid');
        }
    })

    /**
     * Validates username
     */
    in_username.keyup(function () {
        if (!isValidUsername(in_username.val())) {
            in_username
                .removeClass()
                .addClass('invalid');
        } else {
            in_username
                .removeClass()
                .addClass('valid');
        }
    })
    /**
     * Checks username is available
     */
    in_username.blur(function () {
        if (!in_username.hasClass('valid')) {
            return false;
        }
        $.ajax({
            type: 'POST',
            url: '/user/available',
            dataType: 'json',
            data: {'username': in_username.val()},
            error: function (response, text_status, error) {
                in_username
                    .removeClass()
                    .addClass('unavailable');
            },
            success: function(response, textStatus, request) {
                if (request.status != 200 ||
                    response.available != 1) {
                    // not available
                    in_username
                        .removeClass()
                        .addClass('unavailable');
                } else {
                    in_username
                        .removeClass()
                        .addClass('available');
                }
            }
        });
    });

});
