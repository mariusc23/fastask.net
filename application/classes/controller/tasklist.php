<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';
    private $user = null;

    /**
     * Index action, lists tasks
     */
    public function action_index() {
        // get the content
        $view = $this->template->content = View::factory('tasklist/index');
        $view->tasks = array();
        $view->pager = '';

        // get the content
        /*
        $tasks = ORM::factory('task')
             ->order_by('priority','asc')
             ->order_by('due','asc')
             ->limit(1)
             ->offset(100)
             ->find_all()
        ;
        $columns = $tasks[0]->list_columns();
        print '<pre>';
        foreach ($columns as $k => $v) {
            print "$k -> {$tasks[0]->$k}\n";
        }
        print "user -> {$tasks[0]->user->nick}\n";
        print "followers -> \n";
        $columns = $tasks[0]->user->list_columns();
        foreach ($tasks[0]->followers->find_all() as $follower) {
            foreach ($columns as $k => $v) {
                print "    $k -> {$follower->$k}\n";
            }
        }
        //print_r($tasks[0]->followers);
        print '</pre>';
        die;
        */
    }

    /**
     * Lists tasks given a page and number of items per page
     */
    public function action_t() {
        $this->request->headers['Content-Type'] = 'application/json';
        $this->auto_render = FALSE;
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
                    'nick' => $follower->nick,
                );
            }

            $json_task['group'] = null;
            $groups = $task->groups
                ->where('user_id', '=', $this->user->id)
                ->find_all();
            // only expecting one group atm
            foreach ($groups as $group) {
                $json_task['group'] = array(
                    'id' => $group->id,
                    'name' => $group->name,
                );
                break;
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
            // created by me AND followed by me
            return DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->join('task_group')
                    ->on('task_group.task_id', '=', 'tasks.id')
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
                ->execute('default')->get('count');
            ;
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
                ->execute('default')->get('count');
        }
    }


    public function get_tasks($params, $pagination) {
        $yesterday = date(DATE_MYSQL_FORMAT, strtotime('yesterday 00:00'));
        if (isset($params['g']) && intval($params['g'])) {
            $g_id = $params['g'];
            // my tasks are:
            // created by me AND followed by me
            return ORM::factory('task')
                ->distinct(true)
                ->join('follow_task')
                    ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->on('follow_task.task_id', '=', 'tasks.id')
                ->join('task_group')
                    ->on('task_group.task_id', '=', 'tasks.id')
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
        $this->user = new Model_User(1);//Auth::instance()->get_user();
        $this->template->user = $this->user;
        $this->template->model = 'tasklist';
        $this->template->action = Request::instance()->action;
   }
}
