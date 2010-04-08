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
    'follower_remove': '/task/share/'
};

CLASSES = {
    'loadrow': 'loadbar'
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

// how often to refresh autocomplete
    'autorefresh': 90251,
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
    'assignmentindent': 5
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
    'trash'
];
// the html version, used in e.g. groups
TITLES_HTML = [
    '<a href="#t=0">' + TITLES_PLAIN[0] + '</a>',
    '<a href="#t=1">' + TITLES_PLAIN[1] + '</a>',
    '<a href="#t=2">' + TITLES_PLAIN[2] + '</a>',
    '<a href="#t=3">' + TITLES_PLAIN[3] + '</a>',
    '<a href="#s=1">' + TITLES_PLAIN[4] + '</a>',
    '<a href="#l=1">' + TITLES_PLAIN[5] + '</a>',
    '<a href="#l=2">' + TITLES_PLAIN[6] + '</a>'
];

// used with ajax, for performing calls
PATHS = {
    'users': '/user/l/',
    'list': '/tasklist/t/',
    'share': '/user/s/',
    'groups': '/group/l/'
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
    'modal_trigger': $('<a href="#" class="modal_trigger jqModal hide">Show error</a>'),

    'modal': $('<div class="jqmWindow modal_dialog"> \
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

// collaborate template
    'collaborate': $('<li><a href="#">Add collaborator</a></li>')
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
    'priority': '3'
}

HELP = '<div class="help" id="h-top">' +
'<h1>Welcome to Tasklist! Need some help?</h1>' +
'<div class="text">' +
"<p>We're excited that you are using our product. Below you will find a description of the available features and examples.</p>" +


'<h2>Sections</h2><p>' +
'Tasklist only has one page! It is split into five areas of interest. Here they are:' +
'<ul>' +
'<li><a href="#h-main">The main box</a>, the largest of them all, contains four tabs, a search feature.</li>' +
'<li><a href="#h-mini">The mini box</a>, contains the planner and trash.</li>' +
'<li><a href="#h-noti">The notification area</a>, shows you feedback about important actions.</li>' +
'<li><a href="#h-work">The work box</a>, from which you can create and plan tasks.</li>' +
'<li><a href="#h-prof">Your profile</a>, from which you can change your name, email, or password.</li>' +
'</ul>' +
'Other sections:' +
'<ul><li><a href="#h-due">Due dates</a>, how Tasklist understands and displays due dates.</li>' +
'<li><a href="#h-groups">Groups</a>, how to create and efficiently use groups.</li></ul>' +
'</p>' +


'<h2 id="h-main">The main box</h2> <a href="#h-top">back to top</a><p>' +

'<h3>The top bar</h3><p>' +
'<p>First, look at the top bar, on top of the tasks. To the top left, notice 4 tabs, in order: <ul>' +
'<li><em>My tasks</em> &mdash; what you created and are also following.</li>' +
'<li><em>Assignments</em> &mdash; what other people have shared with you.</li>' +
'<li><em>Command center</em> &mdash; what you created for other people.</li>' +
'<li><em>Archive</em> &mdash; what you are following and is marked done.</li>' +
'</ul></p>' +
'<p>The center area shows your currently active tab. If you have created groups, hover over this area to filter your tasks by groups when viewing. Note that filtering by groups only brings up the tasks in that group <em>in the currently viewed tab</em>.</p>' +
'<p>The rightmost area is for search. Simply type a word or two and the results will appear below.</p>' +

'<h3>The main list and pagination</h3><p>' +
'<p><em>See also</em> <a href="#h-due">due dates</a> and <a href="#h-groups">groups</a></p>' +
'<p>The list is the most frequently used part of Tasklist. You can edit your tasks, change their priority, share or unshare them with others, delete them, and so on.</p>' +
'<p>For the first 3 tabs, tasks are listed first in order of priority, and then by due date. For the archive (4th tab), they are ordered by completion time.</p>' +
'<p>While you are editing a row, its background will change color temporarily, to indicate whether your action has completed successfully. If your actions will change the order or number of tasks in your list, the list will refresh after a few seconds of inactivity.</p>' +
'<p>To mark a task as complete (or unmark it), click the <em>checkbox</em> to the left.</p>' +
'<p>To change its priority, click the <em>circle icon</em> to the left of the text.</p>' +
'<p>To edit a task or its due date, simply click on that area. Press <em>Enter</em> when you are done editing, or <em>Esc</em> to cancel your changes. If you click on the wrong task, no worries &mdash; you can navigate in the list by pressing the arrors: Up/Down or Ctrl + Alt + Left/Right.</p>' +
'<p>To delete a task, click the red <em>delete icon</em> to the right. To undo your deletion, click on the <em>green</em> icon</p>' +
'<p>To share a task, hover over the rightmost <em>sharing icon</em> and select who you want to share with.</p>' +
'<p>To navigate through a longer list, use the pagination at the bottom.</p>' +


'<h2 id="h-mini">The mini box</h2> <a href="#h-top">back to top</a><p>' +

'<h3>The mini top bar</h3><p>' +
'<p>This mini top bar has 2 tabs: planner and trash.<ul>' +
'<li><em>Planner</em> &mdash; tasks you are following without a due date set.</li>' +
'<li><em>Trash</em> &mdash; deleted tasks. These are periodically deleted by Tasklist (usually after 30 days).</li>' +
'</ul></p>' +

'<h3>The mini list</h3> <a href="#h-top">back to top</a><p>' +
'<p>This list offers fewer functionality than the main one.</p>' +
'<p>If you are viewing the planner, you can plan tasks or delete them. To plan a task for today, just click on it. To plan it for a different date, use <em>Shift + Click</em> instead.</p>' +
'<p>You can also delete or undelete tasks by clicking on the red or green delete icon, respectively.</p>' +


'<h2 id="h-noti">The notification area</h2> <a href="#h-top">back to top</a><p>' +
'<p>This area gives you feedback about your actions. Some of the notifications included are: <ul>' +
'<li><em>Task added</em> &mdash; when you create a task from the <em><a href="#h-work">work box</a></em>.</li>' +
'<li><em>Task created in planner</em> &mdash; when you plan a new task.</li>' +
'<li><em>Password changed/Profile updated</em> &mdash; when you change your password successfully or update your profile.</li>' +
'<li><em>Failure to update profile</em> &mdash; this usually happens if you try to change your password and type incorrectly. Your password must be at least 5 characters.</li>' +
'<li><em>Task deleted/undeleted</em> &mdash; when you delete/undelete a task.</li>' +
'</ul></p>' +


'<h2 id="h-work">The work box</h2> <a href="#h-top">back to top</a><p>' +
'<p>From here you can create and plan tasks. Fill out the fields and click <em>submit</em> or <em>plan</em>.</p>' +
'<p>The date field is fairly flexible and we are continuously looking for suggestions on improving its understanding of dates if you feel it is insufficient. <a href="#h-due">Read more about due dates</a>.</p>' +
'</div></div>';
