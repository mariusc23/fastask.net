<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Task extends Controller {

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
        if ($task->save()) {
            $this->request->response = json_encode(array(
                'priority' => $task->priority,
            ));
            return ;
        } else {
            $this->request->status = 500;
        }
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
        if ($task->save()) {
            $this->request->response = json_encode(array(
                'status' => $task->status,
            ));
            return ;
        } else {
            $this->request->status = 500;
        }
    }

    public function action_index()
    {
        $this->request->response = 'hello, world!';
    }

} // End Welcome
