<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Task extends Controller {
    private $user = true;//Auth::instance()->get_user();

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

        $task->priority = ($task->priority + 1) % 3;

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

        if (!isset($_POST['text'])) {
            $this->request->status = 500;
            return ;
        }
        $task->text = $_POST['text'];

        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $this->request->response = json_encode(array(
            'text' => $task->text,
        ));
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
            $this->request->status = 500;
            return ;
        }
        $task->due = Model_Task::format_due_in($_POST['due']);

        if (!$task->save($id)) {
            $this->request->status = 500;
            return ;
        }
        $task->due = Model_Task::format_due_out($task->due);
        $this->request->response = json_encode(array(
            'due' => $task->due,
        ));
    }

    public function action_index()
    {
        $this->request->response = 'hello, world!';
    }

} // End Welcome
