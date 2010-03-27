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
    <form action="/task/add" method="POST"> \
        <label class="name">Name: <br /> \
            <input type="text" name="name" value="" /> \
        </label> \
        <label class="email">Email: <br/> \
            <input type="text" name="email" value="" /> \
        </label> \
        <label class="change_password">Change Password: <br/> \
            <input type="password" name="change_password" value="" /> \
            <input type="submit" name="next" value="next" /> \
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

} // end init_profile