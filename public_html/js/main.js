var
/*-------------- CONSTANTS --------------*/
      TASK_TABLE_ROW_HEIGHT = 30
    , FOOTER_SPACE = 10
    , TASK_TABLE_MINUS = 100
    , INITIAL_URL = window.location.href
    , INITIAL_URL_NOHASH = INITIAL_URL.substr(0, window.location.href.indexOf('#'))
    , HASH_SEPARATOR = ';'
    , SAVE = {
        'priority': '/task/pri/',
        'status': '/task/s/',
        'delete': '/task/d/',
        'text': '/task/text/',
        'due': '/task/due/',
        'follower_add': '/task/share/',
        'follower_remove': '/task/share/',
    }
    , USERS_URL = '/user/l/'
    , GROUPS_URL = '/group/l/'
    , TASKLIST_URL = '/tasklist/t/1/?n='
    , TASKLIST_TIMEOUT = 5000
    , REFRESH_TIMEOUT = 120000
    , LOADING_ROW_CLASS = 'loadbar'
    , DEFAULT_NO_TASKS = $('<div class="notasks"> \
        No tasks are available. Create one using the box to the right. \
        </div>')
    , ROW_TEMPLATE = $('<div class="row "> \
        <div class="td s"> \
            <input type="checkbox" class="md sh" name="status" /> \
        </div> \
        <div class="td p"> </div> \
        <div class="td text"><span class="editable"></span></div> \
        <div class="td due"><span class="editable"></span></div> \
        <div class="td followers"></div> \
        <div class="td del"> \
            <a href="#" class="sh del-link"> </a> \
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
    ]
    , DEFAULT_TITLES = [
        '<a href="#p=1">' + DEFAULT_TITLES_PLAIN[0] + '</a>',
    ]
    , CURRENT_USER
/*-------------- VARIABLES --------------*/
    , hash_last = ''
    , tasks_per_page
    , tasklist_refresh_timeout = false
    , tasklist_timeout = {
        'main': true,
    }
    , t_editing = {
        'main': false,
    }
    , t_page = get_url_param('p', INITIAL_URL)
    , t_group = get_url_param('g', INITIAL_URL)
;

/*****************************************************************************/
/*
/* DISPATCHERS FOR PRIORITY, STATUS, DELETE
/*
/*****************************************************************************/

/**
 * Changing task priority
 */
$('.p', $('#main')).live('click', function() {
    update_row('priority', $('#main'), $(this));
});

/**
 * Changing task status
 */
$('.s input', $('#main')).live('click', function() {
    update_row('status', $('#main'), $(this));
});

/**
 * Delete task
 */
$('.del-link', $('#main')).live('click', function() {
    update_row('delete', $('#main'), $(this));
    return false;
});

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
        case 'delete':
            break;
        case 'text':
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
 * @param task_box jQuery object, the relevant box
 *          which contains the target
 * @param t_row jQuery object, the row edited
 * @param target jQuery object, the exact target
 * @param response JSON response
 * @param textStatus @see jQuery.ajax documentation
 * @param request @see jQuery.ajax documentation
 * @param t_data output of extract_data function
 */
function dispatch_response(type, task_box, t_row, target,
            response, textStatus, request, t_data) {
    if (request.status != 200) {
        dispatch_error(type, task_box, t_row, target,
            response, textStatus, request, t_data);
        return false;
    }
    if (type != 'text') {
        reset_timeout(task_box);
    }
    switch (type) {
        case 'priority':
            target.removeClass('pri_' + t_data.current)
                    .addClass('pri_' + response.priority);
            break;
        case 'status':
            if (response.status) {
                t_row.addClass('done');
            } else {
                t_row.removeClass('done');
            }
            break;
        case 'delete':
            t_row.fadeOut('slow', function() {
                /*$('.trash_tasks .tlist').prepend('<tr><td><a class="ed showhover" href="?e=' + t_id + '">e</a>\
                    <span class="hide"> \
                        <span class="remind">' + t_remind + '</span> \
                        <span class="due">' + t_due + '</span> \
                        <span class="user_id">' + t_user_id + '</span> \
                        <span class="priority">' + t_priority + '</span> \
                    </span> \
                </td><td>' + t_description + '</td></tr>');*/
                $(this).remove();
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
        case 'due':
            finish_edit(task_box, target, response[type]);
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
function dispatch_error(type, task_box, t_row, target,
    response, textStatus, request, t_data) {
    switch (type) {
        case 'priority':
            break;
        case 'status':
            if (!t_row.hasClass('done')) {
                t_row.removeClass('done');
                target.attr('checked', '');
            } else {
                t_row.addClass('done');
                target.attr('checked', 'checked');
            }
            break;
        case 'delete':
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
 * @param task_box = defaults to $('#main') -- which box to look in
 * @param target = the target of the event
 */
function update_row(type, task_box, target) {
    var t_row = target.parents('.row');
    clear_timeout(task_box);
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
            dispatch_error(type, task_box, t_row, target,
                response, textStatus, error, t_data);
            unset_loading_row(t_row);
            return false;
        },
        success: function(response, textStatus, request) {
            dispatch_response(type, task_box, t_row, target,
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
 * Pager updates the url hash
 */
$('.pager a').live('click', function() {
    var page = parseInt(get_url_param('p', $(this).attr('href')));
    url_update_hash('p', page);
    return false;
});

/**
 * Timeouts helpers
 */
function clear_timeout(elem) {
    var id = elem[0].id;
    if (tasklist_timeout[id]) {
        clearTimeout(tasklist_timeout[id]);
        clearTimeout(tasklist_refresh_timeout);
    }
}
function reset_timeout(elem) {
    clear_timeout(elem);
    var id = elem[0].id;
    tasklist_timeout[id] = setTimeout(function() {
        if (!t_editing[id]) {
            // TODO: make work with multiple tasklists
            get_tasklist();
        }
    }, TASKLIST_TIMEOUT);
    tasklist_refresh_timeout = setInterval(get_tasklist, REFRESH_TIMEOUT);
}
/* end timeouts helpers */

/**
 * Fetching tasklist
 */
function get_tasklist() {
    var   task_box = $('#main')
        , group = parseInt(get_url_param('g'));
    clear_timeout(task_box);

    $.ajax({
        type: 'GET',
        url: TASKLIST_URL + tasks_per_page + '&p=' + t_page + '&g=' + t_group,
        dataType: 'json',
        beforeSend: function() {
            return set_loading(task_box);
        },
        error: function (response, text_status, error) {
            unset_loading(task_box);
            if (response.status == 404) {
                DEFAULT_NO_TASKS.insertAfter($('.groups', task_box));
                return ;
            }

            return false;
        },
        success: function(response, textStatus, request) {
            if (request.status == 200) {
                // build tasklist from json
                update_groups(response.groups);
                // remove no tasks message if exists
                $('.notasks', task_box).remove();

                task_box.children('.task-table').html('');
                var task;
                for (var i in response.tasks) {
                    json_task = response.tasks[i]
                    html_task = ROW_TEMPLATE.clone();
                    if (json_task.status) {
                        html_task.children('.s').children('input')
                            .attr('checked', 'checked');
                        html_task.addClass('done');
                    }
                    html_task.children('.p')
                        .addClass('pri_' + json_task.priority);
                    if (json_task.group) {
                        TASK_GROUP_TEMPLATE.clone().attr('href', '#g='
                            + json_task.group.id)
                            .html(json_task.group.name)
                            .prependTo(html_task.children('.text')
                                .children('.editable')
                            );
                        json_task.text = ': ' + json_task.text;
                    }
                    html_task.children('.text').children('.editable')
                        .append(json_task.text);
                    html_task.children('.due').children('.editable')
                        .html(json_task.due);
                    var html_followers = FOLLOWERS_TEMPLATE.clone();
                    for (var i in json_task.followers) {
                        html_followers.find('input.u' +
                            json_task.followers[i].id).attr('checked', 'checked');
                    }
                    html_followers.appendTo(html_task.children('.followers'));
                    html_task.children('.del')
                        .children('input[name="task_id"]').val(json_task.id)
                        .end()
                        .children('input[name="user_id"]').val(json_task.user_id)
                    ;
                    html_task.appendTo(task_box.children('.task-table'));
                }
                pager = $('.pager', task_box)
                if (pager.length > 0) {
                    pager.remove();
                }
                pager = $(response.pager).appendTo(task_box);
            } else {
                task_error_response(response);
            }
            unset_loading(task_box);
        }
    });
}


/*****************************************************************************/
/*
/* EDITABLE FIELDS
/*
/*****************************************************************************/
$('.editable').live('click', replace_html);

/* enter/escape actions inside form */
$('form.inplace input').live('keydown', function(e) {
    if (e.keyCode == 13) {
        // enter pressed
        var type = $(this).parents('.td')
            .attr('class').substr(3);
        update_row(type, $('#main'), $(this));
        return false;
    }
    else if (e.keyCode == 27) {
        // esc pressed
        cancel_edit_task($(this));
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
    var task_box = ref.parents('.task-box');
    var old_text = get_old_text(ref);
    if (new_text && new_text != plain_text(old_text)) {
        return false;
    }
    finish_edit(task_box, ref, old_text);
    return true;
}

function finish_edit(task_box, ref, text) {
    editable = EDITABLE_BLANK.clone().html(text)
    ref.parents('.td')
        .html(editable);
    t_editing[task_box[0].id] = false;
}

function replace_html(event) {
    if (event.button !== undefined && event.button !== 0) return true;
    var id = $(this).parents('.task-box')[0].id;
    if (t_editing[id]) return;

    var buffer = $(this).html()
            .replace(/"/g, '&quot;')
        , rephtml = build_editable_html(buffer)
    ;

    $(this).parent()
        .html(rephtml)
        .unbind('click', replace_html)
        .find('input:first-child').focus();
    t_editing[id] = true;
}

function build_editable_html(buffer) {
    return '<form class="inplace"><input type="text" value="' + plain_text(buffer) + '" /><input type="hidden" name="buffer" value="' + buffer + '" /></form>';
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
$('.editable a').live('click', function (e) {
    if ($(this).hasClass('g')) {
        var id = $(this).attr('href').substr(3);
        url_update_hash('g', id, true);
        return false;
    } else if (!e.ctrlKey) {
        window.location.href = $(this).attr('href');
        return false;
    }
});

$('.title a', $('#main')).live('click', function () {
    url_update_hash('p', 1, true);
    return false;
});

function update_groups(groups) {
    var html_g, url_g;
    template = GROUPS_TEMPLATE.clone().html('');
    for (var i in groups) {
        url_g = '#g=' + groups[i].id;
        title_g = groups[i].name;
        if (t_group && t_group == groups[i].id) {
            $('.title', $('#main'))
                .html('<a href="#p=1">' + groups[i].name
                    + '</a>');
            ;
            url_g = '#p=1';
            title_g = DEFAULT_TITLES_PLAIN[0];
        } else if (!t_group) {
            $('.title', $('#main'))
                .html('<a href="#p=1">' + DEFAULT_TITLES_PLAIN[0]
                    + '</a>');
            ;
        }
        html_g = GROUP_TEMPLATE.clone();
        html_g.children('a')
            .attr('href', url_g)
            .html(title_g)
        ;
        template.append(html_g);
    }
    $('.groups ul', $('#main')).remove();
    template.appendTo($('.groups', $('#main')));
}


/*****************************************************************************/
/*
/* FOLLOWER MANAGEMENT
/*
/*****************************************************************************/
/**
 * Share with someone else
 */
$('.followers input', $('#main')).live('click', function() {
    if ($(this).is(':checked')) {
        update_row('follower_remove', $('#main'), $(this));
    } else {
        update_row('follower_add', $('#main'), $(this));
    }
});

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
            alert('Error getting users.')
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
    error_html += '<a href="#" class="jqmClose">Close</a>';
    $('.error_dialog').html(error_html);
    $('.error_trigger').click();
    $('.error_dialog').focus();
}

function task_error_response(response) {
    var response_arr = response.replace(/\\\"/g, '"').replace(/\\\'/g, "'").split('@#')
    var error_html = '<h2>Error: ' + response_arr[0] + '</h2>';
    if (response_arr[1]) error_html += '<pre>' + response_arr[1] + '</pre>';
    error_html += '<a href="#" class="jqmClose">Close</a>';
    $('.error_dialog').html(error_html);
    $('.error_trigger').click();
    $('.error_dialog').focus();
}

$('body').keydown(function(e) {
    if ((e.keyCode == 13 || e.keyCode == 27) &&
        $('.error_dialog').is(':visible')) {
        // esc or enter pressed
        $('.error_dialog').children('.jqmClose').click();
        return false;
    }
});
/* end error dialog events */

/**
 * Resizing the window causes tables to resize
 */
function resize() {
    var t_height = 0, reload = false;
    $('#main').height($(window).height() - FOOTER_SPACE);
    tasks_per_page = parseInt(
        ($('#main').height() - TASK_TABLE_MINUS)
        / TASK_TABLE_ROW_HEIGHT
    );
    $('.loading').each(function() {
        $(this).height($(this).parent().height() + 'px');
    });
    if ($('.task-table', $('#main')).height() != tasks_per_page * TASK_TABLE_ROW_HEIGHT) {
        $('.task-table', $('#main')).height(tasks_per_page * TASK_TABLE_ROW_HEIGHT);
        reload = true;
    }
    if (!hash_last) reload = true;
    if (reload) get_tasklist();
}
$(window).resize(resize);

$(document).ready(function() {
    /**
     * Init
     */
    get_users();

    $('#content').html(TEMPLATE_WORK_BOX + '\
    <div class="task-box" id="main"> \
        <div class="loading"></div> \
        <div class="groups"><h1 class="title"><a href="#p=1">my tasks</a>\
            </h1></div> \
        <div class="task-table" cellspacing="0"> \
        </div> \
        <!-- pager? --> \
    </div><!-- /.task-box -->');
    resize();
    tasklist_refresh_timeout = setInterval(get_tasklist, REFRESH_TIMEOUT);

    // Initialize history plugin.
    // The callback is called at once by present location.hash.
    $.historyInit(on_hash_change, INITIAL_URL);
    $('.error_dialog').jqm();
    init_workbox()
});
