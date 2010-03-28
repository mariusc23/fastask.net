<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';
    private $user = null;

    /**
     * Index action
     */
    public function action_index() {
        // get the content
        $view = $this->template->content = View::factory('tasklist/index');
        $view->tasks = array();
        $view->pager = '';
    }

    /**
     * Lists tasks given a page and number of items per page
     */
    public function action_t() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;

        // must be logged in
        if (!$this->user) {
            $this->request->status = 403;
            return ;
        }

        $id = $this->request->param('id');

        if (!$id) {
            $this->request->status = 400;
            return ;
        }

        $per_page = 0;
        if (isset($_GET['n'])) {
            $per_page = intval($_GET['n']);
        }
        if ($per_page <= 0) {
            $per_page = TASKS_PER_PAGE;
        }

        $count = $this->get_count($_GET);

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p', 'output' => 'hash'),
            'total_items'    => $count,
            'items_per_page' => $per_page,
        ));

        $tasks = $this->get_tasks($_GET, $pagination);

        $json = array('tasks' => array());

        if (!isset($tasks[0])) {
            $this->request->status = 404;
            $this->request->response = json_encode($json);
            return ;
        }

        $columns = $tasks[0]->list_columns();
        foreach ($tasks as $task) {
            $json_task = array();
            Model_Task::format_task($task, $this->user);

            foreach ($columns as $k => $v) {
                $json_task[$k] = $task->$k;
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

            $json['tasks'][] = $json_task;
        }
        $json['pager'] = $pagination->render();
        $group_controller = new Controller_Group($this->request);
        $json = array_merge(
            $json,
            $group_controller->my_json_groups()
        );

        $this->request->response = json_encode($json);
    }

    public function get_count($params) {
        $yesterday = date(DATE_MYSQL_FORMAT, strtotime('yesterday 00:00'));
        if (isset($params['g']) && intval($params['g'])) {
            $g_id = $params['g'];
            // my tasks are:
            // ONLY followed by me
            return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('trash', '=', 0)
                ->where('follower_id', '=', $this->user->id)
                ->where('due', '>', DATE_PLANNED)
                ->where('group_id','=', $g_id)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->execute()->get('count');
            ;
        } elseif (isset($params['t']) && $params['t']) {
            switch (intval($params['t'])) {
                case 1:
                    // assignments
                    return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('user_id', '!=', $this->user->id)
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
                        ->execute()->get('count');
                case 2:
                    // command center, my tasks assigned to others
                    return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('user_id', '=', $this->user->id)
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
                        ->execute()->get('count');
                case 3:
                    // archive
                    return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('trash', '=', 0)
                        ->where('follower_id', '=', $this->user->id)
                        ->where('status', '=', 1)
                        ->execute()->get('count');
                    ;
                default:
                    return null;
            }
        } else {
            // count items
            return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('trash', '=', 0)
                ->where('follower_id', '=', $this->user->id)
                ->where('due', '>', DATE_PLANNED)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->execute()->get('count');
        }
    }


    public function get_tasks($params, $pagination) {
        $yesterday = date(DATE_MYSQL_FORMAT, strtotime('yesterday 00:00'));
        if (isset($params['g']) && intval($params['g'])) {
            $g_id = $params['g'];
            // my tasks are:
            // ONLY followed by me
            return ORM::factory('task')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('trash', '=', 0)
                ->where('follower_id', '=', $this->user->id)
                ->where('due', '>', DATE_PLANNED)
                ->where('group_id','=', $g_id)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->order_by('status','asc')
                ->order_by('priority','asc')
                ->order_by('due','asc')
                ->limit($pagination->items_per_page)
                ->offset($pagination->offset)
                ->find_all()
            ;
        } elseif (isset($params['t']) && $params['t']) {
            switch (intval($params['t'])) {
                case 1:
                    // assignments
                    return ORM::factory('task')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('user_id', '!=', $this->user->id)
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
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                        ->limit($pagination->items_per_page)
                        ->offset($pagination->offset)
                        ->find_all();
                case 2:
                    // command center, tasks assigned to others
                    // ** only difference between this and my tasks
                    // ** is the follower_id != $this->user->id
                    return ORM::factory('task')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('user_id', '=', $this->user->id)
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
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                        ->limit($pagination->items_per_page)
                        ->offset($pagination->offset)
                        ->find_all()
                    ;
                case 3:
                    // archive stuff that is done AND followed by me
                    return ORM::factory('task')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('trash', '=', 0)
                        ->where('follower_id', '=', $this->user->id)
                        ->where('status', '=', 1)
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                        ->limit($pagination->items_per_page)
                        ->offset($pagination->offset)
                        ->find_all()
                    ;
                default:
                    return null;
            }
        } else {
            // my tasks are:
            // created by me AND followed by me
            return ORM::factory('task')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->where('trash', '=', 0)
                ->where('follower_id', '=', $this->user->id)
                ->where('due', '>', DATE_PLANNED)
                ->and_where_open()
                    ->where('status', '=', 0)
                    ->or_where_open()
                        ->where('status', '=', 1)
                        ->where('lastmodified', '>', $yesterday)
                    ->or_where_close()
                ->and_where_close()
                ->order_by('status','asc')
                ->order_by('priority','asc')
                ->order_by('due','asc')
                ->limit($pagination->items_per_page)
                ->offset($pagination->offset)
                ->find_all()
            ;
        }
    }

    public function before() {
        parent::before();
        $this->user = Auth::instance()->get_user();
        if (!$this->user) {
            Request::instance()->redirect('user/login');
        }
        $this->template->user = $this->user;
        $this->template->model = 'tasklist';
        $this->template->action = Request::instance()->action;
   }
}
