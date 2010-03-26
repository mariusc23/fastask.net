<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';

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

        // count items
        $yesterday = date(DATE_MYSQL_FORMAT, strtotime('yesterday 00:00'));
        $count = DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')
            ->distinct(true)
            ->join('follow_task')
               ->on('follow_task.follower_id', '=', 'tasks.user_id')
               ->on('follow_task.task_id', '=', 'tasks.id')
            ->where('trash', '=', 0)
            ->where('follower_id', '=', 1)
            ->and_where_open()
                ->where('status','=',0)
                ->or_where_open()
                    ->where('status','=','1')
                    ->where('lastmodified', '>', $yesterday)
                ->or_where_close()
            ->and_where_close()
            ->order_by('status','asc')
            ->order_by('priority','asc')
            ->order_by('due','asc')
            ->execute('default')->get('count');

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p', 'output' => 'hash'),
            'total_items'    => $count,
            'items_per_page' => $per_page,
        ));

        // my tasks are:
        // created by me AND followed by me
        $tasks = ORM::factory('task')
            ->distinct(true)
            ->join('follow_task')
               ->on('follow_task.follower_id', '=', 'tasks.user_id')
               ->on('follow_task.task_id', '=', 'tasks.id')
            ->where('trash', '=', 0)
            ->where('follower_id', '=', 1)
            ->where('due', '>', DATE_PLANNED)
            ->and_where_open()
                ->where('status','=',0)
                ->or_where_open()
                    ->where('status','=','1')
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
        $json = array('tasks' => array());
        $columns = $tasks[0]->list_columns();
        foreach ($tasks as $task) {
            $json_task = array();

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

            $json['tasks'][] = $json_task;
        }
        $json['pager'] = $pagination->render();

        $this->request->response = json_encode($json);
    }

    public function before() {
        parent::before();
        $this->template->user = Auth::instance()->get_user();
        $this->template->model = 'tasklist';
        $this->template->action = Request::instance()->action;
   }
}
