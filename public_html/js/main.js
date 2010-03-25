$(document).ready(function() {
    var
    /*-------------- CONSTANTS --------------*/
        TASK_TABLE_ROW_HEIGHT = 30
      , FOOTER_SPACE = 70
      , TASK_TABLE_MINUS = 100
      , INITIAL_URL = window.location.href
      , INITIAL_URL_NOHASH = INITIAL_URL.substr(0, window.location.href.indexOf('#'))
      , SAVE = {
            'priority': '/task/pri/',
            'status': '/task/s/',
            'delete': '/task/d/',
            'text': '/task/text/',
            'due': '/task/due/',
        }
      , TASKLIST_URL = '/tasklist/t/1/?n='
      , TASKLIST_TIMEOUT = 5000
      , REFRESH_TIMEOUT = 120000
      , MAIN
      , ROW_TEMPLATE = $('<div class="row "> \
            <div class="td s"> \
                <input type="checkbox" class="md sh" name="status"> \
            </div> \
            <div class="td p"> </div> \
            <div class="td text"><span class="editable"></span></div> \
            <div class="td due"><span class="editable"></span></div> \
            <div class="td followers"><span></span></div> \
            <div class="td del"> \
                <a href="#" class="sh del-link"> </a> \
                <input type="hidden" value="" name="task_id"> \
                <input type="hidden" value="" name="user_id"> \
            </div> \
            </div>')
      , EDITABLE_BLANK = $('<span class="editable"></span>')
    /*-------------- VARIABLES --------------*/
      , tasks_per_page
      , tasklist_refresh_timeout = false
      , tasklist_timeout = {
            'main': true,
        }
      , t_editing = {
            'main': false,
        }
      , t_page = get_url_param('p', INITIAL_URL);
    ;

    /**
     * Fetching tasklist
     */
    function get_tasklist() {
        var task_box = MAIN;
        clear_timeout(task_box);
        $.ajax({
            type: 'GET',
            url: TASKLIST_URL + tasks_per_page + '&p=' + t_page,
            dataType: 'json',
            beforeSend: function() {
                return set_loading(task_box);
            },
            error: function (response, text_status, error) {
                unset_loading(task_box);
                task_error_ajax(response, text_status, error);
                return false;
            },
            success: function(response, textStatus, request){
                if (request.status == 200) {
                    // build tasklist from json
                    task_box.children('.task-table').html('');
                    var task;
                    for (var i in response.tasks) {
                        json_task = response.tasks[i]
                        html_task = ROW_TEMPLATE.clone();
                        if (json_task.status) {
                            html_task.children('.s')
                                .attr('checked', 'checked');
                            html_task.addClass('done');
                        }
                        html_task.children('.p')
                            .addClass('pri_' + json_task.priority);
                        html_task.children('.text').children('.editable')
                            .html(json_task.text);
                        html_task.children('.due').children('.editable')
                            .html(json_task.due);
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
                    reset_timeout(task_box);
                } else {
                    task_error_response(response);
                }
                unset_loading(task_box);
            }
        });
    }

    /**
     * Changing task priority
     */
    $('.p', MAIN).live('click', function() {
        update_row('priority', MAIN, $(this));
        return false;
    });

    /**
     * Changing task status
     */
    $('.s', MAIN).live('click', function() {
        update_row('status', MAIN, $(this));
        return false;
    });

    /**
     * Changing task priority
     */
    $('.del-link', MAIN).live('click', function() {
        update_row('delete', MAIN, $(this));
        return false;
    });

    /**
     * Extracts data from a row
     */
    function extract_data(type, t_row, target) {
        var task_id = t_row.find('input[name="task_id"]')[0].value
          , data = {'url': SAVE[type] + task_id}
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
            case 'text':
            case 'due':
                break;
            default:
                return false;
        }
        return data;
    }

    function dispatch_response(type, task_box, t_row, target,
                response, textStatus, request, t_data) {
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
        }
    }

    /**
     * Updating row
     * @param type = update type, one of 'priority', 'status'
     * @param task_box = defaults to MAIN -- which box to look in
     * @param target = the target of the event
     */
    function update_row(type, task_box, target) {
        var t_row = target.parents('.row');
        clear_timeout(task_box);
        if (is_loading_row(t_row)) {
            return false;
        }
        t_data = extract_data(type, t_row, target);
        $.ajax({
            type: 'GET',
            url: t_data.url,
            dataType: 'json',
            beforeSend: function() {
                set_loading_row(t_row);
            },
            error: function (response, text_status, error) {
                unset_loading_row(t_row);
                task_error_ajax(response, text_status, error);
                return false;
            },
            success: function(response, textStatus, request){
                if (request.status == 200) {
                    dispatch_response(type, task_box, t_row, target,
                        response, textStatus, request, t_data);
                    reset_timeout(task_box);
                } else {
                    task_error_response(response);
                }
                unset_loading_row(t_row);
            }
        });
    }

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

    var
    /*-------------- CONSTANTS --------------*/
        WORK_BOX_CLASS = 'work_box'
      , WORK_BOX_TITLE = $('.' + WORK_BOX_CLASS + ' .title').html()
      , USER_ID = $('.menu .user a').attr('rel')
      , USER_NAME = $('.menu .user a').text()
      , TYPE_CODE = 100
      , LOADING_ROW_CLASS = 'loadbar'
      , TASK_TABLE_TEMPLATE = '<div class="task_div"><div class="loading"></div><h2 class="title"></h2><table class="tlist" cellspacing="0"><tr></table></div>'
      , TASK_TABLE_TEMPLATE_COMPACT = '<div class="task_div"><div class="loading"></div><h2 class="title"></h2><table class="tlist" cellspacing="0"><tr><th class="options"></th><th class="desc"></th></tr></table></div>'
      , SAVE_TASK_URL = $('#ajax_save_task').text()
      , LOAD_TASK_URL = $('#ajax_load_tasklist').text()
      , TASK_DEFAULT_REMIND = $('.' + WORK_BOX_CLASS + ' input[name="remind"]').val()
      , TASK_DEFAULT_DUE    = $('.' + WORK_BOX_CLASS + ' input[name="due"]')   .val()
      , SPINWHEEL = $('<div class="spin"></div>')
      , T_TYPE = new function() {
        this.my_tasks      = 1;
        this.created_tasks = 2;
        this.planner       = 3;
        this.archive       = 4;
        this.trash_tasks   = 5;
    }
      , T_TITLE = new function() {
        this.my_tasks      = 'my tasks';
        this.created_tasks = 'command center';
        this.planner       = 'planner';
        this.archive       = 'archive';
        this.trash_tasks   = 'wall of shame';
    }
      , DATE_RANGE_URL = {
        1: 'today'
      , 2: 'tomorrow'
      , 3: 'this_week'
      , 4: 'this_month'
    }
      , DATE_RANGE = {
        today     : 1
      , tomorrow  : 2
      , this_week : 3
      , this_month: 4
    }
    /*-------------- VARIABLES --------------*/
      , old_hash = ''
    ;

    $('.error_dialog').jqm();
    $('body').keydown(function(e) {
        if ((e.keyCode == 13 || e.keyCode == 27) &&
            $('.error_dialog').is(':visible')) {
            // esc or enter pressed
            $('.error_dialog').children('.jqmClose').click();
            return false;
        }
    });

    /* change date range in anchor */
    $('.menu a').bind('click', function() {
        var date_range = get_date_range_from_url($(this).attr('href'));
        window.location.href = INITIAL_URL_NOHASH + '#' + DATE_RANGE_URL[date_range];
        return false;
    });

    // This function is called when:
    // 1. after calling $.historyInit();
    // 2. after calling $.historyLoad();
    // 3. after pushing "Go Back" button of a browser
    function on_hash_change(hash) {
        if (hash !== old_hash) {
            old_hash = hash;
            if (!hash) {
                url_param.dr = get_date_range_from_url(window.location.href, true);
            } else if (DATE_RANGE[hash]) {
                url_param.dr = DATE_RANGE[hash];
            }
            $('.menu li').removeClass('active');
            refresh_all_tasklists();
            $('.menu li').eq(url_param.dr-1).addClass('active');
        }
    }
    // Initialize history plugin.
    // The callback is called at once by present location.hash. 
    $.historyInit(on_hash_change, INITIAL_URL);

    $('form.inplace input').live('focusout', function () {
        cancel_edit_task($(this));
    });
    $('form.inplace input').live('keydown', function(e) {
        var move_ref = []
          , t_row = $(this).parents('.row')
          , t_d = $(this).parents('.td')
        ;
        // move up
        if (e.keyCode == 40) {
            var move_ref = $(this).parents('.row').next();
            if (move_ref.length == 0) {
                move_ref = $(this).parents('.row').parent().children().eq(0);
            }
        // move down
        } else if (e.keyCode == 38) {
            var move_ref = $(this).parents('.row').prev();
            if (move_ref.length == 0) {
                move_ref = $(this).parents('.row').parent().children().last();
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
            move_ref = move_ref.find('.editable').parent();
            cancel_edit_task($(this));
            r = move_ref.find('.editable');
            r.click();
        }
        return false;
    });

    function get_old_text(ref) {
        return ref.parents('.row').find('input[name="buffer"]').val().replace(/\\\"/g, '"').replace(/\\\'/g, "'");
    }

    function finish_edit(task_box, ref, text) {
        editable = EDITABLE_BLANK.clone().html(text)
        editable.find('a').bind('click', editable_link);
        ref.parents('.td')
            .html(editable);
        t_editing[task_box[0].id] = false;
    }

    function cancel_edit_task(ref, new_text) {
        var task_box = ref.parents('.task-box');
        var old_text = get_old_text(ref);
        if (new_text && new_text != old_text) {
            return false;
        }
        finish_edit(task_box, ref, old_text);
        return true;
    }

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

    function edit_handler(ref) {
        var t_row = ref.parents('.row')
          , t_d = ref.parents('.td')
          , save = t_d.attr('class').substr(3)
          , task_box = ref.parents('.task-box')
        ;
        if (is_loading_row(t_row)) {
            return false;
        }
        var new_text = ref.val().replace(/\\\"/g, '"').replace(/\\\'/g, "'")
          , edit_ref = ref.parent().parent()
        ;
        if (cancel_edit_task(ref, new_text)) {
            return false;
        }

        clear_timeout(task_box);
        switch (t_row.children().index(t_d)) {
            case 2:
                // text
                break;
            case 3:
                // date
                break;
        }
        t_data = extract_data(save, t_row, ref);
        post_data = {};
        post_data[save] = new_text;
        $.ajax({
            type: 'POST',
            url: t_data.url,
            data: post_data,
            beforeSend: function() {
                set_loading_row(t_row);
            },
            error: function (response, text_status, error) {
                unset_loading_row(t_row);
                return task_error_ajax(response, text_status, error);
            },
            success: function(response, textStatus, request){
                if (request.status == 200) {
                    finish_edit(task_box, ref, response[save]);
                    reset_timeout(task_box);
                } else {
                    task_error_response(response);
                }
                unset_loading_row(t_row);
            }
        });
        return false;
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
        return '<form class="inplace"><input type="text" value="' + buffer.replace(/(<([^>]+)>)/ig,"") + '" /><input type="hidden" name="buffer" value="' + buffer + '" /></form>';
    }

    /* code for editable tasks */
    $('.editable').live('click', replace_html);
    function editable_link(e) {
        if (!e.ctrlKey) {
            window.location.href = $(this).attr('href');
            return false;
        }
    }
    $('.editable a').live('click', editable_link);

    /* enter/escape actions inside form */
    $('form.inplace input').live('keydown', function(e) {
        if (e.keyCode == 13) {
            // enter pressed
            edit_handler($(this));
            return false;
        }
        else if (e.keyCode == 27) {
            // esc pressed
            cancel_edit_task($(this));
            return false;
        }
    });
    /* end of code for editable tasks */

    /* add task -- priority */
    $('.' + WORK_BOX_CLASS + ' .priority span').live('click', function() {
        if ($(this).hasClass('s')) {
            $('.' + WORK_BOX_CLASS + ' .priority input').val('3');
            $(this).removeClass('s');
        }
        else {
            $('.' + WORK_BOX_CLASS + ' .priority span')
                .removeClass('s');
            $('.' + WORK_BOX_CLASS + ' .priority input').val($(this).find('img').attr('alt'));
            $(this)
                .addClass('s');
        }
        return false;
    });

    /* toggle task done/undone */
    $('.tasklist .md').live('click', function() {
        var t_row = $(this).parents('.row')
          , load_ref = $(this).parents('.task-box')
          , t_class = load_ref.attr('class').substr(9)
        ;
        clear_timeout(t_class);
        if (is_loading_row(t_row)) {
            return false;
        }
        var md_ref = $(this).parent().parent()
          , savedata = build_loaddata(t_class);
        savedata.task_id = $(this).parent().siblings(':nth-child(2)').children()[0].value;
        savedata.status = 'toggle';
        $.ajax({
            type: 'POST',
            url: SAVE_TASK_URL,
            data: (savedata),
            beforeSend: function() {
                set_loading_row(t_row);
            },
            error: function (response, text_status, error) {
                unset_loading_row(t_row);
                return task_error_ajax(response, text_status, error);
            },
            success: function(response){
                if ('1' == response.charAt(0)) {
                    var new_text = response.substr(5).replace(/\\\"/g, '"').replace(/\\\'/g, "'");
                    if (md_ref.hasClass('done')) {
                        md_ref.removeClass('done')
                              .find('input').attr('checked', '');
                    }
                    else {
                        md_ref.addClass('done')
                              .find('input').attr('checked', 'checked');
                    }
                    reset_timeout(t_class, load_ref, new_text)
                }
                else {
                    task_error_response(response);
                }
                unset_loading_row(t_row);
            }
        });
        return false;
    });

    /* trash task */
    $('.tasklist .dl').live('click', function() {
        var load_ref = $(this).parents('.task-box')
          , tRef = $(this).parent().parent()
          , t_class = load_ref.attr('class').substr(9)
          , t_tr = $(this).parents('.row')
          , t_id = t_tr.children(':nth-child(2)').children()[0].value
          , t_remind = t_tr.find('input[name="remind"]').val()
          , t_due = t_tr.children()[3].innerHTML
          , t_user_id = t_tr.find('input[name="user_id"]').val()
          , t_created_by = t_tr.find('input[name="created_by"]').val()
          , t_priority = t_tr.find('td.p').attr('class').substr(9)
          , t_description = t_tr.children()[2].innerHTML
          , savedata = build_loaddata(t_class)
        ;
        delete savedata.type;
        savedata['type' + T_TYPE[t_class]] = 1;
        savedata['type' + T_TYPE.trash_tasks] = 1;
        savedata['p' + T_TYPE[t_class]] = url_param[t_class];
        savedata['p' + T_TYPE.trash_tasks] = url_param.trash_tasks;
        savedata.task_id = t_id;
        savedata.trash = '1';

        $.ajax({
            type: 'POST',
            url: SAVE_TASK_URL,
            data: (savedata),
            error: function (response, text_status, error) {
                return task_error_ajax(response, text_status, error);
            },
            success: function(response){
                if ('1' == response.charAt(0)) {
                    var response_arr = response.substr(2).replace(/\\\"/g, '"').replace(/\\\'/g, "'").split('@#')
                      , new_text = response_arr[0].substr(3)
                      , new_tlist_data = response_arr[1]
                    ;
                    tRef.fadeOut('slow', function() {
                        $('.trash_tasks .tlist').prepend('<tr><td><a class="ed showhover" href="?e=' + t_id + '">e</a>\
                            <span class="hide"> \
                                <span class="remind">' + t_remind + '</span> \
                                <span class="due">' + t_due + '</span> \
                                <span class="user_id">' + t_user_id + '</span> \
                                <span class="priority">' + t_priority + '</span> \
                            </span> \
                        </td><td>' + t_description + '</td></tr>');
                        $(this).remove();
                        if (response_arr.length > 1) {
                            update_tasklist('trash_tasks', new_tlist_data);
                        }
                        update_tasklist(t_class, new_text);
                    });
                }
                else {
                    task_error_response(response);
                }
            }
        });
        return false;
    });

    function edit_task(task_id, user_id, description, remind, due, priority) {
        if (!remind || remind == '+0s') remind = 'now';
        if (!due || due == '+0s' || due == '-30yr') due = 'today';
        $('.' + WORK_BOX_CLASS + ' textarea[name="description"]')[0].value = description;
        $('.' + WORK_BOX_CLASS + ' input[name="remind"]').val(remind);
        $('.' + WORK_BOX_CLASS + ' input[name="due"]').val(due);
        $('.' + WORK_BOX_CLASS + ' select[name="user_id"] option')
            .attr('selected', '');
        $('.' + WORK_BOX_CLASS + ' select[name="user_id"] option[value="' + user_id + '"]')
            .attr('selected', 'selected');
        $('.' + WORK_BOX_CLASS + ' .title').html(WORK_BOX_TITLE + ' (edit)');
        $('.' + WORK_BOX_CLASS + ' input[name="add"]').hide();
        $('.' + WORK_BOX_CLASS + ' input[name="task_id"]').val(task_id);

        $('.' + WORK_BOX_CLASS + ' .priority input').val(priority);
        $('.' + WORK_BOX_CLASS + ' .priority span').removeClass('s');
        if (priority != '3') {
            if (priority == '1') {
                var priority_selector = ':first';
            }
            else if (priority == '2') {
                var priority_selector = ':last';
            }
            $('.' + WORK_BOX_CLASS + ' .priority span' + priority_selector).addClass('s');
        }

        $('.' + WORK_BOX_CLASS + ' input[name="edit"]').show();
    }

    function cancel_edit() {
        $('.' + WORK_BOX_CLASS + ' textarea[name="description"]')[0].value = '';
        $('.' + WORK_BOX_CLASS + ' input[name="remind"]').val(TASK_DEFAULT_REMIND);
        $('.' + WORK_BOX_CLASS + ' input[name="due"]').val(TASK_DEFAULT_DUE);
        $('.' + WORK_BOX_CLASS + ' select[name="user_id"] option')
            .attr('selected', '');
        $('.' + WORK_BOX_CLASS + ' .title').html(WORK_BOX_TITLE);
        $('.' + WORK_BOX_CLASS + ' input[name="task_id"]').val('');
        $('.' + WORK_BOX_CLASS + ' input[name="edit"]').hide();

        $('.' + WORK_BOX_CLASS + ' .priority input').val('3');
        $('.' + WORK_BOX_CLASS + ' .priority span').removeClass('s');

        $('.' + WORK_BOX_CLASS + ' input[name="add"]').show();
    }

    $('.ed').live('click', function() {
        var taskRef = $(this).next().children()
          , task_id = parseInt($(this).attr('href').substr(3))
            task = {
                description: $(this).parent().next().text()
              , remind : taskRef[0].innerHTML
              , due    : taskRef[1].innerHTML
              , user_id: taskRef[2].innerHTML
              , priority: taskRef[3].innerHTML
            };
        edit_task(task_id, task.user_id, task.description, task.remind, task.due, task.priority);
        document.getElementById('wb').scrollIntoView(true);
        return false;
    });

    $('.cancel_edit').click(function() {
        cancel_edit();
        return false;
    });

    $('.' + WORK_BOX_CLASS + ' tr:last td')
        .prepend(SPINWHEEL);
    SPINWHEEL.hide();
    $('.' + WORK_BOX_CLASS + ' input[type="submit"]').click(function () {
        var form_data = $('.' + WORK_BOX_CLASS + ' form').serialize()
          , load_ref = $('.' + WORK_BOX_CLASS + '')
          , form_action = $('.' + WORK_BOX_CLASS + ' input[type="submit"]:visible').attr('name');
        $.ajax({
            type: 'POST',
            url: SAVE_TASK_URL,
            data: (form_data + '&' + form_action + '=1'),
            beforeSend: function() {
                SPINWHEEL.show();
            },
            error: function (response, text_status, error) {
                SPINWHEEL.hide();
                return task_error_ajax(response, text_status, error);
            },
            success: function(response){
                if ('1' == response.charAt(0)) {
                }
                else {
                    task_error_response(response);
                }
                refresh_all_tasklists();
                SPINWHEEL.hide();
            }
        });
        return false;
    });


    /*$('.' + WORK_BOX_CLASS + ' input[type="text"]').datetime({
        userLang    : 'en',
        americanMode: true,
    });*/

    function build_loaddata(type) {
        if (type == undefined       || !type      ) type       = 1;
        var loaddata = {'user_id': USER_ID
            , 'type'      : type
            , 'date_range': url_param.dr
            , 'load': 1}
        if (type == TYPE_CODE) {
            delete loaddata.type;
            for (var i in T_TYPE) {
                loaddata['p' + T_TYPE[i]] = url_param[i];
                loaddata['type' + T_TYPE[i]] = 1;
            }
        } else {
            loaddata['p' + T_TYPE[type]] = url_param[type];
            loaddata.type = T_TYPE[type];
        }
        return loaddata;
    }

    function update_tasklist(elem) {

        return false;
        clear_timeout(elem_class);
        var elem_ref = $('.' + elem_class)
          , new_pager_text = ''
          , pager_start = new_text.indexOf('@!@pager')
        ;
        if (pager_start >= 0) {
            new_pager_text = new_text.substr(pager_start + 8);
            new_text = new_text.substr(0, pager_start);
        }
        /* create tasklist if it doesn't exist */
        if (elem_ref.length <= 0 && new_text
            && (new_text.indexOf('<td') >= 0)) {
            switch (T_TYPE[elem_class]) {
                case 1:
                    elem_ref = $(TASK_TABLE_TEMPLATE)
                        .prependTo('#content_left')
                        .addClass(elem_class)
                    ;
                    elem_ref.find('.tlist').addClass('tasklist');
                    break;
                case 2:
                    elem_ref = $(TASK_TABLE_TEMPLATE)
                        .appendTo('#content_left')
                        .addClass(elem_class)
                    ;
                    elem_ref.find('.tlist').addClass('tasklist');
                    break;
                case 3:
                    elem_ref = $(TASK_TABLE_TEMPLATE_COMPACT)
                        .addClass(elem_class)
                        .insertAfter('.work_box')
                    ;
                    elem_ref.find('.tlist').addClass('tasklist2');
                    break;
                case 4:
                    elem_ref = $(TASK_TABLE_TEMPLATE_COMPACT)
                        .addClass(elem_class)
                    ;
                    if ($('.planner').length >= 0) {
                        elem_ref.insertAfter('.planner');
                    } else {
                        elem_ref.insertAfter('.work_box');
                    }
                    elem_ref.find('.tlist').addClass('tasklist2');
                    break;
                case 5:
                    elem_ref = $(TASK_TABLE_TEMPLATE_COMPACT)
                        .addClass(elem_class)
                        .appendTo('#content_right')
                    ;
                    elem_ref.find('.tlist').addClass('tasklist2');
                    break;
            }
            elem_ref.find('.title').html(T_TITLE[elem_class]);
        }
        if (elem_ref.length > 0 && new_text
            && new_text.indexOf('<td') < 0) {
            if (url_param[elem_class] > 0) {
                elem_ref.find('.pager a:last').click();
            } else {
                elem_ref.remove();
            }
        }
        elem_ref.find('.pager').html(new_pager_text);
        elem_ref.find('.tlist').html(new_text);
        elem_ref.find('.loading').height(elem_ref.height() + 'px');
    }

    function get_and_set_tasklist(elem_ref, t_class) {
        var type = t_class;
        if (elem_ref) {
            var load_ref = elem_ref.parents('.task-box');
        }
        var loaddata = build_loaddata(type);
        $.ajax({
            type: 'POST',
            url: LOAD_TASK_URL,
            data: (loaddata),
            beforeSend: function() {
                tasks_action(show_loading, load_ref);
            },
            error: function (response, text_status, error) {
                task_error_ajax(response, text_status, error);
                tasks_action(hide_loading, load_ref);
            },
            success: function(response) {
                if ('1' == response.charAt(0)) {
                    if (type == TYPE_CODE) {
                        new_tables = response.substr(2).split('@#');
                        for (var i in T_TYPE) {
                            if (!t_editing[i]) {
                                update_tasklist(i, new_tables[T_TYPE[i]-1]);
                            }
                        }
                    } else {
                        update_tasklist(t_class, response.substr(2));
                    }
                }
                else {
                    task_error_response(response);
                }
                tasks_action(hide_loading, load_ref);
            }
        });
    }

    function refresh_all_tasklists() {
        get_and_set_tasklist(null, TYPE_CODE);
    }
    tasklist_refresh_timeout = setInterval(refresh_all_tasklists, REFRESH_TIMEOUT);

    function tasks_action(func_action, load_ref) {
        if (undefined == load_ref) {
            for (var i in T_TYPE) {
                var load_ref = $('.' + i);
                if (undefined != load_ref.attr('class')) {
                    func_action(load_ref);
                }
            }
        } else {
            func_action(load_ref);
        }
    }

    function get_date_range_from_url(url, force) {
        var date_range_string = '';
        if (url.indexOf('#') >= 0 && !force) {
            date_range_string = url.substr(url.indexOf('#') + 1);
        } else if (url.indexOf('/list/') >= 0) {
            date_range_string = url.substr(url.indexOf('/list/') + 6);
        } else {
            date_range_string = 'today';
        }
        return DATE_RANGE[date_range_string];
    }

    function get_url_param(name, url) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( url );
        if( results == null )
            return "";
        else
            return results[1];
    }

    /**
    * Function : dump()
    * Arguments: The data - array,hash(associative array),object
    *    The level - OPTIONAL
    * Returns  : The textual representation of the array.
    * This function was inspired by the print_r function of PHP.
    * This will accept some data as the argument and return a
    * text that will be a more readable version of the
    * array/hash/object that is given.
    * Docs: http://www.openjs.com/scripts/others/dump_function_php_print_r.php
    */
    function dump(arr,level) {
        var dumped_text = "";
        if(!level) level = 0;

        //The padding given at the beginning of the line.
        var level_padding = "";
        for(var j=0;j<level+1;j++) level_padding += "    ";

        if(typeof(arr) == 'object') { //Array/Hashes/Objects 
            for(var item in arr) {
                var value = arr[item];
                if(typeof(value) == 'object') { //If it is an array,
                    dumped_text += level_padding + "'" + item + "' ...\n";
                    dumped_text += dump(value,level+1);
                } else {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
            }
        } else { //Stings/Chars/Numbers etc.
            dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
        }
        return dumped_text;
    }


    $('.pager a').live('click', function() {
        t_page = parseInt(get_url_param('p', $(this).attr('href')));
        get_tasklist();
        return false;
    });

    /**
     * Timeouts helpers
     */
    function clear_timeout(elem) {
        var id = elem[0].id;
        if (tasklist_timeout[id]) {
            clearTimeout(tasklist_timeout[id]);
        }
    }

    function reset_timeout(elem) {
        clear_timeout(elem);
        var id = elem[0].id;
        tasklist_timeout[id] = setTimeout(function() {
            if (!t_editing[id]) {
                update_tasklist(elem);
            }
        }, TASKLIST_TIMEOUT);
    }
    /* end timeouts helpers */

    /**
     * Resizing the window causes tables to resize
     */
    function resize() {
        var t_height = 0;
        MAIN.height($(window).height() - FOOTER_SPACE);
        tasks_per_page = parseInt(
            (MAIN.height() - TASK_TABLE_MINUS)
            / TASK_TABLE_ROW_HEIGHT
        );
        $('.loading').each(function() {
            $(this).height($(this).parent().height() + 'px');
        });
        if ($('.task-table', MAIN).height() == tasks_per_page * TASK_TABLE_ROW_HEIGHT) {
            return false;
        }
        $('.task-table', MAIN).height(tasks_per_page * TASK_TABLE_ROW_HEIGHT);
        get_tasklist();
    }
    $(window).resize(resize);

    /**
     * Create page
     */
    (function () {
        $('#content').html('\
        <div class="task-box" id="main"> \
            <div class="loading"></div> \
            <h1 class="title">my tasks</h1> \
            <div class="task-table" cellspacing="0"> \
            </div> \
            <!-- pager? --> \
        </div><!-- /.task-box -->');
        MAIN = $('#main');
        resize();
    })();


});
