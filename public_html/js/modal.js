/**
 * Handles notifications
 * Expects a global variable notif_handler as a reference to itself
 * which is required for executing events in global scope where `this`
 * is lost.
 * @requires jQuery (tested with 1.4.[012])
 */
function Modal() {
    // reference to list of notifications
    this.classes = [];

    // reference to last notification added
    this.last_item = null;

    // current count of notifications
    this.count = 0;

    // how many items are showing
    this.showing = 0;

    // (bool) whether the entire notification list is showing or not
    this.showing_list = 0;

    /**
     * Shows the modal window as a prompt
     * @param string text to populate it with
     * @param string cls class to add (will be removed on close)
     * @param string ev_type the event type to listen for on the input
     *     (usually keyup, ENTER)
     * @param reference func this is called when ev_type occurs
     */
    this.show_prompt = function (text, cls, ev_type, func) {
        var i;
        // clean up classes
        if (this.classes.length > 0) {
            for (i in this.classes) {
                TEMPLATES.modal.removeClass(this.classes[i]);
            }
            this.classes = [];
        }

        $('.text', TEMPLATES.modal).html(text);
        $('input', TEMPLATES.modal).bind(ev_type, func);
        TEMPLATES.modal.addClass(cls);
        this.classes.push(cls);
        TEMPLATES.modal_trigger.click();
        $('input', TEMPLATES.modal).focus();
    };

    this.help = function () {
        $('.text', TEMPLATES.modal).html(HELP);
        TEMPLATES.modal.find('.help a').click(function () {
            document.getElementById($(this).attr('href').substr(1)).scrollIntoView(true);
            return false;
        });
        TEMPLATES.modal.addClass('help');
        TEMPLATES.modal_trigger.click();
    }

    /**
     * Click anywhere on body or pressing esc hides modal dialog
     */
    $('body').keydown(function (e) {
        if ((e.keyCode === 27) &&
            TEMPLATES.modal.is(':visible')) {
            // esc pressed
            TEMPLATES.modal.children('.text').children().remove().end()
                .html('');
            TEMPLATES.modal.children('.jqmClose').click();
            return false;
        }
    });

    // init stuff
    TEMPLATES.modal.appendTo('#content');
    TEMPLATES.modal_trigger.appendTo('#content');
    TEMPLATES.modal.jqm();
    $('#content > .help_trigger').click(this.help);
}
