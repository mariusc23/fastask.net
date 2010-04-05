/*-------------- GLOBAL VARIABLES --------------*/
var
    row_handler,
    list_handler,
    notif_handler,
    url_handler
;

$(document).ready(function() {
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
    list_handler.resize();
});
