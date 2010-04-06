/**
 * Main driver for tasklist
 * Instantiates all objects and fires up the tasklist getter.
 * Each object appends its templates to the content when it is created.
 * @see constants.js for template and other initial setup
 */
var row_handler,
    list_handler,
    notif_handler,
    url_handler;

$(document).ready(function () {
    /**
     * Init
     */
    $('#content')
        .children()
            .remove()
            .end()
        .html('');

    notif_handler = new Notification();
    list_handler = new List();
    row_handler = new Row();
    url_handler = new Url();
    profile_handler = new Profile();
    workbox_handler = new Workbox();

    list_handler.set_params(url_handler.mainpage,
        url_handler.minipage,
        url_handler.group,
        url_handler.type
    );
    var hash_pos = window.location.href.indexOf('#');
    if (hash_pos === -1 || hash_pos + 1 == window.location.href.length) {
        list_handler.get_lists();
    }

});
