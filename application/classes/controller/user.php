<?php defined('SYSPATH') or die('No direct script access.');
class Controller_User extends Controller_Template {
    public $template = 'base/template';
    private $user = null;

    /**
     * Checks user is available
     */
    public function action_available() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;
        if (!Request::$is_ajax) {
            $this->request->status = 400;
            return ;
        }

        // error if not found
        if (!isset($_POST['username'])
            || !preg_match(USERNAME_REGEX, $_POST['username'])) {
            $this->request->status = 400;
            return ;
        }

        $user = ORM::factory('user')
            ->where('username', '=', $_POST['username'])
            ->find()
        ;

        $json = array('available' => 1);
        if ($user->id) {
            $json['available'] = 0;
        }

        $this->request->response = json_encode($json);
    }

    /**
     * Lists users id and username in JSON format
     */
    public function action_l() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        $users = ORM::factory('user')
             ->order_by('username', 'asc')
             ->find_all()
        ;

        $json = array('users' => array());
        foreach ($users as $user) {
            $json_user = array();

            $json_user['id'] = $user->id;
            $json_user['username'] = $user->username;

            $json['users'][] = $json_user;
        }

        $this->request->response = json_encode($json);
    }

    public function action_login() {
        if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != 'on')) {
            $this->request->redirect(URL::site('user/login', 'https'));
        }
        $view = $this->template->content = View::factory('user/login');
        // if user already logged in
        if (Auth::instance()->logged_in() != 0){
            if ($_POST) Request::instance()->redirect($this->referer);
            $view->user = $this->template->user;
        }


        // if posted data
        if ($_POST) {
            $user = ORM::factory('user');

            // check auth
            if ($user->login($_POST)) {
                Request::instance()->redirect(URL::site('/', 'http'));
            } else {
                $view->errors = $_POST->errors('login');
                $this->template->title = 'Error logging in';
                return ;
            }
        }

        $this->template->title = 'Log in';
    }


    public function action_logout() {
        // log out
        Auth::instance()->logout();
        if (!isset($this->referer)) {
            $this->referer = '/';
        }
        Request::instance()->redirect($this->referer);
    }


    function action_register() {
        // if user already logged in
        if (Auth::instance()->logged_in() != 0){
            Request::instance()->redirect('user/login');
        }

        $view = $this->template->content = View::factory('user/register');
 
        // if posted data
        if ($_POST) {
            $user = ORM::factory('user');
 
            // validate data
            $post = $user->validate_create($_POST);
 
            if ($post->check()) {
                // feed the data
                $user->values($post);
 
                // and save it
                $user->save();
 
                // add the login role
                $login_role = new Model_Role(array('name' => 'login'));
                $user->add('roles', $login_role);
 
                // sign the user in
                Auth::instance()->login($post['username'], $post['password']);
 
                // show their account
                Request::instance()->redirect('/');
            } else {
                // show the registration errors
                $view->errors = $post->errors('register');
            }
        }
    }

    public function before() {
        parent::before();
        $this->user = Auth::instance()->get_user();
        $this->template->user = $this->user;
        $this->template->model = 'user';
        $this->template->action = Request::instance()->action;
   }
}
