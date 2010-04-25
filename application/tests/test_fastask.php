<?php

/**
 * @group fastask
 * @group fastask.general
 */
Class FastaskTest extends PHPUnit_Framework_TestCase {
    private $fastask = null;
    private $test_user_id = 1;
    private $test_username = 'paul';
    private $test_password = 'testpass';

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')->unit_testing;
        Auth::instance()->login($this->test_username, $this->test_password);
        $this->fastask = new Controller_Fastask(Request::instance());
        $this->fastask->before();
    }

    protected function tearDown() {
    }

    /**
     * Just test that the page renders.
     */
    function testIndex() {
        $this->fastask->action_index();
        $response = $this->fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'text/html; charset=utf-8'
        );
        $this->assertSame(
            $response->status,
            200
        );
    }

    /**
     * Sets the data for main lists.
     * Tests # of tasks returned and property values.
     */
    function providerGetMain() {
        /* format for each test:
            array(
                array $_GET - will be assigned to global $_GET,
                array $pairs - array of key=>value to test in each task
                    array($property, $value)
                tasks per page to expect
        */
        return array(
            array(array(
                    'p' => 2,
                    'ep' => 1,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  TASKS_PER_PAGE,
            ),
            array(array(
                    'p' => 2,
                    'ep' => 1,
                    'n' => 1,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1,
            ),
            array(array(
                    't' => 1,
                    'ep' => 1,
                    'n' => 1,
                  ),
                  array(
                    array('user_id', 2),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1
            ),
            array(array(
                    't' => 2,
                    'ep' => 1,
                    'n' => 3,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  3
            ),
            array(array(
                    't' => 3,
                    'ep' => 1,
                    'n' => 5,
                  ),
                  array(
                    array('status', 1),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  5
            ),
        );
    }

    /**
     * Test loading main lists.
     * @dataProvider providerGetMain
     */
    function testGetMain($get, $pairs, $per_page) {
        $_GET = $get;
        $this->fastask->action_t();
        $response = $this->fastask->request;
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
            count($json->tasks),
            $per_page
        );
        foreach ($json->tasks as $task) {
            foreach ($pairs as $pair) {
                $this->assertSame(
                    $task->$pair[0],
                    $pair[1]
                );
            }
        }
    }

    /**
     * Sets the data for followers.
     */
    function providerGetFollowers() {
        /* format for each test:
            array(
                array $_GET - will be assigned to global $_GET,
                array $pairs - array of key=>value to test in each task
                    array($property, $value)
                tasks per page to expect
        */
        return array(
            array(array(
                    'ep' => 1,
                  ),
                  $this->test_user_id,
            ),
            array(array(
                    't' => 1,
                    'ep' => 1,
                  ),
                  $this->test_user_id,
            ),
            array(array(
                    't' => 3,
                    'ep' => 1,
                  ),
                  $this->test_user_id,
            ),
        );
    }

    /**
     * Test followers for 3 main tabs (except command center)
     * @dataProvider providerGetFollowers
     */
    function testGetFollowers($get, $follower_id) {
        $_GET = $get;
        $this->fastask->action_t();
        $response = $this->fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        foreach ($json->tasks as $task) {
            $follower_ids = array();
            foreach($task->followers as $follower) {
                $follower_ids[] = $follower->id;
            }
            $this->assertContains(
                $follower_id,
                $follower_ids
            );
        }
    }

    /**
     * Test followers for command center (tab 3).
     */
    function testCommandCenter() {
        $_GET =  array(
            't' => 2,
            'ep' => 1,
        );
        $this->fastask->action_t();
        $response = $this->fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            200
        );
        $json = json_decode($response->response);
        foreach ($json->tasks as $task) {
            if (count($task->followers) === 1) {
                $this->assertNotEquals(
                    $this->test_user_id,
                    $task->followers[0]->id
                );
            }
        }
    }
    /**
     * Test pagination works properly.
     */
    function testPagination() {
        $_GET = array('ep' => 1, );
        $task_ids = array();
        for ($page = 0; $page < 3; $page++) {
            $_GET['p'] = $page + 1;
            $this->fastask->action_t();
            $json = json_decode($this->fastask->request->response);
            for($i = 0; $i < TASKS_PER_PAGE; $i++) {
                $task_ids[] = $json->tasks[$i]->id;
            }
        }
        $this->assertSame(
            count($task_ids),
            count(array_unique($task_ids)),
            'Duplicate tasks received in pagination'
        );
    }

    /**
     * Sets the data for mini lists.
     * Tests # of tasks returned and property values.
     */
    function providerGetMini() {
        /* format for each test:
            array(
                array $_GET - will be assigned to global $_GET,
                array $pairs - array of key=>value to test in each task
                    array($property, $value)
                tasks per page to expect
        */
        return array(
            array(array(
                    'eu' => 1,
                    'm'  => 2,
                  ),
                  array(
                    array('status', 0),
                    array('planned', 1),
                    array('trash', 0),
                  ),
                  2
            ),
            array(array(
                    'eu' => 1,
                    'tr' => 2,
                    'm'  => 5,
                  ),
                  array(
                    array('trash', 1),
                  ),
                  5
            ),
            array(array(
                    'u'  => 2,
                    'eu' => 1,
                    'tr' => 2,
                    'm'  => 1,
                  ),
                  array(
                    array('trash', 1),
                  ),
                  1
            ),
        );
    }

    /**
     * Test loading mini lists.
     * @dataProvider providerGetMini
     */
    function testGetMini($get, $pairs, $per_page) {
        $_GET = $get;
        $this->fastask->action_t();
        $response = $this->fastask->request;
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
            count($json->tasks),
            $per_page
        );
        foreach ($json->tasks as $task) {
            foreach ($pairs as $pair) {
                $this->assertSame(
                    $task->$pair[0],
                    $pair[1]
                );
            }
        }
        foreach ($json->tasks as $task) {
            $follower_ids = array();
            foreach($task->followers as $follower) {
                $follower_ids[] = $follower->id;
            }
            $this->assertContains(
                $this->test_user_id,
                $follower_ids
            );
        }
    }

    /**
     * Sets the data for groups.
     */
    function providerGroups() {
        /* format for each test:
            array(
                array $_GET - will be assigned to global $_GET,
                array $pairs - array of key=>value to test in each task
                    array($property, $value)
                tasks per page to expect
        */
        return array(
            array(array(
                    'p' => 1,
                    'ep' => 1,
                    'g' => 1,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('group_id', 1),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  TASKS_PER_PAGE,
            ),
            array(array(
                    'p' => 2,
                    'ep' => 1,
                    'n' => 1,
                    'g' => 51,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('group_id', 51),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1,
            ),
            array(array(
                    't' => 1,
                    'ep' => 1,
                    'n' => 1,
                    'g' => 50,
                  ),
                  array(
                    array('user_id', 4),
                    array('group_id', 50),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1
            ),
            array(array(
                    't' => 2,
                    'ep' => 1,
                    'n' => 1,
                    'g' => 45,
                  ),
                  array(
                    array('user_id', $this->test_user_id),
                    array('group_id', 45),
                    array('status', 0),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1
            ),
            array(array(
                    't' => 3,
                    'ep' => 1,
                    'n' => 1,
                    'g' => 45,
                  ),
                  array(
                    array('group_id', 45),
                    array('status', 1),
                    array('planned', 0),
                    array('trash', 0),
                  ),
                  1
            ),
        );
    }

    /**
     * Test loading main lists.
     * @dataProvider providerGroups
     */
    function testGroups($get, $pairs, $per_page) {
        $_GET = $get;
        $this->fastask->action_t();
        $response = $this->fastask->request;
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
            count($json->tasks),
            $per_page
        );
        foreach ($json->tasks as $task) {
            foreach ($pairs as $pair) {
                $this->assertSame(
                    $task->$pair[0],
                    $pair[1]
                );
            }
        }
    }

    /**
     * Just test that content-type is JSON, error set and empty tasks.
     */
    function testEmptyRequest() {
        $this->fastask->action_t();
        $response = $this->fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            404
        );
        $json = json_decode($response->response);
        $this->assertSame(
            $json->error,
            'No tasks found'
        );
        $this->assertSame(
            $json->tasks,
            array()
        );
    }
}

