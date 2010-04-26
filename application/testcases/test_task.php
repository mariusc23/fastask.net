<?php

/**
 * @group loggedin
 * @group task
 * @group task.general
 */
Class TaskTest extends PHPUnit_Framework_TestCase {
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
     * Sets $_POST data for changing priority
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
}
