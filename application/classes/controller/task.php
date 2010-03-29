<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Task extends Controller {
    private $user = null;
    private $task = null;
    private $id = null;

    public function action_add() {
        if ($this->request->status != 200) {
            return ;
        }
        $json = array();
        if (!$_POST ||
            (!isset($_POST['add']) && !isset($_POST['plan']))) {
            $this->request->status = 400;
            $json['errors'][] = 'Must be adding or planning.';
            $this->request->response = json_encode($json);
            return ;
        }
        // validate data first
        $post = new Validate($_POST);
        $post
            ->rule('text', 'min_length', array(10))
            ->rule('text', 'max_length', array(1500))
            ->rule('text', 'min_length', array(5))
            ->rule('priority', 'range', array(1, 3))
            ->filter(TRUE, 'trim')
        ;

        if (!$post->check()) {
            $json['errors'][] = 'Invalid data posted.';
            $json['errors_post'][] = $post['errors'];
            $this->request->response = json_encode($json);
            $this->request->status = 400;
            return ;
        }

        if (!$_POST['follower'] || !is_array($_POST['follower'])
            || (count($_POST['follower']) == 1 &&
                !in_array($this->user->id, $_POST['follower']))
            ) {
            $json['errors'][] = 'You must be assigned to the '
                . 'tasks you create so you can see them.';
            $this->request->response = json_encode($json);
            $this->request->status = 400;
            return ;
        }

        $type = 0;
        if (isset($_POST['t'])) {
            $type = intval($_POST['t']);
        }

        // create task
        $task = new Model_Task;
        $task->group_id = 0;

        if (isset($_POST['plan'])) {
            $task->due = 0;
        } else {
            $task->due = Model_Task::format_due_in(trim($_POST['due']));
        }
        $task->planned = ($task->due < TIMESTAMP_PLANNED) ? 1 : 0;

        $task->priority = $post['priority'];
        $task->user_id = $this->user->id;
        $task->status = 0;
        $task->trash = 0;
        $task->created = time();
        $task->num_followers = 0;

        $group = $this->handle_text($task, $post['text']);
        $group_arr = array();
        if ($group) {
            $group_arr = array( 'group' => array(
                'id' => $group->id,
                'name' => $group->name,
            ));
        }

        // use array of follower ids
        $followers = array();
        foreach ($_POST['follower'] as $f_id) {
            $follower = new Model_User(intval($f_id));
            if ($follower->loaded()) {
                $followers[] = $follower;
                $task->num_followers++;
            }
        }

        if (!$task->save()) {
            $this->request->status = 500;
            return ;
        }

        foreach ($followers as $follower) {
            $task->add('followers', $follower);
        }

        $group_controller = new Controller_Group($this->request);
        $task->reload();
        $this->task = $task;
        $json = array_merge($group_arr,
            array('id' => $this->task->id, 'planned' => $this->task->planned),
            $group_controller->json_groups($type)
        );
        $this->request->response = json_encode($json);
    }

    /**
     * adds or removes a follower to a task
     */
    public function action_share() {
        if ($this->request->status != 200) {
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
            if ($this->task->has('followers', $user)) {
                $this->request->status = 400;
                return ;
            }

            if (!$this->task->add('followers', $user)) {
                $this->request->status = 500;
                $this->request->response = json_encode(array(
                    'error' => $user->username . 'is already following this task',
                ));
                return ;
            } else {
                $this->task->num_followers++;
                // save it
                if (!$this->task->save()) {
                    $this->request->status = 500;
                    return ;
                }
            }
        } else {
        // removing a user
            if (!$this->task->has('followers', $user)) {
                $this->request->status = 400;
                return ;
            }

            if ($this->task->num_followers <= 1) {
                $this->request->status = 400;
                return ;
            }
            if (!$this->task->remove('followers', $user)) {
                $this->request->status = 500;
                return ;
            } else {
                $this->task->num_followers--;
                // save it
                if (!$this->task->save($this->id)) {
                    $this->request->status = 500;
                    return ;
                }
            }
        }
        $this->request->response = '{}';
    }

    /**
     * updates priority
     */
    public function action_pri() {
        if ($this->request->status != 200) {
            return ;
        }
        $this->task->priority = ($this->task->priority + 1) % 3 + 1;

        // save it
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = json_encode(array(
            'priority' => $this->task->priority,
        ));
    }

    /**
     * updates status
     */
    public function action_s() {
        if ($this->request->status != 200) {
            return ;
        }
        $this->task->status = 1 - $this->task->status;
        if (!in_array($this->task->status, array(0, 1))) {
            $this->task->status = 0;
        }

        // save it
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = json_encode(array(
            'status' => $this->task->status,
        ));
    }

    /**
     * delete task
     */
    public function action_d() {
        if ($this->request->status != 200) {
            return ;
        }
        $json = array('task' => array());
        if (isset($_GET['undo'])) {
            $this->task->trash = 0;
        } else {
            $this->task->trash = 1;
        }
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }

        $columns = $this->task->list_columns();
        $json['task'] = $this->jsonify($this->task, $columns);
        $this->request->response = json_encode($json);
    }


    /**
     * updates text
     */
    public function action_text() {
        if ($this->request->status != 200) {
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

        $type = 0;
        if (isset($_POST['t'])) {
            $type = intval($_POST['t']);
        }

        $group = $this->handle_text($this->task, $post['text']);
        $group_arr = array();
        if ($group) {
            $group_arr = array( 'group' => array(
                'id' => $group->id,
                'name' => $group->name,
            ));
        }
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }
        $this->task->text = Model_Task::format_text_out($this->task, $this->user);
        $group_controller = new Controller_Group($this->request);
        $json = array_merge($group_arr,
            array('text' => $this->task->text),
            $group_controller->json_groups($type)
        );
        $this->request->response = json_encode($json);
    }


    /**
     * plans a task
     */
    public function action_plan() {
        if ($this->request->status != 200) {
            return ;
        }
        // default to today
        $due = time();// + SECONDS_IN_DAY; //tomorrow
        $this->task->due = $due;
        if (isset($_POST['due'])) {
            $due = $_POST['due'];
            $this->task->due = Model_Task::format_due_in($due);
        }
        $this->task->planned = ($this->task->due < TIMESTAMP_PLANNED) ? 1 : 0;
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }
        // reload the task
        $this->task->reload();
        $due_out = Model_Task::format_due_out($this->task);
        $json = array(
            'due' => $this->task->due,
            'due_out' => $due_out,
        );
        $json['planned'] = $this->task->planned;
        $this->request->response = json_encode($json);
    }

    /**
     * updates due
     */
    public function action_due() {
        if ($this->request->status != 200) {
            return ;
        }
        if (!isset($_POST['due'])) {
            $this->request->status = 400;
            return ;
        }
        $this->task->due = Model_Task::format_due_in($_POST['due']);
        $this->task->planned = ($this->task->due < TIMESTAMP_PLANNED) ? 1 : 0;
        if (!$this->task->save($this->id)) {
            $this->request->status = 500;
            return ;
        }
        // reload the task
        $this->task->reload();
        $due_out = Model_Task::format_due_out($this->task);
        $this->request->response = json_encode(array(
            'due' => $this->task->due,
            'due_out' => $due_out,
            'planned' => $this->task->planned,
        ));
    }

    public function action_index()
    {
        if ($this->request->status != 200) {
            return ;
        }
        $this->request->response = 'hello, world!';
    }


    public function jsonify($task, $columns) {
        $json_task = array();
        Model_Task::format_task($task, $this->user);

        foreach ($columns as $k => $v) {
            $json_task[$k] = $task->$k;
        }
        $json_task['due_out'] = $task->due_out;

        if ($task->trash == 1) {
            $json_task['trash'] = 1;
        }

        $json_task['followers'] = array();
        foreach ($task->followers->find_all() as $follower) {
            $json_task['followers'][] = array(
                'id' => $follower->id,
                'username' => $follower->username,
            );
        }

        if ($task->group_id > 0) {
            $json_task['group'] = array(
                'id' => $task->group->id,
                'name' => $task->group->name,
            );
        }
        return $json_task;
    }

    /**
     * Parses the text and creates a group. Also removes a group if
     * the old task's group does not belong to any other task.
     */
    public function handle_text(&$task, $text) {
        $t_arr = explode(': ', $text, 2);
        $group_id = 0;
        if ($task->user_id != $this->user->id) {
            // cannot change group if not owner
            $t_arr = array($text);
            $group_id = $task->group_id;
        }
        // task has group
        $group_arr = array();
        $group_controller = new Controller_Group($this->request);
        if (isset($t_arr[1])) {
            $text = trim($t_arr[1]);
            $t_arr[0] = trim($t_arr[0]);
            $group = ORM::factory('group')
                ->where('user_id', '=', $this->user->id)
                ->where('name', '=', $t_arr[0])
                ->find();
            // if not found, create group
            if (!$group->loaded()) {
                $group = $group_controller->_add(array(
                    'name' => $t_arr[0],
                ));
                if (!$group) {
                    $this->request->status = 500;
                    return ;
                }
                $group->num_tasks++;
            }

            if (!$group->save()) {
                $this->request->status = 500;
                return ;
            }
            $group_id = $group->id;
        } // end if task has group
        if ($task->group_id != $group_id) {
            // group has changed
            if ($task->group_id) {
                Controller_Group::remove_if_unused($task->group);
            }
            $task->group_id = $group_id;
        }
        $task->text = $text;
        if (isset($group)) {
            return $group;
        } else {
            return null;
        }
    }

    public function before() {
        if (Request::instance()->action == 'index') return ;

        $this->request->headers['Content-Type'] = 'application/json';
        $this->user = Auth::instance()->get_user();
        if (!$this->user) {
            $this->request->status = 403;
            $this->request->response = json_encode(array(
                'error' => 'Permission denied. You must be logged in.'
            ));
            return ;
        }

        if (Request::instance()->action == 'add') return ;

        // must be logged in to do anything
        $this->id = $this->request->param('id');
        $this->task = new Model_Task($this->id);
        // error if not found
        if (!$this->task->loaded()) {
            $this->request->status = 404;
            $this->request->response = json_encode(array(
                'error' => 'Task not found.',
            ));
            return ;
        }

        // error if not following
        if (!$this->task->has('followers', $this->user)
            && $this->task->user_id != $this->user->id) {
            $this->request->status = 403;
            $this->request->response = json_encode(array(
                'error' => 'Permission denied. You must be the owner '
                    + 'or assigned to this task.',
            ));
            return ;
        }
    }
}
