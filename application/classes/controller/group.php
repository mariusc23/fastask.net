<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Group extends Controller {
    private $user = null;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->user = Auth::instance()->get_user();
    }

    public function groups_counts($type = 0) {
        $counts = DB::select(DB::expr('COUNT(groups.id) AS count'))
            ->select(DB::expr('groups.id'))
            ->from('groups')
            ->join('tasks')
                ->on('tasks.group_id', '=', 'groups.id');
        $params = array('t' => $type);

        $fastask_controller = new Controller_Fastask($this->request);
        $fastask_controller->user = $this->user;
        $fastask_controller->orm_chain_tasks($counts, $params, false);

        $counts = $counts
            ->group_by('groups.id')
            ->execute();
        $counts_keyed = array();
        foreach ($counts as $count) {
            $counts_keyed[$count['id']] = $count['count'];
        }
        return $counts_keyed;
    }


    public function json_groups($type = 0) {
        $groups = ORM::factory('group')
            ->join('tasks')
                ->on('tasks.group_id', '=', 'groups.id');
        $params = array('t' => $type);

        $fastask_controller = new Controller_Fastask($this->request);
        $fastask_controller->user = $this->user;
        $fastask_controller->orm_chain_tasks($groups, $params, false);

        $groups = $groups
                ->order_by('name', 'asc')
            ->find_all();

        $groups_counts = $this->groups_counts($type);

        $json = array('groups' => array());
        $users = array($this->user->id => $this->user, );
        foreach ($groups as $group) {
            $json_group = array();

            $json_group['id'] = $group->id;
            $json_group['name'] = $group->name;
            $json_group['num_tasks'] = $groups_counts[$group->id];
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
    public function action_l() {
        if ($this->request->status != 200) {
            return;
        }
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        $json = array('results' => array());

        $groups = ORM::factory('group')
             ->where('user_id', '=', $this->user->id)
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
     * Deletes a group if no tasks are using it
     * Precondition: $group is an existing group
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
        $this->request->headers['Content-Type'] = 'application/json';
        // must be logged in
        if (!$this->user) {
            $this->request->status = 403;
            return ;
        }
    }
}
