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


    public static function check_used($group) {
        $count = DB::select(DB::expr('COUNT(*) AS count'))->from('task_group')
            ->where('group_id', '=', $group->id)
            ->execute('default')->get('count');
        if ($count > 0) return true;

        // if no other task uses this group, delete it
        if (!$group->delete()) {
            return false;
        }
        return true;
    }
}
