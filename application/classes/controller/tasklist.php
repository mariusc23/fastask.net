<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';

    /**
     * Index action, lists tasks
     * 
     * Also handles pagination.
     */
    public function action_index() {
        // count items
        $count = DB::select(DB::expr('COUNT(id) AS count'))->from('tasks')->execute('default')->get('count');

        // create pagination object
        $pagination = Pagination::factory(array(
            'current_page'   => array('source' => 'query_string', 'key' => 'p'),
            'total_items'    => $count,
            'items_per_page' => TASKS_PER_PAGE,
        ));

        // get the content
        $view = $this->template->content = View::factory('tasklist/index');
        $view->tasks = ORM::factory('task')->order_by('id','desc')
             ->limit($pagination->items_per_page)
             ->offset($pagination->offset)
             ->find_all()
        ;

        // render the pager
        $view->pager = $pagination->render();
    }

    public function before() {
        parent::before();
        $this->template->user = Auth::instance()->get_user();
        $this->template->model = 'tasklist';
        $this->template->action = Request::instance()->action;
   }
}
