/**
 * Main driver for fastask
 * Instantiates all objects and fires up the task list getter.
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

    modal_handler = new Modal();
    notif_handler = new Notification();
    list_handler = new List();
    row_handler = new Row();
    url_handler = new Url();
    workbox_handler = new Workbox();

    list_handler.set_params(url_handler.mainpage,
        url_handler.minipage,
        url_handler.group,
        url_handler.type
    );

    // this one fires off the calls
    profile_handler = new Profile();
});
