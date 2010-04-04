var
/*-------------- CONSTANTS --------------*/
      SEARCH_TIMEOUT = 500
    , TASKLIST_TIMEOUT = 5000
    , REFRESH_TIMEOUT = 120000
    , RESIZE_TIMEOUT = 1000
    , TASK_TABLE_ROW_HEIGHT = 30
    , TASK_TABLE_MINUS = 100
    , PL_TASK_TABLE_MINUS = 100
    , ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT = 20
    , TEXT_INDENT_ADJUSTMENT = 5
    , INITIAL_URL = window.location.href
    , INITIAL_URL_NOHASH = INITIAL_URL.substr(0, window.location.href.indexOf('#'))
    , HASH_SEPARATOR = ';'
    , USERS_URL = '/user/l/'
    , SEARCH_MINLENGTH = 5
    , DEFAULT_NO_TASKS = $('<div class="notasks"> \
        No tasks are available. Create one using the box to the right. \
        </div>')
    , FOLLOWERS_TEMPLATE = $('<ul></ul>')
    , FOLLOWER_TEMPLATE = $('<li><label><input class="" type="checkbox" \
            name="follower[]" value=""/> <span></span></label></li>')
    , TASK_GROUP_TEMPLATE = $('<a href="#" class="g"></a>')
    , GROUPS_TEMPLATE = $('<ul></ul>')
    , GROUP_TEMPLATE = $('<li><a href="#"></a></li>')
    , EDITABLE_BLANK = $('<span class="editable"></span>')
    , DEFAULT_TITLES_PLAIN = [
        'my tasks',
        'assignments',
        'command',
        'archive',
        'search'
    ]
    , DEFAULT_TITLES = [
        '<a href="#t=0">' + DEFAULT_TITLES_PLAIN[0] + '</a>',
        '<a href="#t=1">' + DEFAULT_TITLES_PLAIN[1] + '</a>',
        '<a href="#t=2">' + DEFAULT_TITLES_PLAIN[2] + '</a>',
        '<a href="#t=3">' + DEFAULT_TITLES_PLAIN[3] + '</a>',
        '<a href="#s=1">' + DEFAULT_TITLES_PLAIN[4] + '</a>',
    ]
    , CURRENT_USER
/*-------------- VARIABLES --------------*/
    , hash_last = ''
    , tasks_per_page
    , pl_tasks_per_page
    , search_timeout = false
    , tasklist_refresh_timeout = false
    , resize_timeout = false
    , tasklist_timeout
    , t_editing = {
        'main': false,
        'mini': false,
    }
    , t_page = get_url_param('p', INITIAL_URL)
    , t_pl_page = get_url_param('u', INITIAL_URL)
    , t_tr_page = get_url_param('v', INITIAL_URL)
    , search_page = 1
    , t_group = get_url_param('g', INITIAL_URL)
    , t_type = get_url_param('t', INITIAL_URL)
    , last_search_q = ''
    , row_handler
    , list_handler
;

/*****************************************************************************/
/*
/* DISPATCHERS FOR PRIORITY, STATUS, DELETE
/*
/*****************************************************************************/

function RowHandler(task_box, mini_box, TGT) {
    // constants
    this.LISTS = [task_box, mini_box];
    this.SAVE = {
        'priority': '/task/pri/',
        'status': '/task/s/',
        'delete': '/task/d/',
        'undelete': '/task/d/',
        'plan': '/task/plan/',
        'text': '/task/text/',
        'due': '/task/due/',
        'follower_add': '/task/share/',
        'follower_remove': '/task/share/',
    };
    this.LOADING_ROW_CLASS = 'loadbar';
    this.TASK_GROUP_TEMPLATE = TGT;

    var row_handler = this;

    // change
    this.box_num = null;
    this.target = null;
    this.t_row = null;
    this.task_id = null;
    this.data = null;
    this.response = null;
    this.request = null;
    this.error = null;
    this.textStatus = null;

    /**
     * Extracts data from a row
     */
    this.extract_data = function() {
        this.task_id = this.t_row.find('input[name="task_id"]')[0].value;
        this.data = {
            'url': this.SAVE[this.type] + this.task_id,
            'method': 'GET', 'send': {}
        };
        switch (this.type) {
            case 'priority':
                current = parseInt(this.target.attr('class')
                    .charAt(this.target.attr('class').indexOf('pri_') + 4));
                this.data.current = current;
                this.data.next = (current + 1) % 3
                break;
            case 'status':
                break;
            case 'undelete':
                this.data.send.undo = 1;
            case 'delete':
                break;
            case 'plan':
                if (plan_custom) {
                    this.data.send.due = plan_custom;
                    this.data.method = 'POST';
                    plan_custom = false;
                }
                break;
            case 'text':
                this.data.url += '&t=' + t_type;
            case 'due':
                var new_text = this.target.val()
                    .replace(/\\\"/g, '"').replace(/\\\'/g, "'");
                if (cancel_edit_task(this.target, new_text)) {
                    return false;
                }
                this.data.method = 'POST';
                this.data.send = {};
                this.data.send[this.type] = new_text;
                break;
            case 'follower_add':
                this.data.method = 'POST';
                this.data.send = {'u': this.target.val()};
                break;
            case 'follower_remove':
                this.data.method = 'POST';
                this.data.send = {
                    'u': this.target.val(), 'r': 1
                };
                break;
            default:
                return false;
        }
        return true;
    };

    /**
     * Handles the JSON response for a row
     * @param type shortly, which column was edited
     * @param t_row jQuery object, the row edited
     * @param target jQuery object, the exact target
     * @param response JSON response
     * @param textStatus @see jQuery.ajax documentation
     * @param request @see jQuery.ajax documentation
     * @param t_data output of extract_data function
     */
    this.dispatch_response = function() {
        if (this.request.status != 200) {
            this.dispatch_error();
            return false;
        }
        if (this.type != 'text') {
            list_handler.reset_timeout(this.box_num);
        }
        switch (this.type) {
            case 'priority':
                this.target.removeClass('pri_' + this.data.current)
                        .addClass('pri_' + this.response.priority);
                break;
            case 'status':
                if (this.response.status) {
                    if (t_type != 3) {
                        this.t_row.addClass('done');
                    }
                } else {
                    this.t_row.removeClass('done');
                }
                break;
            case 'undelete':
                this.t_row.fadeOut('slow', function() {
                    $(this).remove();
                    if (!row_handler.response.task.planned) {
                        list_handler.reset_timeout(0);
                    }
                });
                break;
            case 'delete':
                this.t_row.fadeOut('slow', function() {
                    $(this).remove();
                });
                break;
            case 'plan':
                if (this.response.planned) {
                    break;
                }
                this.t_row.fadeOut('slow', function() {
                    $(this).remove();
                    list_handler.clear_timeout();
                    list_handler.expect(0);
                    list_handler.get_tasklist();
                });
                break;
            case 'text':
                if (this.response.group) {
                    var group = $('<div/>').html(
                        this.TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
                            + this.response.group.id)
                            .html(this.response.group.name)
                        );
                    this.response.text = group.html() + ': ' + this.response.text;
                }
                update_groups(this.response.groups);
                finish_edit(this.target, this.response.text);
                break;
            case 'due':
                if (this.response.planned) {
                    list_handler.reset_timeout(1);
                }
                finish_edit(this.target, this.response.due_out);
                break;
            case 'follower_add':
            case 'follower_remove':
                break;
        }
        return true;
    };

    /**
     * Handles errors
     */
    this.dispatch_error = function() {
        switch (this.type) {
            case 'priority':
                break;
            case 'status':
                if (!this.t_row.hasClass('done')) {
                    this.t_row.removeClass('done');
                    target.attr('checked', '');
                } else {
                    if (t_type != 3) {
                        this.t_row.addClass('done');
                    }
                    this.target.attr('checked', 'checked');
                }
                break;
            case 'delete':
            case 'plan':
                break;
            case 'text':
            case 'due':
                break;
            case 'follower_add':
            case 'follower_remove':
                if (this.target.is(':checked')) {
                    this.target.attr('checked', '');
                } else {
                    this.target.attr('checked', 'checked');
                }
                break;
        }
        return true;
    };

    /**
     * Updating row
     * @param type = update type, one of 'priority', 'status'
     * @param target = the target of the event
     */
    this.update_row = function(type, target) {
        this.type = type;
        this.target = target;
        this.t_row = target.parents('.row');
        this.box_num = target.parents('.box').attr('rel');
        list_handler.clear_timeout(this.LISTS[this.box_num]);
        if (this.is_loading_row(this.t_row)) {
            return false;
        }
        if (!this.extract_data()) {
            return false;
        }
        // need this inside ajax, scope
        $.ajax({
            type: this.data.method,
            url: this.data.url,
            data: this.data.send,
            dataType: 'json',
            beforeSend: function() {
                row_handler.set_loading_row();
            },
            error: function (request, textStatus, error) {
                row_handler.request = request;
                row_handler.textStatus = textStatus;
                row_handler.error = error;
                row_handler.response = null;
                row_handler.dispatch_error();
                row_handler.unset_loading_row();
                return false;
            },
            success: function(response, textStatus, request) {
                row_handler.response = response;
                row_handler.textStatus = textStatus;
                row_handler.request = request;
                row_handler.error = null;
                row_handler.dispatch_response();
                row_handler.unset_loading_row();
            }
        });
    };

    /**
     * Row loading helpers
     */
    this.is_loading_row = function() {
        if (this.t_row.hasClass(this.LOADING_ROW_CLASS)) {
            return true;
        }
        return false;
    };
    this.set_loading_row = function() {
        this.t_row.addClass(this.LOADING_ROW_CLASS);
    };
    this.unset_loading_row = function() {
        this.t_row.removeClass(this.LOADING_ROW_CLASS);
    };
    /* end row loading helpers */

    /*****************************************************************************/
    /*
    /* EDITABLE FIELDS
    /*
    /*****************************************************************************/
    /* enter/escape actions inside form */
    $('form.inplace input', this.LISTS[0]).live('keydown', function(e) {
        if (e.keyCode == 13) {
            // enter pressed
            var type = $(this).parents('.td')
                .attr('class').substr(3);
            if (type.indexOf(' ') >= 0) {
                type = type.substr(0, type.indexOf(' '));
            }
            row_handler.update_row(type, $(this));
            return false;
        }
        else if (e.keyCode == 27) {
            // esc pressed
            $(this).focusout();
            return false;
        }
    });
    $('form.inplace input', this.LISTS[0]).live('focusout', function () {
        cancel_edit_task($(this));
    });
    /* navigation in task-table */
    $('form.inplace input', this.LISTS[0]).live('keydown', function(e) {
        var   move_ref = []
            , t_row = $(this).parents('.row')
            , t_d = $(this).parents('.td')
            , form_index = 0
        ;
        // move up
        if (e.keyCode == 40) {
            var move_ref = $(this).parents('.row').next();
            if (move_ref.length == 0) {
                move_ref = $(this).parents('.row').parent().children().eq(0);
            }
            if ($(this).parents('.td').hasClass('due')) {
                form_index = 1;
            }
        // move down
        } else if (e.keyCode == 38) {
            var move_ref = $(this).parents('.row').prev();
            if (move_ref.length == 0) {
                move_ref = $(this).parents('.row').parent().children().last();
            }
            if ($(this).parents('.td').hasClass('due')) {
                form_index = 1;
            }
        // move right
        } else if (e.altKey && e.ctrlKey && e.keyCode == 39) {
            var move_ref = $(this).parents('.row');
        // move left
        } else if (e.altKey && e.ctrlKey && e.keyCode == 37) {
            var move_ref = $(this).parents('.row');
        } else {
            return true;
        }
        if (move_ref.length > 0) {
            move_ref = move_ref.find('.editable').eq(form_index).parent();
            cancel_edit_task($(this));
            r = move_ref.find('.editable');
            r.click();
        }
        return false;
    });

    function get_old_text(ref) {
        return ref.parents('.row').find('input[name="buffer"]').val().replace(/\\\"/g, '"').replace(/\\\'/g, "'");
    }

    function cancel_edit_task(ref, new_text) {
        var old_text = get_old_text(ref);
        if (new_text && new_text != plain_text(old_text)) {
            return false;
        }
        finish_edit(ref, old_text);
        return true;
    }

    function finish_edit(ref, text) {
        editable = EDITABLE_BLANK.clone().html(text);
        editable.width(ref.next().attr('rel') + 'px');
        if (t_type == 1 && ref.parents('.td').children('.g').length > 0) {
            editable.css('text-indent',
                (ref.parents('.td').children('.g').width()) + 'px');
        }
        editable.bind('click', row_handler.replace_html);
        ref.parents('.td')
            .children('form.inplace')
                .remove()
                .end()
            .append(editable);
        t_editing[row_handler.LISTS[0][0].id] = false;
    }

    this.replace_html = function(event) {
        if (event.button !== undefined && event.button !== 0) return true;
        var id = $(this).parents('.task-box')[0].id;
        if (t_editing[id]) return;

        var buffer = $(this).html()
                .replace(/"/g, '&quot;')
            , rephtml = $(build_editable_html(buffer, $(this).width()))
        ;
        var the_parent = $(this).parent();
        if (t_type == 1) {
            rephtml.find('input[type="text"]').width(
                the_parent.width() - $(this).prev().width()
                - ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT
            );
        }
        $(this).remove();
        the_parent
            .append(rephtml)
            .unbind('click', row_handler.replace_html)
            .find('input:first-child').focus();
        t_editing[id] = true;
    }

    function build_editable_html(buffer, preserved_width) {
        return '<form class="inplace"><input type="text" value="' + plain_text(buffer) + '" /><input type="hidden" name="buffer" value="' + buffer + '" rel="' + preserved_width + '"/></form>';
    }

    function plain_text(text) {
        return text.replace(/(<([^>]+)>)/ig,"");
    }
    /* end of code for editable fields */
}


function ListHandler(row_handler,
    TT, REFT, REST, ST,
    AEWA, TIA, TTRH, TTM, PTTM, FT) {
    this.LISTS = row_handler.LISTS;
    this.LIST_URL = '/tasklist/t/?';

    this.TASKLIST_TIMEOUT = TT;
    this.REFRESH_TIMEOUT = REFT;
    this.RESIZE_TIMEOUT = REST;
    this.SEARCH_TIMEOUT = ST;
    this.ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT = AEWA;
    this.TEXT_INDENT_ADJUSTMENT = TIA;
    this.TASK_TABLE_ROW_HEIGHT = TTRH;
    this.TASK_TABLE_MINUS = TTM;
    this.PL_TASK_TABLE_MINUS = PTTM;
    this.FOLLOWERS_TEMPLATE = FT;

    this.ROW_TEMPLATE = $('<div class="row"> \
        <div class="td s"> \
            <input type="checkbox" class="md sh" name="status" /> \
        </div> \
        <div class="td p"><a href="#" title="change priority"></a></div> \
        <div class="td text"><span class="editable"></span></div> \
        <div class="td due"><span class="editable"></span></div> \
        <div class="td del"> \
            <a href="#"> </a> \
            <input type="hidden" value="" name="task_id"> \
            <input type="hidden" value="" name="user_id"> \
        </div> \
        <div class="td followers"><a href="#" title="sharing"></a></div> \
        </div>');
    this.ROW_TEMPLATE_MIN = $('<div class="row"> \
        <div class="td text"></div> \
        <div class="td"> \
            <a href="#"> </a> \
        </div> \
        <div class="td del"> \
            <a href="#"> </a> \
            <input type="hidden" value="" name="task_id"> \
            <input type="hidden" value="" name="user_id"> \
        </div> \
        </div>');
    this.TASK_GROUP_TEMPLATE = row_handler.TASK_GROUP_TEMPLATE;

    // change
    this.expecting = [1, 1];
    this.expect_what = 1;

    /**
     * Timeouts helpers
     */
    this.clear_timeout = function() {
        if (tasklist_timeout) {
            clearTimeout(tasklist_timeout);
            clearTimeout(tasklist_refresh_timeout);
        }
    };
    this.reset_timeout = function(li) {
        this.clear_timeout();
        this.expect(li);
        tasklist_timeout = setTimeout(function() {
            list_handler.get_tasklist();
        }, this.TASKLIST_TIMEOUT);
        tasklist_refresh_timeout = setInterval(this.refresh_all, this.REFRESH_TIMEOUT);
    };
    this.refresh_all = function() {
        for (var i in list_handler.expecting) {
            list_handler.expect(i);
        }
        list_handler.get_tasklist();
    };
    /* end timeouts helpers */

    /**
     * Task loading helpers
     */
    this.set_loading = function(li) {
        if (!t_editing[this.LISTS[li][0].id]) {
            this.LISTS[li].children('.loading').show();
            return true;
        }
        return false;
    };

    this.unset_loading = function(li) {
        this.LISTS[li].children('.loading').hide();
    };
    /* end task loading helpers */

    this.expect = function(li) {
        this.expecting[li] = 1;
    };

    this.unexpect = function(li) {
        this.expecting[li] = 0;
    };

    this.plan = function(val) {
        plan_custom = val;
    }

    /**
     * Fetching tasklist
     */
    this.get_tasklist = function() {
        // reset search
        last_search_q = '';
        if (this.expecting[0]) {
            this.clear_timeout();
        }
        $.ajax({
            type: 'GET',
            url: this.LIST_URL + 'g=' + t_group
                + '&t=' + t_type + '&p=' + t_page
                + '&u=' + t_pl_page + '&tr=' + this.expect_what
                + '&ep=' + this.expecting[0] + '&n=' + tasks_per_page
                + '&eu=' + this.expecting[1] + '&m=' + pl_tasks_per_page
            ,
            dataType: 'json',
            beforeSend: function() {
                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i]
                        && !list_handler.set_loading(i)) {
                        return false;
                    }
                }
            },
            error: function (request, textStatus, error) {
                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i]) {
                        list_handler.unset_loading(i);
                    }
                }
                if (list_handler.expecting[0]
                    && list_handler.request.status == 404) {
                    $('.title', list_handler.LISTS[0])
                        .html(list_handler.DEFAULT_TITLES[t_type]);
                    ;
                    list_handler.DEFAULT_NO_TASKS.insertBefore(
                        $('.task-table', list_handler.LISTS[0]));
                    $('.task-table', list_handler.LISTS[0]).html('');
                    $('.tabs .icon', list_handler.LISTS[0])
                        .removeClass('active')
                        .eq(t_type).addClass('active')
                    ;
                }
            },
            success: function(response, textStatus, request) {
                //if (request.status == 200) {
                list_handler.response = response;
                list_handler.textStatus = textStatus;
                list_handler.request = request;
                list_handler.build_tasklist();
                //} else {
                    //task_error_response(response);
                //}

                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i]) {
                        list_handler.unset_loading(i);
                        // done, expecting nothing now
                        list_handler.expecting[i] = 0;
                    }
                }
                $('.tabs .icon', list_handler.LISTS[0])
                    .removeClass('active')
                    .eq(t_type).addClass('active')
                ;
                $('.tabs .icon', list_handler.LISTS[1])
                    .removeClass('active')
                    .eq(list_handler.expect_what - 1).addClass('active')
                ;
            }
        });
    };

    this.build_tasklist = function() {
        // build tasklist from json
        update_groups(this.response.groups);
        // remove no tasks message if exists

        for (var i in this.expecting) {
            if (this.expecting[i]) {
                if (i == 0) {
                    // also remove message
                    $('.notasks', this.LISTS[i]).remove();
                }
                $('.table', this.LISTS[i]).html('');
            }
        }

        for (var i in this.response.tasks) {
            if (this.response.tasks[i].planned
                || this.response.tasks[i].trash) {
                html_task = this.build_task_json_min(i);
                if (this.response.tasks[i].trash) {
                    html_task.appendTo(this.LISTS[1].children('.table'));
                    html_task.find('.del').addClass('undo');
                } else {
                    html_task.children().eq(1).addClass('plan')
                        .bind('click', handle_plan_action);
                    html_task.appendTo(this.LISTS[1].children('.table'));
                }
            } else {
                html_task = this.build_task_json(i);
                html_task.appendTo(this.LISTS[0].children('.table'));
                if (t_type == 1 && this.response.tasks[i].group) {
                    html_text = html_task.children('.text');
                    html_text.find('.editable').width(
                        html_text.width() - html_text.find('.g').width()
                        - this.ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT
                    );
                    html_text.children('.editable').css(
                        'text-indent', (html_text.children('.g').width()
                            + this.TEXT_INDENT_ADJUSTMENT) + 'px');
                }
            }
        }

        if (this.expecting[0]) {
            pager = $('.pager', this.LISTS[0]);
            if (pager.length > 0) {
                pager.remove();
            }
            pager = $(this.response.pager).appendTo(this.LISTS[0]);
            pager.children('a').bind('click', handle_pager_main);

            // update small numbers for the tabs
            for (var k in this.response.counts) {
                $('.tabs .c', this.LISTS[0]).eq(k).html(this.response.counts[k]);
            }
        }
        if (this.expecting[1]) {
            pager = $('.pager', this.LISTS[1]);
            if (pager.length > 0) {
                pager.remove();
            }
            pager = $(this.response.pl_pager).appendTo(this.LISTS[1]);
            pager.children('a').bind('click', handle_pager_mini);

            for (var k in this.response.counts_left) {
                $('.tabs .c', this.LISTS[1]).eq(k).html(this.response.counts_left[k]);
            }
        }
    }

    this.build_task_json_min = function(i) {
        var task, task_group, html_text;
        json_task = this.response.tasks[i];
        html_task = this.ROW_TEMPLATE_MIN.clone();
        if (json_task.status) {
            html_task.addClass('done');
        }
        html_text = html_task.children('.text');
        if (json_task.group) {
            task_group = this.TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
                + json_task.group.id)
                .html(json_task.group.name);
            // for ASSIGNMENTS, not allowed to change group
            task_group
                .prependTo(html_text);
            json_task.text = ': ' + json_task.text;
        }
        html_text
            .append(json_task.text);
        html_task.children('.del')
            .children('input[name="task_id"]').val(json_task.id)
            .end()
            .children('input[name="user_id"]').val(json_task.user_id)
        ;
        if (json_task.trash) {
            html_task.children('.del')
                .bind('click', handle_undelete);
        } else {
            html_task.children('.del')
                .bind('click', handle_delete);
        }
        return html_task;
    }

    this.build_task_json = function(i) {
        var task, task_group, html_text;
        json_task = this.response.tasks[i];
        html_task = this.ROW_TEMPLATE.clone();
        if (json_task.status) {
            html_task.children('.s').children('input')
                .attr('checked', 'checked')
            if (t_type != 3) {
                html_task.addClass('done');
            }
        }
        html_task.children('.s').children('input')
            .bind('click', handle_status);
        html_task.children('.p')
            .addClass('pri_' + json_task.priority)
            .bind('click', handle_priority);
        html_text = html_task.children('.text');
        if (json_task.group) {
            task_group = this.TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
                + json_task.group.id)
                .html(json_task.group.name);
            // for ASSIGNMENTS, not allowed to change group
            if (t_type == 1) {
                task_group.html(task_group.html() + ': ')
                    .prependTo(html_text);
                html_text.addClass('nogroup');
            } else {
                task_group
                    .prependTo(html_task.children('.text')
                        .children('.editable')
                    );
                json_task.text = ': ' + json_task.text;
            }
        }
        html_text.children('.editable')
            .append(json_task.text)
            .bind('click', row_handler.replace_html);
        html_text.find('a')
            .bind('click', handle_editable_click);

        html_task.children('.due').children('.editable')
            .html(json_task.due_out)
            .bind('click', row_handler.replace_html);
        var html_followers = this.FOLLOWERS_TEMPLATE.clone();
        for (var i in json_task.followers) {
            html_followers.find('input.u' +
                json_task.followers[i].id).attr('checked', 'checked');
        }
        html_followers.appendTo(html_task.children('.followers'))
            .find('input').bind('click', handle_follow_action);
        html_task.children('.del')
            .children('input[name="task_id"]').val(json_task.id)
            .end()
            .children('input[name="user_id"]').val(json_task.user_id)
            .end()
            .children('a').bind('click', handle_delete);
        ;
        return html_task;
    }

    /*****************************************************************************/
    /*
    /* SEARCHING
    /*
    /*****************************************************************************/
    this.do_search = function(search_val) {
        if (undefined == search_val ||
            search_val.length <= 0) {
            return;
        }
        $.ajax({
            type: 'GET',
            url: TASKLIST_URL + 'n=' + tasks_per_page
                + '&p=' + search_page + '&s=' + search_val
            ,
            dataType: 'json',
            beforeSend: function() {
                $('.search .search-s').show();
                return this.set_loading(0);
            },
            error: function (response, text_status, error) {
                last_search_q = search_val;
                this.unset_loading(0);
                $('.search .search-s').hide();
                if (response.status == 404) {
                    $('.task-table', $('#main')).html('');
                    DEFAULT_NO_TASKS.insertBefore($('.task-table', TASK_BOX));
                    return ;
                }
                return false;
            },
            success: function(response, textStatus, request) {
                last_search_q = search_val;
                if (request.status == 200) {
                    $('.task-table', $('#main')).html('');
                    list_handler.expect(0);
                    list_handler.unexpect(1);
                    list_handler.build_tasklist(response, textStatus, request);
                    list_handler.unexpect(0);
                    var   url_g = '#t=' + t_group
                        , title_g = DEFAULT_TITLES_PLAIN[4];
                    ;
                    $('.title', $('#main'))
                        .html('<a href="' + url_g + '">'
                            + title_g
                            + '</a>');
                    ;
                } else {
                    task_error_response(response);
                }
                $('.search .search-s').show();
                this.unset_loading(0);
            }
        });
    }

    function handle_search_action(e) {
        search_page = 1;
        clearTimeout(search_timeout);
        var search_val = $(this).val();
        if (search_val.length < SEARCH_MINLENGTH) {
            if (search_val.length <= 0) {
                last_search_q = '';
            }
            return true;
        }
        if (last_search_q == search_val) {
            return true;
        }
        list_handler.clear_timeout();

        search_timeout = setTimeout(function() {
            do_search(search_val)
        }, SEARCH_TIMEOUT);
    }

    /**
     * Task Box Pager updates the url hash
     * Parameter: p
     */
    handle_pager_main = function(e) {
        var page = parseInt(get_url_param('p', $(this).attr('href')));
        if (last_search_q.length > 0) {
            search_page = page;
            do_search(last_search_q);
            last_search_q = search_val;
        } else {
            url_update_hash('p', page);
        }
        return false;
    }

    /**
    * Mini Box Pager updates the url hash
    * Parameter: u, v
    */
    handle_pager_mini = function(e) {
        var page = parseInt(get_url_param('u', $(this).attr('href')));
        url_update_hash('u', page);
        return false;
    }

    /**
    * Changing task priority
    */
    handle_priority = function(e) {
        row_handler.update_row('priority', $(this));
        return false;
    }

    /**
    * Changing task status
    */
    handle_status = function(e) {
        row_handler.update_row('status', $(this));
    }

    /**
    * Delete task
    * #main, #mini
    */
    handle_delete = function(e) {
        row_handler.update_row('delete', $(this));
        return false;
    }

    handle_undelete = function(e) {
        row_handler.update_row('undelete', $(this));
        return false;
    }

    handle_editable_click = function(e) {
        if ($(this).hasClass('g')) {
            var id = $(this).attr('href').substr(3);
            url_update_hash('g', id, true);
            return false;
        } else if (!e.ctrlKey) {
            window.location.href = $(this).attr('href');
            return false;
        }
    }

    /**
     * Share with someone else
     */
    handle_follow_action = function(e) {
        if ($(this).is(':checked')) {
            row_handler.update_row('follower_remove', $(this));
        } else {
            row_handler.update_row('follower_add', $(this));
        }
    }

    $('input[name="search"]', this.LISTS[0]).bind('keyup', handle_search_action);
    /**
    * Changing tabs
    */
    $('.tabs .icon', this.LISTS[0]).click(function () {
        var type = parseInt(get_url_param('t', $(this).children('a').attr('href')));
        url_update_hash('t', type);
        return false;
    });

    $('.tabs .icon', this.LISTS[1]).click(function () {
        var type = parseInt(get_url_param('l', $(this).children('a').attr('href')));
        list_handler.expect(1);
        list_handler.expect_what = type;
        list_handler.get_tasklist();
        return false;
    });

    /**
     * Resizing the window causes tables to resize
     */
    this.resize = function() {
        var t_height = 0, reload = false;
        tasks_per_page = parseInt(
            ((list_handler.LISTS[0].height() - list_handler.TASK_TABLE_MINUS
            ) / list_handler.TASK_TABLE_ROW_HEIGHT)
        );
        pl_tasks_per_page = parseInt(
            ((list_handler.LISTS[1].height() - list_handler.PL_TASK_TABLE_MINUS
            ) / list_handler.TASK_TABLE_ROW_HEIGHT)
        );
        $('.loading').each(function() {
            $(this).height($(this).parent().height() + 'px');
        });

        list_handler.unexpect(0);
        if ($('.task-table', list_handler.LISTS[0]).height() != tasks_per_page
                * list_handler.TASK_TABLE_ROW_HEIGHT) {
            $('.task-table', list_handler.LISTS[0]).height(tasks_per_page
                * list_handler.TASK_TABLE_ROW_HEIGHT);
            reload = true;
            list_handler.expect(0);
        }

        list_handler.unexpect(1);
        if ($('.table', list_handler.LISTS[1]).height() !=
            pl_tasks_per_page * list_handler.TASK_TABLE_ROW_HEIGHT) {
            $('.table', list_handler.LISTS[1])
                .height(pl_tasks_per_page * list_handler.TASK_TABLE_ROW_HEIGHT);
            reload = true;
            list_handler.expect(1);
        }

        if (!hash_last) reload = true;
        if (reload) {
            list_handler.get_tasklist();
        }
    }

    $(window).resize(function() {
        clearTimeout(list_handler.resize_timeout);
        list_handler.resize_timeout = setTimeout(list_handler.resize,
            list_handler.RESIZE_TIMEOUT);
    });
}


/*****************************************************************************/
/*
/* GROUP MANAGEMENT
/*
/*****************************************************************************/
$('.title a', $('#main')).live('click', function () {
    url_update_hash('p', 1, true);
    return false;
});

function update_groups(groups) {
    if (!t_group) {
        $('.title', $('#main'))
            .html(DEFAULT_TITLES[t_type]);
        ;
    }
    $('.groups ul', $('#main')).remove();
    if (groups.length <= 0) {
        return;
    }
    var html_g, url_g;
    template = GROUPS_TEMPLATE.clone().html('');
    for (var i in groups) {
        url_g = '#g=' + groups[i].id;
        title_g = groups[i].name
            + ' (' + groups[i].num_tasks + ')';
        if (t_group && t_group == groups[i].id) {
            url_g = '#t=' + t_group;
            $('.title', $('#main'))
                .html('<a href="' + url_g + '">'
                    + title_g
                    + '</a>');
            ;
            title_g = DEFAULT_TITLES_PLAIN[t_type];
        }
        html_g = GROUP_TEMPLATE.clone();
        html_g.children('a')
            .attr('href', url_g)
            .html(title_g)
        ;
        template.append(html_g);
    }
    template.appendTo($('.groups', $('#main')));
    $('.groups a').bind('click', handle_change_group);
}

/**
 * Groups update the url hash
 */
function handle_change_group(e) {
    var group = parseInt(get_url_param('g', $(this).attr('href')));
    url_update_hash('g', group, true);
    return false;
}



/*****************************************************************************/
/*
/* FOLLOWER MANAGEMENT
/*
/*****************************************************************************/
/**
 * Gets and builds the list of users in JSON
 */
function get_users() {
    $.ajax({
        type: 'GET',
        url: USERS_URL,
        dataType: 'json',
        error: function (response, text_status, error) {
            console.log('Error getting users.');
            return false;
        },
        success: function(response, textStatus, request) {
            var html_f;
            FOLLOWERS_TEMPLATE.html('');
            for (var i in response.users) {
                if (response.users[i].current) {
                    CURRENT_USER = response.users[i];
                }
                html_f = FOLLOWER_TEMPLATE.clone();
                html_f.find('input')
                    .val(response.users[i].id)
                    .attr('class', 'u' + response.users[i].id)
                ;
                html_f.find('span').html(response.users[i].username);
                FOLLOWERS_TEMPLATE.append(html_f);
            }
            init_continue();
        }
    });
}

/*****************************************************************************/
/*
/* MINI BOX
/*
/*****************************************************************************/
function handle_plan_action(e) {
    var target = $(this);
    if (e.shiftKey) {
        $('.modal_dialog .text').html('Type date and press ENTER or TAB: \
            <input type="text" name="plan_custom" />');
        $('.modal_dialog input').bind('keyup', function (e) {
            // enter or tab
            if (e.keyCode == 13 || e.keyCode == 9) {
                list_handler.plan($(this).val());
                $('.modal_dialog').children('.jqmClose').click();
                row_handler.update_row('plan', target);
            }
        });
        $('.modal_dialog input').focus();
        $('.modal_trigger').click();
        return false;
    }
    row_handler.update_row('plan', target);
    return false;
}


/*****************************************************************************/
/*
/* ERROR HANDLING
/*
/*****************************************************************************/
function task_error_ajax(response, text_status, error) {
    var error_html = '';
    if (response && response.status == 404) {
        error_html += response.responseText;
    }
        error_html = error + '<br/>' + text_status + '<br/>' +
            response + '<br/>' + error;
    error_html += '<a href="#" class="jqmClose">x</a>';
    $('.error_dialog').html(error_html);
    $('.error_trigger').click();
    $('.error_dialog').focus();
}

function task_error_response(response) {
    var response_arr = response.replace(/\\\"/g, '"').replace(/\\\'/g, "'").split('@#')
    var error_html = '<h2>Error: ' + response_arr[0] + '</h2>';
    if (response_arr[1]) error_html += '<pre>' + response_arr[1] + '</pre>';
    error_html += '<a href="#" class="jqmClose">x</a>';
    $('.error_dialog').html(error_html);
    $('.error_trigger').click();
    $('.error_dialog').focus();
}

$('body').keydown(function(e) {
    if ((e.keyCode == 27) &&
        $('.modal_dialog').is(':visible')) {
        // esc pressed
        $('.modal_dialog').children('.text').html('');
        $('.modal_dialog').children('.jqmClose').click();
        return false;
    }
});
/* end error dialog events */

$(document).ready(function() {
    /**
     * Init
     */
    get_users();
});
function init_continue() {
    $('#content').html('\
    <a href="#" class="modal_trigger jqModal hide">Show error</a> \
    <div class="jqmWindow modal_dialog"> \
        <a href="#" class="jqmClose">x</a> \
        <div class="text"></div> \
    </div> \
    <div class="task-box box" rel="0" id="main"> \
        <div class="loading"></div> \
        <div class="tabs"> \
            <div class="icon my-tasks" title="my and only my tasks"> \
                <a href="#t=0"></a><span class="c"></span></div> \
            <div class="icon assignments" title="assignments from others"> \
                <a href="#t=1"></a><span class="c"></span></div> \
            <div class="icon command" title="command center"> \
                <a href="#t=2"></a><span class="c"></span></div> \
            <div class="icon archive" title="archive"> \
                <a href="#t=3"></a><span class="c"></span></div> \
        </div> \
        <div class="groups"><h1 class="title"><a href="#t=0">my tasks</a>\
            </h1></div> \
        <div class="search"> \
            <input type="text" name="search" value="" title="Search as you type" /> \
            <span class="search-s info" style="display: none">Searching...</span> \
            <div class="icon" title="search"><a href="#s=1"></a></div> \
        </div> \
        <div class="task-table table" cellspacing="0"> \
        </div> \
        <!-- pager? --> \
    </div><!-- /.task-box -->');
    //tasklist_refresh_timeout = setInterval(refresh_all, REFRESH_TIMEOUT);

    // Initialize history plugin.
    // The callback is called at once by present location.hash.
    $.historyInit(on_hash_change, INITIAL_URL);
    $('.modal_dialog').jqm();
    init_profile();
    init_minibox();
    init_workbox();

    row_handler = new RowHandler($('#main'), $('#mini'),
        TASK_GROUP_TEMPLATE
    );
    list_handler = new ListHandler(row_handler,
        TASKLIST_TIMEOUT,
        REFRESH_TIMEOUT,
        RESIZE_TIMEOUT,
        SEARCH_TIMEOUT,
        ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT,
        TEXT_INDENT_ADJUSTMENT,
        TASK_TABLE_ROW_HEIGHT,
        TASK_TABLE_MINUS,
        PL_TASK_TABLE_MINUS,
        FOLLOWERS_TEMPLATE
    );

    list_handler.resize();
}
