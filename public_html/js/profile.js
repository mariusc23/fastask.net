/*****************************************************************************/
/*
/* Profile JS
/*
/*****************************************************************************/
/**
 * Called from main.js at end of setup
 */
function init_profile() {

var
/*-------------- CONSTANTS --------------*/
      PROFILE_BOX = $('\
    <div class="profile-box"> \
    <div class="loading"></div> \
    <h1 class="title"></h1> \
    <form action="/user/update" method="POST"> \
        <label class="name">Name: <br /> \
            <input type="text" name="name" value="" /> \
        </label> \
        <label class="email">Email: <br/> \
            <input type="text" name="email" value="" /> \
        </label> \
        <label class="change_password"><span class="lstep">\
            Change Password:</span><br/> \
            <input type="password" name="change_password" value="" /> \
            <input type="hidden" name="current_password" value="" /> \
            <input type="hidden" name="password" value="" /> \
            <input type="hidden" name="password_confirm" value="" /> \
            <a class="submit">next</a> \
            <span class="info-box"> \
                <span class="info">Enter current password.</span> \
                <span class="steps"><span class="on" href="#">1</span> &rsaquo; \
                <span href="#">2</span> &rsaquo; <span href="#">3</span></span> \
            </span> \
        </label> \
        <span class="links"> \
            <a class="save" href="#">save</a> \
            <a href="/user/logout">log out</a> \
            <a class="cancel" href="#">cancel</a> \
        </span> \
    </form> \
    </div><!-- profile-box -->')
/*-------------- VARIABLES --------------*/
;

PROFILE_BOX.children('.title').prepend(CURRENT_USER.username);
PROFILE_BOX.find('input[name="name"]').val(CURRENT_USER.name);
PROFILE_BOX.find('input[name="email"]').val(CURRENT_USER.email);
PROFILE_BOX.appendTo('#content');
PROFILE_BOX.height(($(window).height() - 340) + 'px');
$('.loading', PROFILE_BOX).height($('.loading', PROFILE_BOX).parent().height() + 'px');
PROFILE_FORM = $('form', PROFILE_BOX);

$('.save', PROFILE_BOX).click(function() {
    var  form_data = PROFILE_FORM.serialize()
    ;
    $.ajax({
        type: 'POST',
        url: PROFILE_BOX.find('form').attr('action'),
        data: form_data,
        beforeSend: function() {
            set_loading(PROFILE_BOX);
        },
        error: function (response, text_status, error) {
            unset_loading(PROFILE_BOX);
            return task_error_ajax(response, text_status, error);
        },
        success: function(response) {
            $('input[name="change_password"]', PROFILE_BOX).val('');
            unset_loading(PROFILE_BOX);
        }
    });
    return false;
});

$('.submit', PROFILE_BOX).click(function () {
    var steps = PROFILE_FORM.find('.steps').children(),
        current_step = steps.index(steps.filter('.on'))
        change_password = PROFILE_FORM
                .find('input[name="change_password"]').val();
    if (current_step == 0) {
        $('input[name="current_password"]', PROFILE_FORM).val(change_password);
        $('.info', PROFILE_BOX).hide();
        $('.lstep', PROFILE_BOX).html('New password: ');
    } else if (current_step == 1) {
        $(this).val('save');
        $('input[name="password"]', PROFILE_FORM).val(change_password);
        $('.info', PROFILE_BOX).hide();
        $('.lstep', PROFILE_BOX).html('Confirm new password: ');
    } else if (current_step == 2) {
        $('input[name="password_confirm"]', PROFILE_FORM).val(change_password);
        $('.save', PROFILE_BOX).click();
        current_step = -1;
        $('.lstep', PROFILE_BOX).html('Change password: ');
        $('.info', PROFILE_BOX).show();
    }
    $('input[name="change_password"]', PROFILE_FORM).val('');
    steps
        .removeClass('on')
        .eq(current_step + 1).addClass('on');

    return false;
});

$('input[name="change_password"]', PROFILE_BOX).keyup(function (e) {
    if (e.keyCode == 13) {
        $('.submit', PROFILE_BOX).click();
        return false;
    }
});

} // end init_profile