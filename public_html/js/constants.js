/**
 * Contains constans used throughout the other js files
 * Each constant is preceded by a short description of use.
 * Most of these can be customized.
 * When changing something, check around this file for other instances affected
 */

/*-------------- PURE JS ----------------*/

// NOTES:
// for timeouts, unless stated, assume milliseconds
// for sizes, unless stated, assume pixels
SAVE = {
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

CLASSES = {
    'loadrow': 'loadbar',
};


TIMEOUTS = {
// delay for searching after last keypress
    'search': 500,

// delay for quick reloading a list after user interaction
    'changed': 5000,

// delay for autorefresh
    'refresh': 120000,

// delay for window resize
    'resize': 2000,

// delay for notification, hidden after this time
    'notifhide': 2500,

// called when trying to autocomplete
    'autocomplete': 400,
};

COUNTS = {
// minimum character count to trigger search
    'searchmin': 5,

// maximum number of notifications to keep/show at any time
    'notifmax': 5
};

PIXELS = {
// height for task row in list
    'rowheight': 30,

// height adjustment for main list, used to determine number of tasks
    'mainminus': 100,

// height adjustment for planner/trash, used to determine number of tasks
    'miniminus': 110,

// editable span width needs to be adjusted on main list
// when groups are read-only
    'assignmentwidth': 20,
// so does the text indent
    'assignmentindent': 5,
};

// separator for variables in the url hash
HASH_SEPARATOR = ';';

// plain titles for the lists, used in e.g. notifications
TITLES_PLAIN = [
    'my tasks',
    'assignments',
    'command',
    'archive',
    'search',
    'planner',
    'trash',
];
// the html version, used in e.g. groups
TITLES_HTML = [
    '<a href="#t=0">' + TITLES_PLAIN[0] + '</a>',
    '<a href="#t=1">' + TITLES_PLAIN[1] + '</a>',
    '<a href="#t=2">' + TITLES_PLAIN[2] + '</a>',
    '<a href="#t=3">' + TITLES_PLAIN[3] + '</a>',
    '<a href="#s=1">' + TITLES_PLAIN[4] + '</a>',
    '<a href="#l=1">' + TITLES_PLAIN[5] + '</a>',
    '<a href="#l=2">' + TITLES_PLAIN[6] + '</a>',
];

// used with ajax, for performing calls
PATHS = {
    'users': '/user/l/',
    'list': '/tasklist/t/',
    'groups': '/group/f/',
};

// url parameters (in hash)
PARAMS = {
    'mainpage': 'p',
    'minipage': 'u',
    'group': 'g',
    'type': 't',
    'minitype': 'l'
};

/*-------------- JQUERY -----------------*/

TEMPLATES = {
// shown when no tasks in list
    'notasks': $('<div class="notasks"> \
    No tasks are available. Create one using the box to the right. \
    </div>'),

// used to create the list of groups
    'groups': $('<ul></ul>'),
    'group': $('<li><a href="#"></a></li>'),

// used to create the list of followers
// this will be altered in listhandler.js on init
    'followers': $('<ul></ul>'),
    'follower': $('<li><label><input class="" type="checkbox" \
        name="follower[]" value=""/> <span></span></label></li>'),

// main row template
    'mainrow': $('<div class="row"> \
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
        </div>'),

// mini row template (for planner, trash)
    'minirow': $('<div class="row"> \
        <div class="td text"></div> \
        <div class="td"> \
            <a href="#"> </a> \
        </div> \
        <div class="td del"> \
            <a href="#"> </a> \
            <input type="hidden" value="" name="task_id"> \
            <input type="hidden" value="" name="user_id"> \
        </div> \
        </div>'),

// used to greate a single group in a row
    'rowgroup': $('<a href="#" class="g"></a>'),

// used to create an editable element
    'editable': $('<span class="editable"></span>'),

// the notifications box, used to create the main area
    'notifbox': $('<div class="notification"> \
        <div class="top"><span>Notifications</span> \
            <span class="adding">:: Adding...</span></div> \
        <ul></ul> \
    </div><!-- /.notification -->'),

// notifications with icons
    'notifs': [
        $('<li><span class="icon a"></span>\
            <span>Task added</span></li>'),
        $('<li><span class="icon d"></span>\
            <span>Task deleted</span></li>'),
        $('<li><span class="icon u"></span>\
            <span>Cannot unassign everyone</span></li>'),
        $('<li><span class="icon p"></span>\
            <span>Task moved to ' + TITLES_PLAIN[5]
            + '</span></li>'),
        $('<li><span class="icon s"></span>\
            <span>Password changed</span></li>'),
        $('<li><span class="icon e"></span>\
            <span>Task undeleted</span></li>')
    ],

// indicates if a notification is about to be added
// partially used to indicate that request is processing
    'indicator': $('.top .adding', this.NOTIF_BOX),

// modal window template
    'modal': $('<div class="jqmWindow modal_dialog"> \
        <a href="#" class="modal_trigger jqModal hide">Show error</a> \
        <a href="#" class="jqmClose">x</a> \
        <div class="text"></div> \
    </div>'),

// profile template
    'profile': $('\
    <div class="profile-box"> \
    <div class="loading"></div> \
    <h1 class="title"></h1> \
    <form action="/user/update" method="POST"> \
        <label class="name">Name: <br /> \
            <input type="text" name="name" value="" /> \
        </label> \
        <label class="email">Email: <br/> \
            <input type="text" name="email" value="" /> \
        </label> \
        <label class="change_password"><span class="lstep">\
            Change Password:</span><br/> \
            <input type="password" name="change_password" value="" /> \
            <input type="hidden" name="current_password" value="" /> \
            <input type="hidden" name="password" value="" /> \
            <input type="hidden" name="password_confirm" value="" /> \
            <a class="submit">next</a> \
            <span class="info-box"> \
                <span class="info">Enter current password.</span> \
                <span class="steps"><span class="on" href="#">1</span> &rsaquo; \
                <span href="#">2</span> &rsaquo; <span href="#">3</span></span> \
            </span> \
        </label> \
        <span class="links"> \
            <a class="save" href="#">save</a> \
            <a href="/user/logout">log out</a> \
            <a class="cancel" href="#">cancel</a> \
        </span> \
    </form> \
    </div><!-- profile-box -->'),

// workbox template
    'workbox': $('\
    <div class="work-box"> \
    <h1 id="wb" class="title">work box</h1> \
    <form action="/task/add" method="POST"> \
        <label class="text">Task: <br /> \
            <textarea rows="5" cols="10" name="text"></textarea> \
        </label> \
        <label class="due">Date: <br/> \
            <input type="text" name="due" value="+1d" /> \
            <span class="due-icon"></span> \
        </label> \
        <div class="share label">Sharing: <br/> \
            <span class="input"></span> \
            <span class="share-icon"></span> \
        </div> \
        <div class="priority label"><span>Priority:</span> \
            <input type="hidden" name="priority" value="3" /> \
            <span class="p p-1"><span class="img hi" alt="1"></span> High</span> \
            <span class="p p-2"><span class="img me" alt="2"></span> Medium</span> \
        </div> \
        <input type="submit" name="add" title="Add task" value="Add task" /> \
        <input type="submit" name="plan" title="Plan task" value="Plan task" /> \
        <a class="clear" href="#">clear</a> \
    </form> \
    </div><!-- work_box -->'),

// spinwheel template
    'spinwheel': $('<div class="spin"></div>'),
};

LISTS = [
// main list template
    $('<div class="task-box box" rel="0" id="main"> \
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
    </div><!-- /.task-box -->'),

// minibox template
    $('\
    <div id="mini" class="mini-box box" rel="1"> \
    <div class="loading"></div> \
    <h1 class="title">' + TITLES_PLAIN[5] + '</h1> \
    <div class="tabs"> \
        <div class="icon planner" title="stuff witout a due date"> \
            <a href="#l=1"></a><span class="c"></span></div> \
        <div class="icon trash" title="bleh, garbage"> \
            <a href="#l=2"></a><span class="c"></span></div> \
    </div> \
    <div class="table" cellspacing="0"> \
    </div> \
    </div><!-- mini-box -->')
];

WORKBOX = {
// default due date
    'due': '+1d',

// default priority
    'priority': '3',
}