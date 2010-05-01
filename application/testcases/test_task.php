<?php

/**
 * @group application
 * @group loggedin
 * @group task
 * @group task.general
 */
class TaskTest extends PHPUnit_Framework_TestCase {
    public $task = null;
    public $request = null;

    protected function setUp() {
        Request::instance()->action = 'add';
        $this->task = new Controller_Task(new Request('task/add'));
        $this->task->before();
    }

    /**
     * Sets $_POST data for adding tasks
     */
    function providerAdd() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value)
        */
        return array(
            array(array(
                    'plan' => 1,
                    'text' => 'Test planning for someone else.',
                    'priority' => '3',
                    'follower' => array('2'),
                  ),
                  array(
                    array('id', 69),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'plan' => 1,
                    'text' => 'Test planning.',
                    'priority' => '2',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 70),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test adding without due date.',
                    'priority' => '1',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 71),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test invalid due date, 1',
                    'due' => 'invalid',
                    'priority' => '3',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 72),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test invalid due date, 2',
                    'due' => '6 decades ago',
                    'priority' => '2',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 73),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test adding with due date.',
                    'due' => '+7d',
                    'priority' => '1',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 74),
                    array('planned', 0),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test assigning to someone else.',
                    'due' => '+7d',
                    'priority' => '1',
                    'follower' => array('2'),
                  ),
                  array(
                    array('id', 75),
                    array('planned', 0),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Test assigning to 2 people on command center.',
                    'due' => '+7d',
                    't' => '2',
                    'priority' => '1',
                    'follower' => array('1', '2'),
                  ),
                  array(
                    array('id', 76),
                    array('planned', 0),
                  ),
            ),
        );
    }

    /**
     * Test general adding.
     * @dataProvider providerAdd
     */
    function testAdd($post, $pairs) {
        $_POST = $post;
        $this->task->action_add();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        foreach ($pairs as $pair) {
            $this->assertSame(
                $json->$pair[0],
                $pair[1]
            );
        }
    }


    /**
     * Sets $_POST data for adding tasks in groups
     */
    function providerGroup() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value)
        */
        return array(
            array(array(
                    'plan' => 1,
                    'text' => 'paul_1: Test planning in group.',
                    'priority' => '1',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 77),
                    array('planned', 1),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'paul_1: Test adding in group.',
                    'due' => '+7d',
                    'priority' => '2',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 78),
                    array('planned', 0),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'paul_9: Test adding in new group.',
                    'due' => '+5d',
                    'priority' => '2',
                    'follower' => array('1'),
                  ),
                  array(
                    array('id', 79),
                    array('planned', 0),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'paul_9: Test adding in new group again.',
                    'due' => '+1d',
                    'priority' => '2',
                    'follower' => array('2'),
                  ),
                  array(
                    array('id', 80),
                    array('planned', 0),
                  ),
            ),
        );
    }

    /**
     * Test adding with groups.
     * @dataProvider providerGroup
     */
    function testGroup($post, $pairs) {
        $_POST = $post;
        $this->task->action_add();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        foreach ($pairs as $pair) {
            $this->assertSame(
                $json->$pair[0],
                $pair[1]
            );
        }
    }


    /**
     * Sets $_POST data for deleting tasks
     */
    function providerDelete() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value),
                tasks.id
        */
        return array(
            array(array(),
                  array(
                    array('id', 75),
                    array('trash', 1),
                  ),
                  75,
            ),
            array(array(
                    'undo' => '1',
                  ),
                  array(
                    array('id', 75),
                    array('trash', 0),
                  ),
                  75,
            ),
            array(array(
                  ),
                  array(
                    array('id', 77),
                    array('trash', 1),
                  ),
                  77,
            ),
            array(array(
                    'undo' => '1',
                  ),
                  array(
                    array('id', 76),
                    array('trash', 0),
                  ),
                  76,
            ),
        );
    }

    /**
     * Test delete.
     * @dataProvider providerDelete
     */
    function testDelete($post, $pairs, $id) {
        $_POST = $post;
        Request::instance()->action = 'd';
        $this->task = new Controller_Task(new Request('task/d/' . $id));
        $this->task->before();
        $this->task->action_d();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        foreach ($pairs as $pair) {
            $this->assertSame(
                $json->tasks[0]->$pair[0],
                $pair[1]
            );
        }
    }


    /**
     * Sets $_POST data for sharing tasks
     */
    function providerShare() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                tasks.id,
        */
        return array(
            array(array(
                    'u' => '2',
                    'a' => '1',
                  ),
                  73,
            ),
            array(array(
                    'u' => '2',
                    'a' => '1',
                  ),
                  79,
            ),
            array(array(
                    'u' => '1',
                  ),
                  76,
            ),
            array(array(
                    'u' => '2',
                  ),
                  73,
            ),
        );
    }

    /**
     * Test sharing.
     * @dataProvider providerShare
     */
    function testShare($post, $id) {
        $_POST = $post;
        Request::instance()->action = 'share';
        $this->task = new Controller_Task(new Request('task/share/' . $id));
        $this->task->before();
        $this->task->action_share();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $this->assertSame(
            $response->response,
            '{}'
        );
    }


    /**
     * Sets $_POST data for changing priority
     */
    function providerPriority() {
        /* format for each test:
            array(
                tasks.id,
                next priority, to check against
        */
        return array(
            array(70, 3),
            array(70, 1),
            array(70, 2),
            array(70, 3),
            array(71, 2),
        );
    }

    /**
     * Test priority.
     * @dataProvider providerPriority
     */
    function testPriority($id, $priority) {
        Request::instance()->action = 'pri';
        $this->task = new Controller_Task(new Request('task/pri/' . $id));
        $this->task->before();
        $this->task->action_pri();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $this->assertSame(
            json_decode($response->response)->priority,
            $priority
        );
    }


    /**
     * Sets $_POST data for toggling status.
     */
    function providerStatus() {
        /* format for each test:
            array(
                tasks.id,
                next status, to check against
        */
        return array(
            array(70, 1),
            array(70, 0),
            array(70, 1),
            array(71, 1),
        );
    }

    /**
     * Test status.
     * @dataProvider providerStatus
     */
    function testStatus($id, $status) {
        Request::instance()->action = 's';
        $this->task = new Controller_Task(new Request('task/s/' . $id));
        $this->task->before();
        $this->task->action_s();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $this->assertSame(
            json_decode($response->response)->status,
            $status
        );
    }


    /**
     * Sets $_POST data for changing the text
     */
    function providerText() {
        /* format for each test:
            array(
                $_POST data
                tasks.id,
                returned group
                returned groups contain (depends on type)
                returned groups do not contain (depends on type)
        */
        return array(
            array(array(
                    'text' => 'paul_2: Test changing to existing group.',
                    't' => 0,
                  ),
                  79,
                  'Test changing to existing group.',
                  array(2, 'paul_2'),
                  array('paul_1', 'paul_2'),
                  array(),
            ),
            array(array(
                    'text' => 'paul_1: Test changing to existing group.',
                    't' => 0,
                  ),
                  79,
                  'Test changing to existing group.',
                  array(1, 'paul_1'),
                  array('paul_1', 'paul_2'),
                  array(),
            ),
            array(array(
                    'text' => 'paul_10: Test changing to new group ' .
                        'and deleting old group.',
                    't' => 2,
                  ),
                  80,
                  'Test changing to new group and deleting old group.',
                  array(15, 'paul_10'),
                  array('paul_1', 'paul_10'),
                  array('paul_9'),
            ),
            // SECURITY: modifying the group for someone else's task
            array(array(
                    'text' => 'paul_11: Test changing group of task ' .
                        'owned by other user.',
                    't' => 1,
                  ),
                  57,
                  'paul_11: Test changing group of task owned by other user.',
                  array(null, null),
                  array('marius_1', 'marius_2'),
                  array('paul_11'),
            ),
        );
    }

    /**
     * Test editing text.
     * @dataProvider providerText
     */
    function testText($post, $id, $text, $new_group, $contains, $excludes) {
        $_POST = $post;
        Request::instance()->action = 'text';
        $this->task = new Controller_Task(new Request('task/text/' . $id));
        $this->task->before();
        $this->task->action_text();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        $this->assertSame(
            $json->text,
            $text
        );
        $this->assertSame(
            $json->group->id,
            $new_group[0]
        );
        $this->assertSame(
            $json->group->name,
            $new_group[1]
        );
        $group_names = array();
        foreach ($json->groups as $group) {
            $group_names[] = $group->name;
        }
        foreach ($contains as $group_name) {
            $this->assertContains(
                $group_name,
                $group_names
            );
        }
        $this->assertSame(
            array_intersect($excludes, $group_names),
            array()
        );
    }


    /**
     * Sets $_POST data for planning a task.
     */
    function providerPlan() {
        /* format for each test:
            array(
                $_POST data,
                tasks.id,
                due timestamp,
                due string,
                planned or not,
        */
        return array(
            array(array(),  // default planning
                  73,
                  time() + PLAN_DEFAULT_DELAY,
                  '7:0',
                  0,
            ),
            array(array('due' => '+1d'),
                  73,
                  time() + SECONDS_IN_DAY,
                  date('D', time() + SECONDS_IN_DAY),
                  0,
            ),
            array(array('due' => '-40yr'),
                  73,
                  0,
                  'plan',
                  1,
            ),
            array(array('due' => 'invalid'),
                  73,
                  0,
                  'plan',
                  1,
            ),
        );
    }

    /**
     * Test planning task.
     * @dataProvider providerPlan
     */
    function testPlan($post, $id, $due, $due_out, $planned) {
        $_POST = $post;
        Request::instance()->action = 'plan';
        $this->task = new Controller_Task(new Request('task/plan/' . $id));
        $this->task->before();
        $this->task->action_plan();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        $this->assertLessThanOrEqual(
            $json->due,
            $due
        );
        $this->assertLessThanOrEqual(
            $json->due,  // 5 seconds delay should be generous
            $due
        );
        $this->assertSame(
            $json->due_out,
            $due_out
        );
        $this->assertSame(
            $json->planned,
            $planned
        );
    }


    /**
     * Sets $_POST data for changing task due date.
     */
    function providerDue() {
        /* format for each test:
            array(
                $_POST data,
                tasks.id,
                due timestamp,
                due string,
                planned or not,
        */
        return array(
            array(array('due' => '+1d'),
                  73,
                  time() + SECONDS_IN_DAY,
                  date('D', time() + SECONDS_IN_DAY),
                  0,
            ),
            array(array('due' => 'invalid'),
                  73,
                  0,
                  'plan',
                  1,
            ),
            array(array('due' => 'sunday'),
                  73,
                  strtotime('sunday'),
                  'Sun',
                  0,
            ),
        );
    }

    /**
     * Test changing task due date.
     * @dataProvider providerDue
     */
    function testDue($post, $id, $due, $due_out, $planned) {
        $_POST = $post;
        Request::instance()->action = 'due';
        $this->task = new Controller_Task(new Request('task/due/' . $id));
        $this->task->before();
        $this->task->action_due();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        $this->assertLessThanOrEqual(
            $json->due,
            $due
        );
        $this->assertLessThanOrEqual(
            $json->due,  // 5 seconds delay should be generous
            $due
        );
        $this->assertSame(
            $json->due_out,
            $due_out
        );
        $this->assertSame(
            $json->planned,
            $planned
        );
    }
}
