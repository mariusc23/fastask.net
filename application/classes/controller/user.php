<?php defined('SYSPATH') or die('No direct script access.');
class Controller_User extends Controller {
    private $user = null;

    /**
     * Lists users id and nickname in JSON format
     */
    public function action_l() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        $users = ORM::factory('user')
             ->order_by('nick', 'asc')
             ->find_all()
        ;

        $json = array('users' => array());
        foreach ($users as $user) {
            $json_user = array();

            $json_user['id'] = $user->id;
            $json_user['nick'] = $user->nick;

            $json['users'][] = $json_user;
        }

        $this->request->response = json_encode($json);
    }

    public function before() {
        $this->user = new Model_User(1);//Auth::instance()->get_user();
    }
}
