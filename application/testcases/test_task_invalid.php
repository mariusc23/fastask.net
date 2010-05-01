<?php

/**
 * @group application
 * @group loggedin
 * @group task
 * @group task.invalid
 */
class TaskInvalidTest extends PHPUnit_Framework_TestCase {
    private $task = null;

    protected function setUp() {
        // Need to reset status code for running multiple tests
        // because Kohana's Request::instance() is static.
        Request::instance()->status = 200;
    }

    /**
     * Test adding with empty data produces a bad request (status code 400)
     */
    function testAddEmpty() {
        Request::instance()->action = 'add';
        $this->task = new Controller_Task(Request::instance());
        $this->task->before();
        $this->task->action_add();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            400
        );
        $this->assertSame(
            json_decode($response->response)->error,
            'Must be adding or planning.'
        );
    }

    /**
     * Sets invalid $_POST data for adding tasks
     */
    function providerInvalid() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value)
        */
        return array(
            array(array(
                    'add' => 1,
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'tooshort',
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Good enough',
                    'priority' => 'invalid',
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'priority' => '2',
                    'text' => str_repeat('*', 1501),
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'add' => 1,
                    'text' => 'Good enough',
                    'priority' => '2',
                  ),
                  array(
                    array('error', 'Someone must be assigned to this task.'),
                  ),
            ),
            array(array(
                    'plan' => 1,
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'plan' => 1,
                    'text' => 'tooshort',
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'plan' => 1,
                    'text' => 'Good enough',
                    'priority' => 'invalid',
                  ),
                  array(
                    array('error', 'Invalid data submitted.'),
                  ),
            ),
            array(array(
                    'plan' => 1,
                    'text' => 'Good enough',
                    'priority' => '2',
                  ),
                  array(
                    array('error', 'Someone must be assigned to this task.'),
                  ),
            ),
        );
    }

    /**
     * Test invalid data.
     * @dataProvider providerInvalid
     */
    function testAddInvalid($post, $pairs) {
        $_POST = $post;
        Request::instance()->action = 'add';
        $this->task = new Controller_Task(Request::instance());
        $this->task->before();
        $this->task->action_add();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            400
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
     * Sets invalid $_POST data for sharing tasks
     */
    function providerShareInvalid() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                tasks.id
                status
        */
        return array(
            array(array(),
                  999,
                  404,
            ),
            array(array(),
                  1,
                  400,
            ),
            array(array(
                    'u' => '5',
                  ),
                  1,
                  404,
            ),
            array(array(
                    'u' => '2',
                  ),
                  1,
                  400,
            ),
            array(array(
                    'u' => '1',
                    'a' => '1',
                  ),
                  1,
                  400,
            ),
            // SECURITY: modifying someone else's task
            array(array(
                    'u' => '2',
                    'a' => '1',
                  ),
                  50,
                  403,
            ),
            array(array(
                    'u' => '1',
                    'a' => '1',
                  ),
                  51,
                  403,
            ),
            array(array(
                    'u' => '3',
                    'a' => '1',
                  ),
                  64,
                  403,
            ),
            array(array(
                    'u' => '1',
                  ),
                  1,
                  400,
            ),
        );
    }

    /**
     * Test share invalid.
     * @dataProvider providerShareInvalid
     */
    function testShareInvalid($post, $id, $status) {
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
            $status
        );
    }


    /**
     * Sets invalid $_POST data for editing text in tasks.
     */
    function providerTextInvalid() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                status
        */
        return array(
            array(array(
                    'text' => 'tooshort',
                  ),
                  400,
            ),
            array(array(
                    'text' => str_repeat('*', 1501),
                  ),
                  400,
            ),
        );
    }

    /**
     * Test text invalid.
     * @dataProvider providerTextInvalid
     */
    function testTextInvalid($post, $status) {
        $_POST = $post;
        Request::instance()->action = 'text';
        $this->task = new Controller_Task(new Request('task/text/2'));
        $this->task->before();
        $this->task->action_text();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            $status
        );
    }


    /**
     * Sets invalid $_POST data for editing due date in tasks.
     */
    function providerDueInvalid() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                status
        */
        return array(
            array(array(),
                  400,
            ),
        );
    }

    /**
     * Test due invalid.
     * @dataProvider providerDueInvalid
     */
    function testDueInvalid($post, $status) {
        $_POST = $post;
        Request::instance()->action = 'due';
        $this->task = new Controller_Task(new Request('task/due/2'));
        $this->task->before();
        $this->task->action_due();
        $response = $this->task->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            $status
        );
    }
}
