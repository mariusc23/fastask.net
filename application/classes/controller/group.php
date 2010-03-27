<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Group extends Controller {
    private $user = null;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->user = Auth::instance()->get_user();
    }

    public function my_json_groups() {
        $groups = ORM::factory('group')
             ->where('user_id', '=', $this->user->id)
             ->order_by('name', 'asc')
             ->find_all()
        ;

        $json = array('groups' => array());
        foreach ($groups as $group) {
            $json_group = array();

            $json_group['id'] = $group->id;
            $json_group['name'] = $group->name;

            $json['groups'][] = $json_group;
        }
        return $json;
    }


    /**
     * Find groups starting with given letters
     */
    public function action_f() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        $json = array('results' => array());

        if (!isset($_POST['name']) || !$_POST['name']) {
            $this->request->status = 400;
            $this->request->response =
                json_encode(array_merge($json,
                    array('message' => 'Empty search')
                ));
            return ;
        }

        $groups = ORM::factory('group')
             ->where('user_id', '=', $this->user->id)
             ->where('name', 'LIKE', $_POST['name'] . '%')
             ->order_by('name', 'asc')
             ->find_all()
        ;

        foreach ($groups as $group) {
            $json_group = array();
            $json_group['name'] = $group->name . ': ';

            $json['results'][] = $json_group;
        }
        $this->request->response = json_encode($json);
    }


    /**
     * Lists groups, id and name in JSON format
     */
    public function action_l() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        $json = $this->my_json_groups();

        $this->request->response = json_encode($json);
    }

    /**
     * Adds a group
     * @param array $data array of (field_name, field_value) pairs
     * @return newly inserted group on success
     *      OR null on failure to save
     *      OR false on invalid supplied data
     */
    public function _add($data) {
        $post = new Validate($data);
        // validate data first
        $post
            ->rule('name', 'min_length', array(2))
            ->rule('name', 'max_length', array(50))
            ->rule('name', 'alpha_dash')
            ->filter(TRUE, 'trim')
        ;

        if ($post->check()) {
            $group = new Model_Group;
            $group->name = $post['name'];
            $group->user_id = $this->user->id;

            if ($group->save()) {
                return $group;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }


    /**
     * Precondition: $id is an integer
     * Deletes a group if no tasks are using it
     */
    public static function remove_if_unused($id) {
        $count = DB::select(DB::expr('COUNT(*) AS count'))->from('tasks')
            ->where('group_id', '=', $id)
            ->execute('default')->get('count');
        if ($count > 1) return true;
        $group = new Model_Group($id);
        // if no other task uses this group, delete it
        if (!$group->delete()) {
            return false;
        }
        return true;
    }

    public function before() {
        parent::before();
        // must be logged in
        if (!$this->user) {
            $this->request->status = 403;
            return ;
        }
    }
}
