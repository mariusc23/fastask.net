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
        $count = DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')->execute('default')->get('count');

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p'),
            'total_items'    => $count,
            'items_per_page' => $per_page,
        ));

        // get the content
        $tasks = ORM::factory('task')
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
