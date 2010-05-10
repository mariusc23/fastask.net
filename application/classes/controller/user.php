<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User controller, handles actions on the user model.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 */
class Controller_User extends Controller_Template
{
    public $template = 'base/template';
    protected $user = null;

    /**
     * Checks username is available.
     */
    public function action_available()
    {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = false;
        // error if not found
        if (!$_POST['username'] || !Validate::alpha($_POST['username'])
            || !Validate::min_length($_POST['username'], 3)
        ) {
            $this->request->status = 400;
            return;
        }

        $user = ORM::factory('user')
            ->where('username', '=', $_POST['username'])
            ->find();

        $json = array('available' => 1);
        if ($user->id) {
            $json['available'] = 0;
        }

        $this->request->response = json_encode($json);
    }

    /**
     * Lists users with id and username in JSON format.
     * Current user is always first.
     */
    public function action_l()
    {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = false;

        $users = $this->user->followers->find_all();

        $json = array('users' => array());
        // current user comes first
        $json_user = array();
        $json_user['id'] = $this->user->id;
        $json_user['username'] = $this->user->username;
        $json_user['current'] = 1;
        $json_user['name'] = $this->user->name;
        $json_user['email'] = $this->user->email;
        $json_user['logins'] = $this->user->logins;
        $json_user['last_login'] = $this->user->last_login;
        $json['users'][] = $json_user;

        foreach ($users as $user) {
            $json_user = array();

            $json_user['id'] = $user->id;
            $json_user['username'] = $user->username;
            $json['users'][] = $json_user;
        }

        $this->request->response = json_encode($json);
    }

    /**
     * Logs the user in, and redirects to the main fastask page,
     * or to referrer.
     */
    public function action_login()
    {
        $view = $this->template->content = View::factory('user/login');
        // if user already logged in
        $referer = $_REQUEST['r'] ? $_REQUEST['r'] : URL::site('in');
        if (Auth::instance()->logged_in() != 0) {
            Request::instance()->redirect($referer);
        }


        // if posted data
        if ($_POST) {
            $user = ORM::factory('user');

            // check auth
            if ($user->login($_POST)) {
                Request::instance()->redirect($referer);
            } else {
                $view->errors = $_POST->errors('login');
                $this->template->title = 'Error logging in';
                return;
            }
        }

        $this->template->title = 'Log in';
    }


    /**
     * Logs the user out, and redirects to the homepage, or to referrer.
     */
    public function action_logout()
    {
        // log out
        Auth::instance()->logout();
        $referer = $_REQUEST['r'] ? $_REQUEST['r']
            : URL::site('user/login');
        Request::instance()->redirect($referer);
        Request::instance()->redirect($referer);
    }

    /**
     * Registers the user.
     */
    function action_register()
    {
        // if user logged in
        if (Auth::instance()->logged_in() != 0) {
            Request::instance()->redirect(URL::site('user/login'));
        }

        $view = $this->template->content = View::factory('user/register');

        $view->invited = false;

        if ($_REQUEST['code']) {
            $invitation = ORM::factory('invitation')
                ->where('code', '=', $_REQUEST['code'])
                ->find();
            if ($invitation->loaded()) {
                $view->invited = true;
            }
        }
 
        // if posted data
        if ($_POST && $view->invited) {
            $user = ORM::factory('user');
 
            // validate data
            $post = $user->validate_create($_POST);

            if ($post->check()) {
                // feed the data
                $user->values($post);

                // and save it
                $user->save();

                // delete the invitation
                $invitation->delete();

                // add the login role
                $login_role = new Model_Role(array('name' => 'login'));
                $user->add('roles', $login_role);

                // sign the user in
                Auth::instance()->login($post['username'], $post['password']);

                // show their account
                Request::instance()->redirect('in');
            } else {
                // show the registration errors
                $view->errors = $post->errors('register');
                $this->template->title = 'Error registering';
                return;
            }
        }
        if ($view->invited) {
            $view->invite_email = $invitation->email;
        }
        $this->template->title = 'Register';
    }

    /**
     * Update user info from profile
     */
    function action_update()
    {
        // if user not logged in
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = false;
        // must be logged in
        if (Auth::instance()->logged_in() !== true) {
            $this->request->status = 403;
            $json['error'] = 'Access denied. Must be logged in.';
            $this->request->response = json_encode($json);
            return;
        }

        // if posted data
        if ($_POST) {
            $user = $this->user;
 
            // validate data
            $post = $user->validate_change($_POST);
            $pass_check = 1;
            if (isset($_POST['current_password'])
                && $_POST['current_password']
            ) {
                if (Auth::instance()->login(
                        $this->user->username,
                        trim($_POST['current_password']))) {
                    $pass_check = 2;
                } else {
                    $pass_check = 0;
                }
            }
 
            if ($post->check() && $pass_check) {
                // feed the data
                $user->name = $post['name'];
                $user->email = $post['email'];

                // change password
                if ($pass_check == 2) {
                    $pass_array = array(
                        'password' => trim($_POST['password']),
                        'password_confirm' => trim($_POST['password_confirm'])
                    );
                    if (!$user->change_password($pass_array, true)) {
                        $this->request->status = 400;
                        $json['error'] = 'Could not change your password. ' .
                            'Make sure you typed it twice. Case sensitive.';
                        $this->request->response = json_encode($json);
                        return;
                    }
                }
                // and save it
                if (!$user->save()) {
                    $this->request->status = 500;
                    $json['error'] = 'Failed to process your request.';
                    $this->request->response = json_encode($json);
                    return;
                }
            } else {
                // show the registration errors
                $this->request->status = 400;
                $json['error'] = 'Failed to process your request.';
                $json['errors_post'] = $post->errors();
                $this->request->response = json_encode($json);
            }
        } else {
            // show the registration errors
            $this->request->status = 400;
            $json['error'] = 'No data posted.';
            $this->request->response = json_encode($json);
        }
    }

    /**
     * Reset/forgot password
     */
    function action_reset()
    {
        // must be logged out
        if (Auth::instance()->logged_in() !== 0) {
            $this->request->redirect(URL::site('user/logout'));
        }
        $view = $this->template->content = View::factory('user/reset');
        $this->template->okjs = true;
        $view->title = 'Oops!';

        if (isset($_GET['code'])) {
            // check code
            $notification = ORM::factory('notification')
                ->where('code', '=', $_GET['code'])
                ->find();
            if (!$notification->loaded()) {
                $view->message = 'Sorry. Could not find any notification with
                that code. If you pasted the URL, make sure you didn\'t
                make a mistake.';
                return;
            }
            $view->title = $notification->user->username;
            $view->code = $notification->code;
            return;
        } elseif (isset($_POST['username']) && $_POST['username']) {
            //generate hash code and send email
            $user = ORM::factory('user')
                ->where('username', '=', trim($_POST['username']))
                ->find();
            if (!$user->loaded()) {
                // could not find user
                $view->message = 'Could not find any user named ' .
                    $_POST['username'] . '</br>Please <a href="' .
                    URL::site('user/reset') . '">go back</a> and try again.
                    <a href="http://craciunoiu.net/contact">Contact us</a> 
                    if you do not receive an email after 24 hours.</div>';
                return;
            }
            $notification = ORM::factory('notification')
                ->where('user_id', '=', $user->id)
                ->where('type', '=', NOTIFICATION_PASSWORD_RESET)
                ->find();
            if (!$notification->loaded()) {
                $notification = new Model_Notification();
                $notification->user_id = $user->id;
                $notification->type = NOTIFICATION_PASSWORD_RESET;
                $notification->code = sha1(session_id()) . sha1(rand() .
                    session_id() . rand()) . time();
                $notification->params =  '';
                $notification->lastmodified = time();
                $notification->save();
            }
            // send email
            include APPPATH . 'config/email_reset.php';
            mail(
                // to
                $user->email,
                // subject
                $subject,
                // message
                $message,
                $additional_headers
            );
            $view->title = 'Thank you, '. $user->username;
            $view->message = 'Your request has been processed.</br>
                You should receive an email with further instructions in a
                moment. <a href="http://craciunoiu.net/contact">Contact us</a>
                if you do not receive an email after 24 hours.</div>';
            return;
        } elseif (isset($_POST['password']) && isset($_POST['code'])) {
            // change password
            $pass_array = array(
                'password' => trim($_POST['password']),
                'password_confirm' => trim($_POST['password_confirm'])
            );
            $notification = ORM::factory('notification')
                ->where('code', '=', $_POST['code'])
                ->find();
            if (!$notification->loaded()) {
                $view->message = "Sorry. Could not find any notification with
                    that code. If you pasted the URL, make sure you didn't
                    make a mistake.";
                return;
            }
            if (!$notification->user->change_password($pass_array, true)) {
                $view->message = 'Could not change your password.
                    Make sure you typed it twice. It must be at least
                    6 characters long and it is case sensitive. Hit back in
                    your browser.';
                return;
            }
            Auth::instance()->login($notification->user->username,
                                    $_POST['password']);
            $notification->delete();
            // show their account
            Request::instance()->redirect('in');
        } else {
            $view->title = '';
            $view->start = true;
            return;
        }

    }


    /**
     * Allow logged in users to share with people.
     */
    function action_s()
    {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = false;
        // must be logged in
        if (Auth::instance()->logged_in() == 0) {
            $this->request->status = 403;
            return;
        }
        // must be an ajax request and send data
        if (!Request::$is_ajax || !$_POST['user']) {
            $this->request->status = 400;
            return;
        }

        $user = ORM::factory('user')
            ->or_where_open()
                ->or_where('username', '=', $_POST['user'])
                ->or_where('email', '=', $_POST['user'])
            ->or_where_close()
            ->find();

        if (!$user->loaded()) {
            $this->request->status = 404;
            return;
        }

        // sharing with self is not cool
        if ($user->id === $this->user->id) {
            $this->request->response = 'self';
            $this->request->status = 400;
            return;
        }

        // if already sharing with
        if ($this->user->has('followers', $user)) {
            $this->request->response = 'already';
            $this->request->status = 400;
            return;
        }

        $notification = ORM::factory('notification')
            ->where('user_id', '=', $this->user->id)
            ->where('follower_id', '=', $user->id)
            ->where('type', '=', NOTIFICATION_USER_SHARE)
            ->find();

        if ($notification->loaded()) {
            if ($notification->params === NOTIFICATION_USER_BLOCK) {
                $this->request->response = 'blocked';
            } else {
                $this->request->response = 'exists';
            }
            $this->request->status = 400;
            return;
        }

        $notification = new Model_Notification();
        $notification->user_id = $this->user->id;
        $notification->follower_id = $user->id;
        $notification->type = NOTIFICATION_USER_SHARE;
        $notification->code = sha1(time() . session_id()) . time();
        $notification->params = '';
        $notification->lastmodified = time();
        $notification->save();

        // send email
        include APPPATH . 'config/email_share.php';

        mail(
            // to
            $user->email,
            // subject
            $subject,
            // message
            $message,
            $additional_headers
        );


        $json = array();
        $json['username'] = $user->username;
        $this->request->response = json_encode($json);
    }


    /**
     * Accepting share requests
     */
    function action_share()
    {
        $view->user = $this->user;
        $view = $this->template->content = View::factory('user/share');
        $this->template->okjs = true;
        $this->template->title = 'Accept sharing invitation';

        if ($_GET['code']) {
            $notification = ORM::factory('notification')
                ->where('code', '=', $_REQUEST['code'])
                ->where('type', '=', NOTIFICATION_USER_SHARE)
                ->find();
            if ($notification->loaded()) {
                $view->valid = true;
                $user_1 = new Model_User($notification->user_id);
                $user_2 = new Model_User($notification->follower_id);
                if (!$user_1->loaded() || !$user_1->loaded()) {
                    $this->request->status = 500;
                    $view->valid = false;
                }
                $view->name = $user_2->username;
                $view->name_with = $user_1->username;
                if ($_GET['block']) {
                    $this->template->title = 'Block user from sharing';
                    $view->block = true;
                    $notification->params = NOTIFICATION_USER_BLOCK;
                    $notification->save();
                } else {
                    $view->block = false;
                    $user_1->add('followers', $user_2);
                    $user_2->add('followers', $user_1);
                    $notification->delete();
                }
            }
        }
    }


    /**
     * Allow logged in users to invite people.
     */
    function action_invite()
    {
        // must be logged in
        if (Auth::instance()->logged_in() == 0) {
            $this->request->redirect(URL::site('user/login?r=user/invite'));
        }

        // must be admin
        $role = new Model_Role(2);
        if (!$this->user->has('roles', $role)) {
            $this->request->redirect(URL::site('in'));
        }

        $user = $this->user;
        $view = $this->template->content = View::factory('user/invite');
        $this->template->okjs = true;
        $this->template->title = 'Invite';

        if ($_POST['email'] && filter_var($_POST['email'],
            FILTER_VALIDATE_EMAIL)) {
            //generate hash code and send email
            $invitation = new Model_Invitation();
            $invitation->user_id = $user->id;
            $invitation->email = $_POST['email'];
            $invitation->code = sha1(rand() . md5(session_id()) . rand()) .
                                    time();
            $invitation->save();
            // send email
            include APPPATH . 'config/email_invite.php';

            mail(
                // to
                $user->email,
                // subject
                $subject,
                // message
                $message,
                $additional_headers
            );
            $this->template->title = 'Invite sent';
            $view->title = 'Thank you, '. $user->username;
            $view->message = 'The invite has been sent.';
            return;
        } elseif ($_POST['email']) {
            $this->template->title = 'Error sending invite';
            $view->title = 'Oops!';
            $view->message = 'Could not send the invite.
                Double check the email address.';
            return;
        } else {
            $view->user = $user;
            return;
        }

    }


    /**
     * Executes before actions. Set up user, model, action.
     */
    public function before()
    {
        parent::before();
        $this->user = Auth::instance()->get_user();
        $this->template->user = $this->user;
        $this->template->model = 'user';
        $this->template->action = Request::instance()->action;
    }
}
