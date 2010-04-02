var
/*-------------- CONSTANTS --------------*/
      TASK_TABLE_ROW_HEIGHT = 30
    , TASK_TABLE_MINUS = 100
    , PL_TASK_TABLE_MINUS = 75
    , ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT = 20
    , INITIAL_URL = window.location.href
    , INITIAL_URL_NOHASH = INITIAL_URL.substr(0, window.location.href.indexOf('#'))
    , HASH_SEPARATOR = ';'
    , SAVE = {
        'priority': '/task/pri/',
        'status': '/task/s/',
        'delete': '/task/d/',
        'undelete': '/task/d/',
        'plan': '/task/plan/',
        'text': '/task/text/',
        'due': '/task/due/',
        'follower_add': '/task/share/',
        'follower_remove': '/task/share/',
    }
    , USERS_URL = '/user/l/'
    , TASKLIST_URL = '/tasklist/t/?'
    , SEARCH_MINLENGTH = 5
    , SEARCH_TIMEOUT = 500
    , TASKLIST_TIMEOUT = 5000
    , REFRESH_TIMEOUT = 120000
    , RESIZE_TIMEOUT = 1000
    , LOADING_ROW_CLASS = 'loadbar'
    , DEFAULT_NO_TASKS = $('<div class="notasks"> \
        No tasks are available. Create one using the box to the right. \
        </div>')
    , ROW_TEMPLATE = $('<div class="row"> \
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
        </div>')
    , ROW_TEMPLATE_MIN = $('<div class="row"> \
        <div class="td text"></div> \
        <div class="td"> \
            <a href="#"> </a> \
        </div> \
        <div class="td del"> \
            <a href="#"> </a> \
            <input type="hidden" value="" name="task_id"> \
            <input type="hidden" value="" name="user_id"> \
        </div> \
        </div>')
    , FOLLOWERS_TEMPLATE = $('<ul></ul>')
    , FOLLOWER_TEMPLATE = $('<li><label><input class="" type="checkbox" \
            name="follower[]" value=""/> <span></span></label></li>')
    , GROUPS_TEMPLATE = $('<ul></ul>')
    , GROUP_TEMPLATE = $('<li><a href="#"></a></li>')
    , TASK_GROUP_TEMPLATE = $('<a href="#" class="g"></a>')
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
        'plan': false,
        'trash': false,
    }
    , t_page = get_url_param('p', INITIAL_URL)
    , t_pl_page = get_url_param('u', INITIAL_URL)
    , t_tr_page = get_url_param('v', INITIAL_URL)
    , search_page = 1
    , t_group = get_url_param('g', INITIAL_URL)
    , t_type = get_url_param('t', INITIAL_URL)
    , last_search_q = ''
    , expecting = {
        'main': 1,
        'plan': 1,
        'trash': 1,
    }
    , plan_custom = 0
;

/*****************************************************************************/
/*
/* DISPATCHERS FOR PRIORITY, STATUS, DELETE
/*
/*****************************************************************************/

/**
 * Extracts data from a row
 */
function extract_data(type, t_row, target) {
    var   task_id = t_row.find('input[name="task_id"]')[0].value
        , data = {'url': SAVE[type] + task_id,
                'method': 'GET', 'send': {}}
    ;
    switch (type) {
        case 'priority':
            current = parseInt(target.attr('class')
                .charAt(target.attr('class').indexOf('pri_') + 4));
            data.current = current;
            data.next = (current + 1) % 3
            break;
        case 'status':
        case 'undelete':
            data.send.undo = 1;
        case 'delete':
            break;
        case 'plan':
            if (plan_custom) {
                data.send.due = plan_custom;
                data.method = 'POST';
                delete plan_custom;
            }
            break;
        case 'text':
            data.url += '&t=' + t_type;
        case 'due':
            var new_text = target.val()
                .replace(/\\\"/g, '"').replace(/\\\'/g, "'");
            if (cancel_edit_task(target, new_text)) {
                return false;
            }
            data.method = 'POST';
            data.send = {};
            data.send[type] = new_text;
            break;
        case 'follower_add':
            data.method = 'POST';
            data.send = {'u': target.val()};
            break;
        case 'follower_remove':
            data.method = 'POST';
            data.send = {
                'u': target.val(), 'r': 1
            };
            break;
        default:
            return false;
    }
    return data;
}

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
function dispatch_response(type, t_row, target,
            response, textStatus, request, t_data) {
    if (request.status != 200) {
        dispatch_error(type, t_row, target,
            response, textStatus, request, t_data);
        return false;
    }
    if (type != 'text') {
        reset_timeout(t_row.parents('.box'));
    }
    switch (type) {
        case 'priority':
            target.removeClass('pri_' + t_data.current)
                    .addClass('pri_' + response.priority);
            break;
        case 'status':
            if (response.status) {
                if (t_type != 3) {
                    t_row.addClass('done');
                }
            } else {
                t_row.removeClass('done');
            }
            break;
        case 'undelete':
            t_row.fadeOut('slow', function() {
                $(this).remove();
                if (response.task.planned) {
                    reset_timeout($('#plan'));
                } else {
                    reset_timeout(TASK_BOX);
                }
            });
            break;
        case 'delete':
            t_row.fadeOut('slow', function() {
                $(this).remove();
                var html_task = build_task_json_min(response.task);
                html_task.prependTo($('#trash').children('.trash-table'));
                reset_timeout($('#trash'));
            });
            break;
        case 'plan':
            if (response.planned) {
                break;
            }
            t_row.fadeOut('slow', function() {
                $(this).remove();
                reset_timeout(TASK_BOX);
            });
            break;
        case 'text':
            if (response.group) {
                var group = $('<div/>').html(
                    TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
                        + response.group.id)
                        .html(response.group.name)
                    );
                response.text = group.html() + ': ' + response.text;
            }
            update_groups(response.groups);
            finish_edit(TASK_BOX, target, response.text);
            break;
        case 'due':
            if (response.planned) {
                reset_timeout($('#plan'));
            }
            finish_edit(TASK_BOX, target, response.due_out);
            break;
        case 'follower_add':
        case 'follower_remove':
            break;
    }
    return true;
}

/**
 * Handles errors
 */
function dispatch_error(type, t_row, target,
    response, textStatus, request, t_data) {
    switch (type) {
        case 'priority':
            break;
        case 'status':
            if (!t_row.hasClass('done')) {
                t_row.removeClass('done');
                target.attr('checked', '');
            } else {
                if (t_type != 3) {
                    t_row.addClass('done');
                }
                target.attr('checked', 'checked');
            }
            break;
        case 'delete':
        case 'plan':
        case 'trash':
            break;
        case 'text':
        case 'due':
            break;
        case 'follower_add':
        case 'follower_remove':
            if (target.is(':checked')) {
                target.attr('checked', '');
            } else {
                target.attr('checked', 'checked');
            }
            break;
    }
    return true;
}

/**
 * Updating row
 * @param type = update type, one of 'priority', 'status'
 * @param target = the target of the event
 */
function update_row(type, target) {
    var t_row = target.parents('.row');
    clear_timeout(TASK_BOX);
    if (is_loading_row(t_row)) {
        return false;
    }
    t_data = extract_data(type, t_row, target);
    if (!t_data) {
        return false;
    }
    $.ajax({
        type: t_data.method,
        url: t_data.url,
        data: t_data.send,
        dataType: 'json',
        beforeSend: function() {
            set_loading_row(t_row);
        },
        error: function (response, textStatus, error) {
            dispatch_error(type, t_row, target,
                response, textStatus, error, t_data);
            unset_loading_row(t_row);
            return false;
        },
        success: function(response, textStatus, request) {
            dispatch_response(type, t_row, target,
                response, textStatus, request, t_data);
            unset_loading_row(t_row);
        }
    });
}

/**
 * Row loading helpers
 */
function set_loading_row(elem) {
    elem.addClass(LOADING_ROW_CLASS);
}
function is_loading_row(elem) {
    if (elem.hasClass(LOADING_ROW_CLASS)) {
        return true;
    }
    return false;
}
function unset_loading_row(elem) {
    elem.removeClass(LOADING_ROW_CLASS);
}
/* end row loading helpers */

/**
 * Task loading helpers
 */
function set_loading(elem) {
    if (!t_editing[elem[0].id]) {
        elem.children('.loading').show();
        return true;
    }
    return false;
}
function unset_loading(elem) {
    elem.children('.loading').hide();
}
/* end task loading helpers */


/**
 * Timeouts helpers
 */
function clear_timeout(elem) {
    var id = elem[0].id;
    if (tasklist_timeout) {
        clearTimeout(tasklist_timeout);
        clearTimeout(tasklist_refresh_timeout);
    }
}
function reset_timeout(elem) {
    clear_timeout(elem);
    var id = elem[0].id;
    expecting[id] = 1;
    tasklist_timeout = setTimeout(function() {
        get_tasklist();
    }, TASKLIST_TIMEOUT);
    clearTimeout(tasklist_refresh_timeout);
    tasklist_refresh_timeout = setInterval(refresh_all, REFRESH_TIMEOUT);
}
function refresh_all() {
    expecting.main = 1;
    expecting.plan = 1;
    expecting.trash = 1;
    get_tasklist();
}
/* end timeouts helpers */

/**
 * Fetching tasklist
 */
function get_tasklist() {
    // reset search
    last_search_q = '';
    if (expecting.main) {
        clear_timeout(TASK_BOX);
    }

    $.ajax({
        type: 'GET',
        url: TASKLIST_URL + 'g=' + t_group
            + '&t=' + t_type + '&p=' + t_page
            + '&u=' + t_pl_page + '&v=' + t_tr_page
            + '&ep=' + expecting.main + '&n=' + tasks_per_page
            + '&eu=' + expecting.plan + '&m=' + pl_tasks_per_page
            + '&ev=' + expecting.trash + '&o=' + pl_tasks_per_page
        ,
        dataType: 'json',
        beforeSend: function() {
            var cont;
            if (expecting.main) {
                cont = set_loading(TASK_BOX);
            }
            if (expecting.plan) {
                cont = set_loading($('#plan'));
            }
            if (expecting.trash) {
                cont = set_loading($('#trash'));
            }
            return cont;
        },
        error: function (response, text_status, error) {
            if (expecting.main) {
                unset_loading(TASK_BOX);
            }
            if (expecting.plan) {
                unset_loading($('#plan'));
            }
            if (expecting.trash) {
                unset_loading($('#trash'));
            }
            if (response.status == 404) {
                $('.title', $('#main'))
                    .html(DEFAULT_TITLES[t_type]);
                ;
                DEFAULT_NO_TASKS.insertBefore($('.task-table', TASK_BOX));
                $('.task-table', TASK_BOX).html('');
                $('.tabs .icon', $('#main'))
                    .removeClass('active')
                    .eq(t_type).addClass('active')
                ;
                return ;
            }
            return false;
        },
        success: function(response, textStatus, request) {
            if (request.status == 200) {
                build_tasklist(response, textStatus, request);
            } else {
                task_error_response(response);
            }
            if (expecting.main) {
                unset_loading(TASK_BOX);
            }
            if (expecting.plan) {
                unset_loading($('#plan'));
            }
            if (expecting.trash) {
                unset_loading($('#trash'));
            }
            // done, expecting nothing now
            expecting.main = 0;
            expecting.plan = 0;
            expecting.trash = 0;
        }
    });
}

function build_tasklist(response, textStatus, request) {
    // build tasklist from json
    update_groups(response.groups);
    // remove no tasks message if exists

    if (expecting.plan) {
        $('.planner-table', $('#plan')).html('');
    }
    if (expecting.trash) {
        $('.trash-table', $('#trash')).html('');
    }
    if (expecting.main) {
        $('.notasks', TASK_BOX).remove();
        $('.task-table', TASK_BOX).html('');
    }
    for (var i in response.tasks) {
        if (response.tasks[i].planned || response.tasks[i].trash) {
            html_task = build_task_json_min(response.tasks[i]);
            if (response.tasks[i].trash) {
                html_task.appendTo($('#trash').children('.trash-table'));
            } else {
                html_task.children().eq(1).addClass('plan')
                    .bind('click', handle_plan_action);
                html_task.appendTo($('#plan').children('.planner-table'));
            }
        } else {
            html_task = build_task_json(response.tasks[i]);
            html_task.appendTo(TASK_BOX.children('.task-table'));
            if (t_type == 1 && response.tasks[i].group) {
                html_text = html_task.children('.text');
                html_text.find('.editable').width(
                    html_text.width() - html_text.find('.g').width()
                    - ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT
                );
                html_text.children('.editable').css(
                    'text-indent', (html_text.children('.g').width() + 5)
                        + 'px');
            }
        }
    }
    if (expecting.main) {
        pager = $('.pager', TASK_BOX);
        if (pager.length > 0) {
            pager.remove();
        }
        pager = $(response.pager).appendTo(TASK_BOX);
        pager.children('a').bind('click', handle_pager_main);

        // update small numbers for the tabs
        for (var k in response.counts) {
            $('.tabs .c').eq(k).html(response.counts[k]);
        }
    }
    if (expecting.plan) {
        pager = $('.pager', $('#plan'));
        if (pager.length > 0) {
            pager.remove();
        }
        pager = $(response.pl_pager).appendTo($('#plan'));
        pager.children('a').bind('click', handle_pager_plan);
    }
    if (expecting.trash) {
        pager = $('.pager', $('#trash'));
        if (pager.length > 0) {
            pager.remove();
        }
        pager = $(response.tr_pager).appendTo($('#trash'));
        pager.children('a').bind('click', handle_pager_trash);
    }
}


/**
 * Task Box Pager updates the url hash
 * Parameter: p
 */
function handle_pager_main(e) {
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
 * Planner/Trash Pager updates the url hash
 * Parameter: u
 */
function handle_pager_plan(e) {
    var page = parseInt(get_url_param('u', $(this).attr('href')));
    url_update_hash('u', page);
    return false;
}

function handle_pager_trash(e) {
    var page = parseInt(get_url_param('v', $(this).attr('href')));
    url_update_hash('v', page);
    return false;
}


function build_task_json_min(json_task) {
    var task, task_group, html_text;
    html_task = ROW_TEMPLATE_MIN.clone();
    if (json_task.status) {
        html_task.addClass('done');
    }
    html_text = html_task.children('.text');
    if (json_task.group) {
        task_group = TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
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

function build_task_json(json_task) {
    var task, task_group, html_text;
    html_task = ROW_TEMPLATE.clone();
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
        task_group = TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
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
        .bind('click', replace_html);
    html_text.find('a')
        .bind('click', handle_editable_click);

    html_task.children('.due').children('.editable')
        .html(json_task.due_out)
        .bind('click', replace_html);
    var html_followers = FOLLOWERS_TEMPLATE.clone();
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

/**
 * Changing task priority
 */
function handle_priority(e) {
    update_row('priority', $(this));
    return false;
}

/**
 * Changing task status
 */
function handle_status(e) {
    update_row('status', $(this));
}

/**
 * Delete task
 * #main, #plan, #trash
 */
function handle_delete(e) {
    update_row('delete', $(this));
    return false;
}

function handle_undelete(e) {
    update_row('undelete', $(this));
    return false;
}


/*****************************************************************************/
/*
/* EDITABLE FIELDS
/*
/*****************************************************************************/
/* enter/escape actions inside form */
$('form.inplace input').live('keydown', function(e) {
    if (e.keyCode == 13) {
        // enter pressed
        var type = $(this).parents('.td')
            .attr('class').substr(3);
        if (type.indexOf(' ') >= 0) {
            type = type.substr(0, type.indexOf(' '));
        }
        update_row(type, $(this));
        return false;
    }
    else if (e.keyCode == 27) {
        // esc pressed
        $(this).focusout();
        return false;
    }
});
$('form.inplace input').live('focusout', function () {
    cancel_edit_task($(this));
});
/* navigation in task-table */
$('form.inplace input').live('keydown', function(e) {
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
    finish_edit(TASK_BOX, ref, old_text);
    return true;
}

function finish_edit(TASK_BOX, ref, text) {
    editable = EDITABLE_BLANK.clone().html(text);
    editable.width(ref.next().attr('rel') + 'px');
    if (t_type == 1 && ref.parents('.td').children('.g').length > 0) {
        editable.css('text-indent',
            (ref.parents('.td').children('.g').width()) + 'px');
    }
    editable.bind('click', replace_html);
    ref.parents('.td')
        .children('form.inplace')
            .remove()
            .end()
        .append(editable);
    t_editing[TASK_BOX[0].id] = false;
}

function replace_html(event) {
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
        .unbind('click', replace_html)
        .find('input:first-child').focus();
    t_editing[id] = true;
}

function build_editable_html(buffer, preserved_width) {
    return '<form class="inplace"><input type="text" value="' + plain_text(buffer) + '" /><input type="hidden" name="buffer" value="' + buffer + '" rel="' + preserved_width + '"/></form>';
}

function plain_text(text) {
    return text.replace(/(<([^>]+)>)/ig,"");
}
/* end of code for editable tasks */


/*****************************************************************************/
/*
/* GROUP MANAGEMENT
/*
/*****************************************************************************/
function handle_editable_click(e) {
    if ($(this).hasClass('g')) {
        var id = $(this).attr('href').substr(3);
        url_update_hash('g', id, true);
        return false;
    } else if (!e.ctrlKey) {
        window.location.href = $(this).attr('href');
        return false;
    }
}

$('.title a', $('#main')).live('click', function () {
    url_update_hash('p', 1, true);
    return false;
});

function update_groups(groups) {
    $('.tabs .icon', $('#main'))
        .removeClass('active')
        .eq(t_type).addClass('active')
    ;
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
 * Share with someone else
 */
function handle_follow_action(e) {
    if ($(this).is(':checked')) {
        update_row('follower_remove', $(this));
    } else {
        update_row('follower_add', $(this));
    }
}

/**
 * Gets and builds the list of users in JSON
 */
function get_users() {
    $.ajax({
        type: 'GET',
        url: USERS_URL,
        dataType: 'json',
        async: false,
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
        }
    });
}

/*****************************************************************************/
/*
/* SEARCHING
/*
/*****************************************************************************/
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
    clear_timeout(TASK_BOX);

    search_timeout = setTimeout(function() {
        do_search(search_val)
    }, SEARCH_TIMEOUT);
}

function do_search(search_val) {
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
            return set_loading(TASK_BOX);
        },
        error: function (response, text_status, error) {
            last_search_q = search_val;
            unset_loading(TASK_BOX);
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
                expecting.main = 1;
                expecting.plan = 0;
                expecting.trash = 0;
                build_tasklist(response, textStatus, request);
                expecting.main = 0;
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
            unset_loading(TASK_BOX);
        }
    });
}

/*****************************************************************************/
/*
/* PLANNER
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
                plan_custom = $(this).val();
                $('.modal_dialog').children('.jqmClose').click();
                update_row('plan', target);
            }
        });
        $('.modal_dialog input').focus();
        $('.modal_trigger').click();
        return false;
    }
    update_row('plan', target);
    return false;
}

/*****************************************************************************/
/*
/* TRASH
/*
/*****************************************************************************/


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

/**
 * Resizing the window causes tables to resize
 */
function resize() {
    var t_height = 0, reload = false;
    tasks_per_page = parseInt(
        (($('#main').height() - TASK_TABLE_MINUS) / TASK_TABLE_ROW_HEIGHT)
    );
    pl_tasks_per_page = parseInt(
        (($('#plan').height() - PL_TASK_TABLE_MINUS) / TASK_TABLE_ROW_HEIGHT)
    );
    $('.loading').each(function() {
        $(this).height($(this).parent().height() + 'px');
    });
    expecting.main = 0;
    if ($('.task-table', $('#main')).height() != tasks_per_page * TASK_TABLE_ROW_HEIGHT) {
        $('.task-table', $('#main')).height(tasks_per_page * TASK_TABLE_ROW_HEIGHT);
        reload = true;
        expecting.main = 1;
    }
    expecting.plan = 0;
    if ($('.planner-table', $('#plan')).height() != pl_tasks_per_page * TASK_TABLE_ROW_HEIGHT) {
        $('.planner-table', $('#plan')).height(pl_tasks_per_page * TASK_TABLE_ROW_HEIGHT);
        $('.trash-table', $('#trash')).height(pl_tasks_per_page * TASK_TABLE_ROW_HEIGHT);
        reload = true;
        expecting.plan = 1;
        expecting.trash = 1;
    }
    if (!hash_last) reload = true;

    if (reload) {
        get_tasklist();
    }
}
$(window).resize(function() {
    clearTimeout(resize_timeout);
    resize_timeout = setTimeout(resize, RESIZE_TIMEOUT);
});

$(document).ready(function() {
    /**
     * Init
     */
    get_users();
    $('#content').html('\
    <a href="#" class="modal_trigger jqModal hide">Show error</a> \
    <div class="jqmWindow modal_dialog"> \
        <a href="#" class="jqmClose">x</a> \
        <div class="text"></div> \
    </div> \
    <div class="task-box box" id="main"> \
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
        <div class="task-table" cellspacing="0"> \
        </div> \
        <!-- pager? --> \
    </div><!-- /.task-box -->');
    TASK_BOX = $('#main');
    $('input[name="search"]').bind('keyup', handle_search_action);
    /**
    * Changing tabs
    */
    $('.tabs .icon', $('#main')).click(function () {
        var type = parseInt(get_url_param('t', $(this).children('a').attr('href')));
        url_update_hash('t', type);
        return false;
    });
    tasklist_refresh_timeout = setInterval(refresh_all, REFRESH_TIMEOUT);

    // Initialize history plugin.
    // The callback is called at once by present location.hash.
    $.historyInit(on_hash_change, INITIAL_URL);
    $('.modal_dialog').jqm();
    init_profile();
    init_planner();
    init_workbox();

    resize();
});
