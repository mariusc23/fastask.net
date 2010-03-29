<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';
    private $user = null;
    private $sphinxclient = null;

    /**
     * Index action
     */
    public function action_index() {
        if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != 'on')) {
            $this->request->redirect(URL::site('/', 'https'));
        }
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

        $type = 0;
        if (isset($_GET['t'])) {
            $type = intval($_GET['t']);
        }

        $per_page = 0;
        if (isset($_GET['n'])) {
            $per_page = intval($_GET['n']);
        }
        if ($per_page <= 0) {
            $per_page = TASKS_PER_PAGE;
        }
        $pl_per_page = 0;
        if (isset($_GET['m'])) {
            $pl_per_page = intval($_GET['m']);
        }
        if ($pl_per_page <= 0) {
            $pl_per_page = TASKS_PER_PAGE;
        }

        $count = 0;
        $tasks = array();
        if (isset($_GET['s'])) {
            extract($this->search($_GET['s'], $per_page));
        } else if (isset($_GET['ep']) && $_GET['ep']) {
            $count = $this->get_count($_GET);
        }

        // create pagination object
        if ($count) {
            $pagination = Pagination::factory(array(
                'current_page'   => array('source' => 'query_string', 'key' => 'p', 'output' => 'hash'),
                'total_items'    => $count,
                'items_per_page' => $per_page,
            ));
        }

        if ($count && !isset($_GET['s'])
            && isset($_GET['ep']) && $_GET['ep']) {
            $tasks = $this->get_tasks($_GET, $pagination)->as_array();
        }

        $json = array('tasks' => array());

        $planner_tasks = array();
        $planner_count = 0;
        if (isset($_GET['eu']) && $_GET['eu']) {
            $planner_count = $this->get_min_count('planner');

            $planner_pagination = Pagination::factory(array(
                'current_page'   => array('source' => 'query_string', 'key' => 'u', 'output' => 'hash'),
                'total_items'    => $planner_count,
                'items_per_page' => $pl_per_page,
            ));
            $planner_tasks = $this->get_min_tasks($planner_pagination, 'planner')->as_array();
        }
        $trash_tasks = array();
        $trash_count = 0;
        if (isset($_GET['ev']) && $_GET['ev']) {
            $trash_count = $this->get_min_count('trash');

            $trash_pagination = Pagination::factory(array(
                'current_page'   => array('source' => 'query_string', 'key' => 'v', 'output' => 'hash'),
                'total_items'    => $trash_count,
                'items_per_page' => $pl_per_page,
            ));
            $trash_tasks = $this->get_min_tasks($trash_pagination, 'trash')->as_array();
        }

        if (!isset($tasks[0])
            && !isset($planner_tasks[0])
            && !isset($trash_tasks[0])) {
            $this->request->status = 404;
            $json['error'] = 'No tasks found';
            $this->request->response = json_encode($json);
            return ;
        }

        $tasks = array_merge($tasks, $planner_tasks, $trash_tasks);


        $task_controller = new Controller_Task($this->request);
        $columns = $tasks[0]->list_columns();
        foreach ($tasks as $task) {
            $json_task = $task_controller->jsonify($task, $columns);
            $json['tasks'][] = $json_task;
        }
        if ($count) {
            $json['pager'] = $pagination->render();
        }
        if ($planner_count) {
            $json['pl_pager'] = $planner_pagination->render();
        }
        if ($trash_count) {
            $json['tr_pager'] = $trash_pagination->render();
        }
        $group_controller = new Controller_Group($this->request);
        $json = array_merge(
            $json,
            $group_controller->json_groups($type)
        );

        $this->request->response = json_encode($json);
    }

    public function get_count($params) {
        $yesterday = time() - SECONDS_IN_DAY;
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
                ->where('planned', '=', 0)
                ->where('group_id','=', $g_id)
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
                        ->where('planned', '=', 0)
                        ->where('status', '=', 0)
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
                        ->where('planned', '=', 0)
                        ->where('status', '=', 0)
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
                ->where('planned', '=', 0)
                ->where('status', '=', 0)
                ->execute()->get('count');
        }
    }


    public function get_tasks($params, $pagination) {
        $yesterday = time() - SECONDS_IN_DAY;
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
                ->where('planned', '=', 0)
                ->where('group_id','=', $g_id)
                ->order_by('status','asc')
                ->order_by('priority','asc')
                ->order_by('due','asc')
                ->limit($pagination->items_per_page)
                ->offset($pagination->offset)
                ->find_all();
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
                        ->where('planned', '=', 0)
                        ->where('status', '=', 0)
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                        ->limit($pagination->items_per_page)
                        ->offset($pagination->offset)
                        ->find_all();
                case 2:
                    // command center, my tasks assigned to others
                    return ORM::factory('task')
                        ->distinct(true)
                        ->join('follow_task')
                            ->on('follow_task.task_id', '=', 'tasks.id')
                        ->where('user_id', '=', $this->user->id)
                        ->where('follower_id', '!=', $this->user->id)
                        ->where('trash', '=', 0)
                        ->where('planned', '=', 0)
                        ->where('status', '=', 0)
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                        ->limit($pagination->items_per_page)
                        ->offset($pagination->offset)
                        ->find_all();
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
                        ->find_all();
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
                ->where('planned', '=', 0)
                ->where('status', '=', 0)
                ->order_by('status','asc')
                ->order_by('priority','asc')
                ->order_by('due','asc')
                ->limit($pagination->items_per_page)
                ->offset($pagination->offset)
                ->find_all();
        }
    }

    /**
     * Planner count. Used for pagination
     */
    public function get_min_count($for = 'planner') {
        switch ($for) {
            case 'planner':
                return DB::select(DB::expr('COUNT(id) AS count'))
                    ->from('tasks')
                    ->distinct(true)
                    ->join('follow_task')
                        ->on('follow_task.task_id', '=', 'tasks.id')
                    ->where('follower_id', '=', $this->user->id)
                    ->where('trash', '=', 0)
                    ->where('status', '=', 0)
                    ->where('planned', '=', 1)
                    ->execute()->get('count');
            case 'trash':
                return DB::select(DB::expr('COUNT(id) AS count'))
                    ->from('tasks')
                    ->distinct(true)
                    ->join('follow_task')
                        ->on('follow_task.task_id', '=', 'tasks.id')
                    ->where('follower_id', '=', $this->user->id)
                    ->where('trash', '=', 1)
                    ->execute()->get('count');
        }
    }

    /**
     * Planner tasks
     */
    public function get_min_tasks($pagination, $for = 'planner') {
        switch ($for) {
            case 'planner':
                return ORM::factory('task')
                    ->distinct(true)
                    ->join('follow_task')
                        ->on('follow_task.task_id', '=', 'tasks.id')
                    ->where('follower_id', '=', $this->user->id)
                    ->where('trash', '=', 0)
                    ->where('status', '=', 0)
                    ->where('planned', '=', 1)
                    ->order_by('status', 'asc')
                    ->order_by('priority', 'asc')
                    ->limit($pagination->items_per_page)
                    ->offset($pagination->offset)
                    ->find_all();
            case 'trash':
                return ORM::factory('task')
                    ->from('tasks')
                    ->distinct(true)
                    ->join('follow_task')
                        ->on('follow_task.task_id', '=', 'tasks.id')
                    ->where('follower_id', '=', $this->user->id)
                    ->where('trash', '=', 1)
                    ->order_by('lastmodified', 'desc')
                    ->limit($pagination->items_per_page)
                    ->offset($pagination->offset)
                    ->find_all();
        }
    }

    public function search($query, $per_page) {
        require_once(APPPATH.'classes/sphinxapi.php');
        $search_query = $query ? $query : '';
        $search_offset = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $search_offset = $per_page * ($search_offset - 1);
        if (!$search_offset) $search_offset = 0;

        if ($search_query !== '') {
            $search_query = mb_convert_encoding($search_query, 'UTF-8', 'auto');
            $search_query = str_replace(array(' OR ',' AND ',' NOT '), array(' | ',' & ',' !'), $search_query);
        } else {
            return array('count' => 0, 'tasks' => array());
        }

        $this->sphinxclient = new SphinxClient();
        $this->sphinxclient->SetServer(SPHINX_HOST, SPHINX_PORT);
        $this->sphinxclient->SetLimits($search_offset, $per_page, SPHINX_MAXRESULTS);
        //$this->sphinxclient->SetMatchMode(SPH_MATCH_EXTENDED2);
        //$this->sphinxclient->SetRankingMode(SPHINX_RANKER);
        $this->sphinxclient->SetSortMode(SPH_SORT_EXTENDED, 'status asc priority asc planned asc due asc');
        $this->sphinxclient->SetArrayResult(true);
        $this->sphinxclient->SetFilter('followers', array($this->user->id));

        $results = $this->sphinxclient->Query(
            mb_ereg_replace('-', '\-', $search_query), SPHINX_INDEX);
        $count = 0;
        if (isset($results['matches'])) {
            $count = $results['total'];
            $results = $results['matches'];

            $tasks = array();
            if (isset($results)) foreach ($results as $sphinx_task) {
                $task = new Model_Task($sphinx_task['id']);
                $task->due = TIMESTAMP_PLANNED + 1;
                $tasks[] = $task;
            }
        }
        if (!$count) {
            return array('count' => 0, 'tasks' => array());
        }
        return compact('count', 'tasks');
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
