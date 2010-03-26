<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Task extends Controller {
    private $user = null;

    /**
     * adds or removes a follower to a task
     */
    public function action_share() {
        $id = $this->request->param('id');
        $task = new Model_Task($id);
        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        if (!$_POST || !isset($_POST['u']) || !intval($_POST['u'])) {
            $this->request->status = 400;
            return ;
        }
        $u_id = intval($_POST['u']);

        $user = ORM::factory('user')->where('id', '=', $u_id)->find();

        // if not found
        if (!$user->loaded()) {
            $this->request->status = 404;
            return ;
        }

        // already has the follower, bad request
        // adding a user
        if (isset($_POST['r']) && $_POST['r']) {
            if ($task->has('followers', $user)) {
                $this->request->status = 400;
                return ;
            }

            if (!$task->add('followers', $user)) {
                $this->request->status = 500;
                return ;
            }
        } else {
        // removing a user
            if (!$task->has('followers', $user)) {
                $this->request->status = 400;
                return ;
            }

            $count = DB::select(DB::expr('COUNT(follower_id) AS count'))->from('follow_task')
                ->where('task_id', '=', $id)
                ->execute('default')->get('count');

            if ($count <= 1) {
                $this->request->status = 400;
                return ;
            }
            if (!$task->remove('followers', $user)) {
                $this->request->status = 500;
                return ;
            }
        }
        $this->request->response = '{}';
    }

    /**
     * updates priority
     */
    public function action_pri() {
        $id = $this->request->param('id');
        $task = new Model_Task($id);
        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        $task->priority = ($task->priority + 1) % 3 + 1;

        // save it
        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = json_encode(array(
            'priority' => $task->priority,
        ));
    }

    /**
     * updates status
     */
    public function action_s() {
        $id = $this->request->param('id');
        $task = new Model_Task($id);
        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        $task->status = 1 - $task->status;
        if (!in_array($task->status, array(0, 1))) {
            $task->status = 0;
        }

        // save it
        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = json_encode(array(
            'status' => $task->status,
        ));
    }

    /**
     * delete task
     */
    public function action_d() {
        if (!$this->user) {
            //Request::instance()->redirect('user/login');
        }

        $id = $this->request->param('id');
        $task = new Model_Task($id);

        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        $task->trash = 1;
        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = '{}';
    }


    /**
     * updates text
     */
    public function action_text() {
        if (!$this->user) {
            //Request::instance()->redirect('user/login');
        }

        $id = $this->request->param('id');
        $task = new Model_Task($id);

        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        $post = new Validate($_POST);
        $post
            ->rule('text', 'min_length', array(10))
            ->rule('text', 'max_length', array(1500))
            ->filter(TRUE, 'trim')
        ;
        if (!$post->check()) {
            $this->request->status = 400;
            return ;
        }
        $text = $post['text'];

        $t_arr = preg_split("/^[^\s]+:\s/u", $text, 2);

        $this->remove_groups($task);
        // task has group
        if (isset($t_arr[1])) {
            $text = trim($t_arr[1]);
            $t_arr[0] = trim($t_arr[0]);
            $group = ORM::factory('group')->where('name', '=', $t_arr[0])->find();

            // if not found, create group
            if (!$group->loaded()) {
                $group_controller = new Controller_Group($this->request);
                $group = $group_controller->_add(array(
                    'name' => $t_arr[0],
                ));
                if (!$group || !$task->add('groups', $group)) {;
                    $this->request->status = 500;
                    return ;
                }
            } else {
            // otherwise, just add it to the task
                if (!$task->add('groups', $group)) {;
                    $this->request->status = 500;
                    return ;
                }
            }
        } // end if task has group

        $task->text = $text;
        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $task->reload();
        $task->text = Model_Task::format_text_out($task, $this->user);
        $group_controller = new Controller_Group($this->request);
        $json = array_merge(
            array('text' => $task->text,
                'group' => Model_Task::get_group($task, $this->user)),
            $group_controller->my_json_groups()
        );
        $this->request->response = json_encode($json);
    }


    /**
     * updates due
     */
    public function action_due() {
        if (!$this->user) {
            //Request::instance()->redirect('user/login');
        }

        $id = $this->request->param('id');
        $task = new Model_Task($id);

        $this->request->headers['Content-Type'] = 'application/json';

        // error if not found
        if (!$task->loaded()) {
            $this->request->status = 404;
            return ;
        }

        if (!isset($_POST['due'])) {
            $this->request->status = 400;
            return ;
        }
        $task->due = Model_Task::format_due_in($_POST['due']);
        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        // reload the task
        $task->reload();
        $task->due = Model_Task::format_due_out($task->due);
        $this->request->response = json_encode(array(
            'due' => $task->due,
        ));
    }

    public function action_index()
    {
        $this->request->response = 'hello, world!';
    }


    /**
     * Removes user's groups for a task
     */
    protected function remove_groups($task) {
        $success = true;
        $task_groups = $task->groups
            ->where('user_id', '=', $this->user->id)
            ->find_all();

        foreach ($task_groups as $group) {
            // skip if not my own
            if ($group->user_id != $this->user->id) {
                continue;
            }
            // otherwise remove it from this task
            $task->remove('groups', $group);
            $success = Controller_Group::check_used($group);
        }
        return $success;
    }

    public function before() {
        $this->user = new Model_User(1);//Auth::instance()->get_user();
    }
}
