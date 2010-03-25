/*****************************************************************************/
/*
/* Common
/*
/*****************************************************************************/

var
/*-------------- CONSTANTS --------------*/
      WORK_BOX_CLASS = 'work_box'
    , WORK_BOX_TITLE = $('.' + WORK_BOX_CLASS + ' .title').html()
    , USER_ID = $('.menu .user a').attr('rel')
    , USER_NAME = $('.menu .user a').text()
    , SAVE_TASK_URL = $('#ajax_save_task').text()
    , LOAD_TASK_URL = $('#ajax_load_tasklist').text()
    , TASK_DEFAULT_REMIND = $('.' + WORK_BOX_CLASS + ' input[name="remind"]').val()
    , TASK_DEFAULT_DUE    = $('.' + WORK_BOX_CLASS + ' input[name="due"]')   .val()
    , SPINWHEEL = $('<div class="spin"></div>')
;

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
    var   taskRef = $(this).next().children()
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
    var   form_data = $('.' + WORK_BOX_CLASS + ' form').serialize()
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

$(document).ready(function() {
    /**
     * Init
     */
});