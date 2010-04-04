<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Tasklist extends Controller_Template {
    public $template = 'base/template';
    public $user = null;
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

        $old_t = isset($_GET['t']) ? $_GET['t'] : 0;
        $count = array($old_t => 0);
        $tasks = array();
        if (isset($_GET['s'])) {
            extract($this->search($_GET['s'], $per_page));
        } else if (isset($_GET['ep']) && $_GET['ep']) {
            for ($t = 0; $t < 4; $t++) {
                $_GET['t'] = $t;
                $count[$t] = $this->get_count($_GET);
            }
        }
        $_GET['t'] = $old_t;

        // create pagination object
        if ($count[$old_t]) {
            $pagination = Pagination::factory(array(
                'current_page'   => array('source' => 'query_string', 'key' => 'p', 'output' => 'hash'),
                'total_items'    => $count[$old_t],
                'items_per_page' => $per_page,
            ));
        }

        if ($count[$old_t] && !isset($_GET['s'])
            && isset($_GET['ep']) && $_GET['ep']) {
            $tasks = $this->get_tasks($_GET, $pagination)->as_array();
        }

        $json = array('tasks' => array());

        $planner_count = $this->get_min_count('planner');
        $trash_count = $this->get_min_count('trash');

        $planner_tasks = array();
        $trash_tasks = array();
        if ($_GET['eu']) {
            if ($_GET['tr'] == 1) {
                $planner_pagination = Pagination::factory(array(
                    'current_page'   => array('source' => 'query_string', 'key' => 'u', 'output' => 'hash'),
                    'total_items'    => $planner_count,
                    'items_per_page' => $pl_per_page,
                ));
                $planner_tasks = $this->get_min_tasks($planner_pagination, 'planner')->as_array();
            } else {
                $trash_pagination = Pagination::factory(array(
                    'current_page'   => array('source' => 'query_string', 'key' => 'u', 'output' => 'hash'),
                    'total_items'    => $trash_count,
                    'items_per_page' => $pl_per_page,
                ));
                $trash_tasks = $this->get_min_tasks($trash_pagination, 'trash')->as_array();
            }
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
        if ($count[$old_t]) {
            $json['pager'] = $pagination->render();
        }
        if ($planner_tasks) {
            $json['pl_pager'] = $planner_pagination->render();
        } elseif ($trash_tasks) {
            $json['pl_pager'] = $trash_pagination->render();
        }
        $group_controller = new Controller_Group($this->request);
        $json['counts'] = $count;
        $json['counts_left'] = array($planner_count, $trash_count);
        //$json['cache_counts'] = $this->get_cache_counts();
        $json = array_merge(
            $json,
            $group_controller->json_groups($type)
        );

        $this->request->response = json_encode($json);

        // update cache
        //$this->set_cache_counts($json['counts'], $json['counts_left']);
    }

    public function get_count($params) {
        $tasks = DB::select(DB::expr('COUNT(id) AS count'))->from('tasks');
        $this->orm_chain_tasks($tasks, $params, false);
        $tasks = $tasks
            ->execute()->get('count');
        return $tasks;
    }


    public function get_tasks($params, $pagination) {
        $tasks = ORM::factory('task');
        $this->orm_chain_tasks($tasks, $params);
        $tasks = $tasks
            ->limit($pagination->items_per_page)
            ->offset($pagination->offset)
            ->find_all();
        return $tasks;
    }

    public function orm_chain_tasks(&$tasks, &$params, $order = true) {
        if (!isset($params['t'])) {
            $params['t'] = 0;
        }
        $tasks
            ->distinct(true)
            ->join('follow_task')
                ->on('follow_task.task_id', '=', 'tasks.id')
            ->where('trash', '=', 0)
            ->where('planned', '=', 0)
        ;
        if (isset($params['g']) && intval($params['g'])) {
            $g_id = $params['g'];
            $tasks = $tasks->where('group_id','=', $g_id);
        }
        switch (intval($params['t'])) {
            case 1:
                // assignments, others' tasks followed by me
                $tasks
                    ->where('status', '=', 0)
                    ->where('tasks.user_id', '!=', $this->user->id)
                    ->where('follower_id', '=', $this->user->id)
                ;
                if ($order) {
                    $tasks
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                    ;
                }
                break;
            case 2:
                // command center, my tasks assigned to others
                $tasks
                    ->where('status', '=', 0)
                    ->where('tasks.user_id', '=', $this->user->id)
                    ->where('follower_id', '!=', $this->user->id)
                ;
                if ($order) {
                    $tasks
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                    ;
                }
                ;
                break;
            case 3:
                // archive stuff that is done AND followed by me
                $tasks
                    ->where('status', '=', 1)
                    ->where('follower_id', '=', $this->user->id)
                ;
                if ($order) {
                    $tasks
                        ->order_by('lastmodified','desc')
                    ;
                }
                break;
            default:
                // my tasks are:
                // created by me AND followed by me
                $tasks
                        ->on('follow_task.follower_id', '=', 'tasks.user_id')
                    ->where('follower_id', '=', $this->user->id)
                    ->where('status', '=', 0)
                ;
                if ($order) {
                    $tasks
                        ->order_by('status','asc')
                        ->order_by('priority','asc')
                        ->order_by('due','asc')
                    ;
                }
                break;
        }
        return $tasks;
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
                    ->order_by('lastmodified', 'desc')
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

    public function get_cache_counts() {
        $user_cache = $this->user->caches
            ->where('key', '=', CACHE_COUNTS)
            ->find();
        if (!$user_cache->loaded()) {
            $user_cache = new Model_Usercache();
            $user_cache->user_id = $this->user->id;
            $user_cache->key = CACHE_COUNTS;
            $user_cache->value = serialize(array());
            $user_cache->save();
        }
        return unserialize($user_cache->value);
    }

    public function set_cache_counts($counts, $counts_left) {
        $counts_all = serialize(array_merge($counts, $counts_left));
        $user_cache = $this->user->caches
            ->where('key', '=', CACHE_COUNTS)
            ->find();
        if (!$user_cache->loaded()) {
            $user_cache = new Model_Usercache();
            $user_cache->user_id = $this->user->id;
            $user_cache->key = CACHE_COUNTS;
        }
        $user_cache->value = $counts_all;
        $user_cache->save();
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
        $count = array(0 => 0);
        if (isset($results['matches'])) {
            $count[0] = $results['total'];
            $results = $results['matches'];

            $tasks = array();
            if (isset($results)) foreach ($results as $sphinx_task) {
                $task = new Model_Task($sphinx_task['id']);
                $task->due = TIMESTAMP_PLANNED + 1;
                $tasks[] = $task;
            }
        }
        if (!$count[0]) {
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
