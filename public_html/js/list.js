/**
 * Handles lists: reloads, creation, searches, etc
 * Expects a global variable list_handler as a reference to itself
 * which is required for executing events in global scope where `this`
 * is lost. Also expects globals for url_handler, row_handler, notif_handler
 * @requires jQuery (tested with 1.4.[012])
 * @requires url.js
 * @requires row.js
 * @requires notification.js
 */
function List() {
    // ajax call results
    this.response = null;
    this.request = null;
    this.error = null;
    this.textStatus = null;

    // tasks per page
    this.main_per_page = 10;
    this.mini_per_page = 10;

    // list of groups
    this.groups_list = false;

    // which of the two lists to expect
    this.expecting = [1, 1];
    // what to expect in the second list
    // 1 = planner, 2 = trash
    this.expect_what = 1;

    // search value, used in do_search()
    this.search_val = '';
    // stored for comparison and reset to avoid searching again
    this.last_search_q = '';

    // store planning custom date
    this.plan_custom = false;

    // timeouts
    this.search_timeout = false;
    this.refresh_timeout = false;
    this.resize_timeout = false;
    this.list_timeout = false;

    // marked with `true` if list is being edited
    this.editing = [0, 0];

    // hash parameters
    this.mainpage = null;
    this.minipage = null;
    this.group = null;
    this.type = null;

    /**
     * Sets the parameters from url hash
     * @param int mainpage the page for the main list
     * @param int minipage the page for the mini list
     * @param int group the group
     * @param int type indicates the active tab
     */
    this.set_params = function (mainpage, minipage, group, type) {
        this.mainpage = mainpage;
        this.minipage = minipage;
        this.group = group;
        this.type = type;
    };

    // Timeouts helpers
    /**
     * Clears the timeouts for list and refresh
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     */
    this.clear_timeout = function (li) {
        if (this.list_timeout) {
            clearTimeout(this.list_timeout);
            clearTimeout(this.refresh_timeout);
        }
    };

    /**
     * Resets the timeouts for list and refresh
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     * @requires global reference to list_handler
     */
    this.reset_timeout = function (li) {
        this.clear_timeout(li);
        // we're expecting this now
        this.expect(li);
        this.list_timeout = setTimeout(function () {
            list_handler.get_lists();
        }, TIMEOUTS.changed);
        this.refresh_timeout = setInterval(this.refresh_all,
            TIMEOUTS.refresh);
    };

    /**
     * Refreshes all the lists, iterates over this.expecting
     * @requires global reference to list_handler
     */
    this.refresh_all = function () {
        for (var i in list_handler.expecting) {
            list_handler.expect(i);
        }
        list_handler.get_lists();
    };
    // End timeouts helpers

    // Task loading helpers
    /**
     * Mark the list as loading
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     * @returns true if request was carried out
     *     or false if not allowed. used to check list is busy
     */
    this.set_loading = function (li) {
        // if list is being edited, return false
        if (this.editing[li]) {
            return false;
        }

        LISTS[li].children('.loading').show();
        return true;
    };

    /**
     * Mark the list as not loading (unset)
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     */
    this.unset_loading = function (li) {
        LISTS[li].children('.loading').hide();
    };
    // End task loading helpers

    /**
     * Expect a list when rebuilding
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     * @see get_lists for use of this.expecting
     */
    this.expect = function (li) {
        this.expecting[li] = 1;
    };

    /**
     * Unxpect a list when rebuilding
     * @param int li the list to clear
     *     atm, valid values are 0 (main) or 1 (mini)
     * @see get_lists for use of this.expecting
     */
    this.unexpect = function (li) {
        this.expecting[li] = 0;
    };

    /**
     * Set a custom plan date
     * @param string val will be interpreted as task due date
     */
    this.plan = function (val) {
        this.plan_custom = val;
    };

    /**
     * Fetching a list
     * @requires global reference to list_handler
     * @requires global reference to notif_handler
     */
    this.get_lists = function () {
        // reset search
        this.last_search_q = '';

        for (var i in this.expecting) {
            if (this.expecting[i]) {
                this.clear_timeout(i);
            }
        }

        $.ajax({
            type: 'GET',
            url: PATHS.list + '?g=' + this.group +
                '&t=' + this.type + '&p=' + this.mainpage +
                '&u=' + this.minipage + '&tr=' + this.expect_what +
                '&ep=' + this.expecting[0] + '&n=' + this.main_per_page +
                '&eu=' + this.expecting[1] + '&m=' + this.mini_per_page,
            dataType: 'json',
            beforeSend: function () {
                // check expecting and loading before requesting
                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i] &&
                        !list_handler.set_loading(i)) {
                        return false;
                    }
                }
            },
            error: function (request, textStatus, error) {
                // store internals for passing around of data
                list_handler.request = request;
                list_handler.textStatus = textStatus;
                list_handler.error = error;
                list_handler.response = null;
                // clear the loading
                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i]) {
                        list_handler.unset_loading(i);
                    }
                }
                // handle not found
                if (request.status === 404) {
                    // still set the title and active tab
                    if (list_handler.expecting[0]) {
                        $('.title', LISTS[0])
                            .html(TITLES_HTML[list_handler.type]);

                        TEMPLATES.notasks.insertBefore(
                            $('.table', LISTS[0]));
                        $('.table', LISTS[0])
                            .children().remove().end()
                            .html('');
                    }
                    // mini list notifies instead, and fails to switch
                    if (list_handler.expecting[1]) {
                        if (list_handler.expect_what === 1) {
                            list_handler.notif_handler.add(2,
                                'No tasks found in '
                                . TITLES_PLAIN[5]);
                        } else {
                            list_handler.notif_handler.add(2,
                                'No tasks found in '
                                . TITLES_PLAIN[6]);
                        }
                    }

                    list_handler.update_active_tabs();
                }
            },
            success: function (response, textStatus, request) {
                // store internals for passing around of data
                list_handler.response = response;
                list_handler.textStatus = textStatus;
                list_handler.request = request;
                list_handler.error = null;

                // build the lists according to responses
                list_handler.build_lists();

                // unload and unexpect
                for (var i in list_handler.expecting) {
                    if (list_handler.expecting[i]) {
                        list_handler.unset_loading(i);
                        // done, expecting nothing now
                        list_handler.unexpect(i);
                    }
                }

                list_handler.update_active_tabs();
            }
        });
    };

    /**
     * Updates the active tabs according to current state
     * @requires global reference to list_handler
     */
    this.update_active_tabs = function () {
        $('.tabs .icon', LISTS[0])
            .removeClass('active')
            .eq(list_handler.type).addClass('active')
        ;
        $('.tabs .icon', LISTS[1])
            .removeClass('active')
            .eq(list_handler.expect_what - 1).addClass('active')
        ;
    };

    /**
     * Builds the lists according to this.response
     */
    this.build_lists = function () {
        var i, k, html_task, html_text, pager;
        // build tasklist from json
        this.update_groups();

        // remove previous tasks
        for (i in this.expecting) {
            if (this.expecting[i]) {
                if (0 === parseInt(i, 10)) {
                    // for main, also remove message
                    $('.notasks', LISTS[i]).remove();
                }
                $('.table', LISTS[i])
                    .children().remove().end()
                    .html('');
            }
        }

        for (i in this.response.tasks) {
            // build planner and trash if not searching
            if ('' === this.last_search_q &&
                (this.response.tasks[i].planned ||
                this.response.tasks[i].trash)
            ) {
                html_task = this.build_task_json_min(i);
                if (this.response.tasks[i].trash) {
                    html_task.appendTo(LISTS[1].children('.table'));
                    html_task.find('.del').addClass('undo');
                } else {
                    html_task.children().eq(1).addClass('plan')
                        .bind('click', this.handle_plan_action);
                    html_task.appendTo(LISTS[1].children('.table'));
                }
            } else {
            // build main list
                html_task = this.build_task_json(i);
                html_task.appendTo(LISTS[0].children('.table'));
                if (1 === this.type && this.response.tasks[i].group) {
                    html_text = html_task.children('.text');
                    html_text.find('.editable').width(
                        html_text.width() - html_text.find('.g').width() -
                        this.ASSIGNMENT_EDITABLE_WIDTH_ADJUSTMENT
                    );
                    html_text.children('.editable').css(
                        'text-indent', (html_text.children('.g').width() +
                            PIXELS.assignmentindent) + 'px');
                }
            }
        }

        // update the pager and the counts
        for (i in this.expecting) {
            if (this.expecting[i]) {
                pager = $('.pager', LISTS[i]);
                if (pager.length > 0) {
                    pager.remove();
                }
                pager = $(this.response.pager[i]).appendTo(LISTS[i]);
                if (i === '0') {
                    pager.children('a').bind('click', handle_pager_main);
                } else {
                    pager.children('a').bind('click', handle_pager_mini);
                }

                // update small numbers for the tabs
                for (k in this.response.counts[i]) {
                    $('.tabs .c', LISTS[i]).eq(k)
                        .html(this.response.counts[i][k]);
                }
            }
        }
    };

    /**
     * Builds a row for the mini list
     * @param int i the row number in this.response.tasks
     */
    this.build_task_json_min = function (i) {
        // use these throughout for convenience
        var task_group, html_text,
            json_task = this.response.tasks[i],
            html_task = TEMPLATES.minirow.clone()
        ;
        if (json_task.status) {
            html_task.addClass('done');
        }
        html_text = html_task.children('.text');
        if (json_task.group) {
            task_group = TEMPLATES.rowgroup.clone().attr('href', '#g=' +
                json_task.group.id)
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
    };

    /**
     * Builds a row for the main list
     * @param int i the row number in this.response.tasks
     * @requires global reference to row_handler
     */
    this.build_task_json = function (i) {
        var task_group, html_text, j, html_followers, json_task, html_task;
        json_task = this.response.tasks[i];
        html_task = TEMPLATES.mainrow.clone();
        if (json_task.status) {
            html_task.children('.s').children('input')
                .attr('checked', 'checked');
            if (this.type !== 3) {
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
            task_group = TEMPLATES.rowgroup.clone().attr('href', '#g=' +
                json_task.group.id)
                .html(json_task.group.name);
            // for ASSIGNMENTS, not allowed to change group
            if (this.type === 1) {
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
        html_followers = TEMPLATES.followers.clone();
        for (j in json_task.followers) {
            html_followers.find('input.u' +
                json_task.followers[j].id).attr('checked', 'checked');
        }
        html_followers.appendTo(html_task.children('.followers'))
            .find('input').bind('click', handle_follow_action);
        html_task.children('.del')
            .children('input[name="task_id"]').val(json_task.id)
            .end()
            .children('input[name="user_id"]').val(json_task.user_id)
            .end()
            .children('a').bind('click', handle_delete);
        return html_task;
    };

    /**
     * Performs a search
     * @requires global reference to list_handler
     * @requires global reference to notif_handler
     */
    this.do_search = function () {
        if (undefined === this.search_val ||
            this.search_val.length <= 0) {
            return;
        }
        // search results go in main list only
        list_handler.expect(0);
        list_handler.unexpect(1);

        $.ajax({
            type: 'GET',
            url: PATHS.list +
                '?p=' + this.search_page + '&s=' + this.search_val +
                '&ep=' + this.expecting[0] + '&n=' + this.main_per_page,
            dataType: 'json',
            beforeSend: function () {
                // show searching is in progress
                $('.search-s', LISTS[0]).show();
                list_handler.set_loading(0);
            },
            error: function (response, textStatus, error) {
                // remember search
                list_handler.last_search_q = list_handler.search_val;

                list_handler.unset_loading(0);
                $('.search-s', LISTS[0]).hide();

                // if nothing found
                if (response.status === 404) {
                    $('.table', LISTS[0])
                        .children().remove().end()
                        .html('');
                    TEMPLATES.notasks.insertBefore($('.table',
                        LISTS[0]));
                    return false;
                }

                // show error message
                notif_handler.add(2, 'Error while searching');
                return false;
            },
            success: function (response, textStatus, request) {
                // remember search
                list_handler.last_search_q = list_handler.search_val;

                $('.search-s', LISTS[0]).hide();
                list_handler.unset_loading(0);

                // wipe previous list
                $('.table', LISTS[0])
                    .children().remove().end()
                    .html('');

                // store internals and pass on the activity
                list_handler.response = response;
                list_handler.textStatus = textStatus;
                list_handler.request = request;
                list_handler.build_lists();

                // change the title to distinguish search results
                var url_g = '#t=' + list_handler.group,
                    title_g = TITLES_PLAIN[4];
                $('.title', LISTS[0])
                    .html('<a href="' + url_g + '">' +
                        title_g +
                        '</a>');
            }
        });
    };

    /**
     * Calculate tasks per page
     * @requires global reference to list_handler
     */
    this.per_page = function () {
        // calculate main tasks per page
        this.main_per_page = parseInt(((LISTS[0].height() -
            PIXELS.mainminus) / PIXELS.rowheight), 10);

        // calculate mini tasks per page
        this.mini_per_page = parseInt(((LISTS[1].height() -
            PIXELS.miniminus) / PIXELS.rowheight), 10);
    };

    /**
     * Resizing the window causes list tables to resize
     * @requires global reference to list_handler
     */
    this.resize = function () {
        var reload = false;

        list_handler.per_page();
        // resize loading boxes
        $('.loading').each(function () {
            $(this).height($(this).parent().height() + 'px');
        });

        // don't automatically refresh unless the numbers changed changed
        list_handler.unexpect(0);
        list_handler.unexpect(1);

        // check if main number changed
        if ($('.table', LISTS[0])
                .height() !== list_handler.main_per_page * PIXELS.rowheight) {
            $('.table', LISTS[0])
                .height(list_handler.main_per_page * PIXELS.rowheight);
            reload = true;
            list_handler.expect(0);
        }

        // check if mini number changed
        if ($('.table', LISTS[1])
                .height() !== list_handler.mini_per_page * PIXELS.rowheight) {
            $('.table', LISTS[1])
                .height(list_handler.mini_per_page * PIXELS.rowheight);
            reload = true;
            list_handler.expect(1);
        }

        if (!url_handler.hash_last) {
            reload = true;
        }
        if (reload) {
            list_handler.get_lists();
        }
    };

    /**
     * Updates the groups in the main dropdown
     */
    this.update_groups = function (groups) {
        // store for convenience
        if (undefined === groups) {
            groups = this.response.groups;
        }
        if (!this.group) {
            $('.title', $('#main'))
                .html(TITLES_HTML[this.type]);
        }
        if (this.groups_list) {
            this.groups_list.remove();
        }
        if (groups.length <= 0) {
            return;
        }
        var html_g, url_g, i, title_g;
        // clean up
        this.groups_list = TEMPLATES.groups.clone().html('');
        for (i in groups) {
            url_g = '#g=' + groups[i].id;
            title_g = groups[i].name +
                ' (' + groups[i].num_tasks + ')';
            if (this.group && this.group === groups[i].id) {
                url_g = '#t=' + this.group;
                $('.title', $('#main'))
                    .html('<a href="' + url_g + '">' +
                        title_g +
                        '</a>');
                title_g = TITLES_PLAIN[this.type];
            }
            html_g = TEMPLATES.group.clone();
            html_g.children('a')
                .attr('href', url_g)
                .html(title_g)
            ;
            this.groups_list.append(html_g);
        }
        this.groups_list.appendTo($('.groups', LISTS[0]));
        $('.groups a').bind('click', handle_change_group);
    };

    /************************************************************************/
    /**
     * jQuery events
     * After this point, all functions are called by jQuery events
     */
    /************************************************************************/

    /**
     * Callback for initiating the search
     * Updates list_handler.search_page and timeouts
     * @requires global reference to list_handler
     */
    handle_search_action = function (e) {
        list_handler.search_page = 1;
        clearTimeout(list_handler.search_timeout);
        var search_val = $(this).val();
        if (search_val.length < COUNTS.searchmin) {
            if (search_val.length <= 0) {
                if (list_handler.last_search_q.length > 0) {
                    list_handler.get_lists();
                }
                list_handler.last_search_q = '';
            }
            return true;
        }
        if (list_handler.last_search_q === search_val) {
            return true;
        }
        list_handler.clear_timeout(0);

        list_handler.search_timeout = setTimeout(function () {
            list_handler.search_val = search_val;
            list_handler.do_search();
        }, TIMEOUTS.search);
    };

    /**
     * Callback main pager updates the url hash
     * Parameter: p
     * @requires global reference to list_handler
     * @requires global reference to url_handler
     */
    handle_pager_main = function () {
        var page = parseInt(url_handler
            .get_url_param(PARAMS.mainpage, $(this).attr('href')), 10);
        if (list_handler.last_search_q.length > 0) {
            list_handler.search_page = page;
            list_handler.last_search_q = list_handler.search_val;
            list_handler.do_search();
        } else {
            url_handler.url_update_hash(PARAMS.mainpage, page);
        }
        return false;
    };

    /**
     * Callback mini pager updates the url hash
     * Parameter: u
     * @requires global reference to list_handler
     * @requires global reference to url_handler
     */
    handle_pager_mini = function () {
        var page = parseInt(url_handler
            .get_url_param(PARAMS.minipage, $(this).attr('href')), 10);
        url_handler.url_update_hash(PARAMS.minipage, page);
        return false;
    };

    /**
     * Changing task priority
     * @requires global reference to row_handler
     */
    handle_priority = function (e) {
        row_handler.update_row('priority', $(this));
        return false;
    };

    /**
     * Changing task status
     * @requires global reference to row_handler
     */
    handle_status = function (e) {
        row_handler.update_row('status', $(this));
    };

    /**
     * Delete task, main or mini
     * @requires global reference to row_handler
     */
    handle_delete = function (e) {
        row_handler.update_row('delete', $(this));
        return false;
    };

    /**
     * Undelete task, main or mini
     * @requires global reference to row_handler
     */
    handle_undelete = function (e) {
        row_handler.update_row('undelete', $(this));
        return false;
    };

    /**
     * Editable, main
     * @requires global reference to url_handler
     */
    handle_editable_click = function (e) {
        if ($(this).hasClass('g')) {
            var id = $(this).attr('href').substr(3);
            url_handler.url_update_hash(PARAMS.group, id, true);
            return false;
        } else if (!e.ctrlKey) {
            window.location.href = $(this).attr('href');
            return false;
        }
    };

    /**
     * Share with someone else
     * @requires global reference to row_handler
     */
    handle_follow_action = function (e) {
        if ($(this).is(':checked')) {
            row_handler.update_row('follower_add', $(this));
        } else {
            row_handler.update_row('follower_remove', $(this));
        }
    };

    /**
     * Changing main tabs
     * @requires global reference to url_handler
     */
    $('.tabs .icon', LISTS[0]).click(function () {
        var type = parseInt(url_handler.
            get_url_param(PARAMS.type, $(this).children('a').attr('href')),
            10);
        url_handler.url_update_hash(PARAMS.type, type);
        return false;
    });

    /**
     * Changing mini tabs
     * @requires global reference to list_handler
     */
    $('.tabs .icon', LISTS[1]).click(function () {
        var type = parseInt(url_handler.
            get_url_param(PARAMS.minitype,
                $(this).children('a').attr('href')), 10);
        list_handler.expect(1);
        list_handler.expect_what = type;
        list_handler.get_lists();
        return false;
    });

    /**
     * Changing groups
     * @requires global reference to url_handler
     */
    $('.title a', LISTS[0]).live('click', function () {
        url_handler.url_update_hash(PARAMS.mainpage, 1, true);
        return false;
    });

    /**
     * Groups update the url hash
     * @requires global reference to url_handler
     */
    handle_change_group = function (e) {
        var group = parseInt(url_handler
            .get_url_param(PARAMS.group, $(this).attr('href')), 10);
        url_handler.url_update_hash(PARAMS.group, group, true);
        return false;
    };

    /**
     * Window resize calls the resize function
     * @requires global reference to url_handler
     */
    $(window).resize(function () {
        clearTimeout(list_handler.resize_timeout);
        list_handler.resize_timeout = setTimeout(list_handler.resize,
            TIMEOUTS.resize);
    });

    /**
     * Share with someone else
     * @requires global reference to row_handler
     * @requires global reference to list_handler
     */
    this.handle_plan_action = function (e) {
        var target = $(this);
        if (e.shiftKey) {
            modal_handler.show_prompt(
                'Type date and press ENTER: ' +
                '<input type="text" name="plan_custom" />',
                'plan',
                'keyup',
                function (ev) {
                    // enter
                    if (ev.keyCode === 13) {
                        list_handler.plan($(this).val());
                        TEMPLATES.modal.children('.jqmClose').click();
                        row_handler.update_row('plan', target);
                    }
                }
            );
            return false;
        }
        row_handler.update_row('plan', target);
        return false;
    };

    // init stuff
    $('input[name="search"]', LISTS[0]).bind('keyup', handle_search_action);
    LISTS[0].appendTo('#content');
    LISTS[1].appendTo('#content');
    this.refresh_timeout = setInterval(this.refresh_all,
        TIMEOUTS.refresh);
    this.per_page();
    $('.loading').each(function () {
        $(this).height($(this).parent().height() + 'px');
    });
}
