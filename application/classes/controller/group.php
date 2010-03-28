<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Group extends Controller {
    private $user = null;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->user = Auth::instance()->get_user();
    }

    public function json_groups($type = 0) {
        $yesterday = date(DATE_MYSQL_FORMAT, strtotime('today 00:00'));
        switch (intval($type)) {
        case 1:
            // assignments
            $groups = ORM::factory('group')
                ->distinct(true)
                ->join('tasks')
                    ->on('tasks.group_id', '=', 'groups.id')
                ->join('follow_task')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('tasks.user_id', '!=', $this->user->id)
                ->where('follower_id', '=', $this->user->id)
                ->where('trash', '=', 0)
                ->where('due', '>', DATE_PLANNED)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->order_by('name', 'asc')
                ->find_all()
            ;
            break;
        case 2:
            // command center
            $groups = ORM::factory('group')
                ->distinct(true)
                ->join('tasks')
                    ->on('tasks.group_id', '=', 'groups.id')
                ->join('follow_task')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('tasks.user_id', '=', $this->user->id)
                ->where('follower_id', '!=', $this->user->id)
                ->where('trash', '=', 0)
                ->where('due', '>', DATE_PLANNED)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->order_by('name', 'asc')
                ->find_all()
            ;
            break;
        case 3:
            // archive
            $groups = ORM::factory('group')
                ->distinct(true)
                ->join('tasks')
                    ->on('tasks.group_id', '=', 'groups.id')
                ->join('follow_task')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('follower_id', '=', $this->user->id)
                ->where('trash', '=', 0)
                ->where('status', '=', 1)
                ->order_by('name', 'asc')
                ->find_all()
            ;
            break;
        case 0:
        default:
            // just get my groups
            $groups = ORM::factory('group')
                ->distinct(true)
                ->join('tasks')
                    ->on('tasks.group_id', '=', 'groups.id')
                ->join('follow_task')
                    ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('tasks.user_id', '=', $this->user->id)
                ->where('follower_id', '=', $this->user->id)
                ->where('trash', '=', 0)
                ->where('due', '>', DATE_PLANNED)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->order_by('name', 'asc')
                ->find_all()
            ;
        }

        $json = array('groups' => array());
        $users = array($this->user->id => $this->user, );
        foreach ($groups as $group) {
            $json_group = array();

            $json_group['id'] = $group->id;
            $json_group['name'] = $group->name;
            $json_group['num_tasks'] = $group->num_tasks;
            $json_group['user_id'] = $group->user->id;
            if (!isset($users[$group->user->id])) {
                // find the user
                $g_user = new Model_User($group->user->id);
                if ($g_user->loaded()) {
                    $users[$group->user->id] = $g_user;
                }
            }
            // if there is a corresponding user, load the username
            if (isset($users[$group->user->id])) {
                $json_group['username'] = $users[$group->user->id]->username;
            }

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
    public static function remove_if_unused($group) {
        $group->num_tasks--;
        if ($group->num_tasks > 1) {
            if (!$group->save()) {
                return false;
            }
            return true;
        }
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
