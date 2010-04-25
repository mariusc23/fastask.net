<?php

/**
 * @group task
 * @group task.add
 */
Class TaskTest extends PHPUnit_Framework_TestCase {
    private $task = null;
    private $request = null;
    private $test_user_id = 1;
    private $test_username = 'paul';
    private $test_password = 'testpass';

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')->unit_testing;
        Auth::instance()->login($this->test_username, $this->test_password);
        $this->request = Request::instance();
        $this->request->action = 'add';
        $this->task = new Controller_Task($this->request);
        $this->task->before();
    }

    protected function tearDown() {
    }

    /**
     * Test add empty is a bad request.
     */
    function testAddEmpty() {
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
        $this->request->status = 200;
    }

    /**
     * Sets invalid $_POST data for adding tasks
     */
    function providerInvalid() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the first task
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
        foreach ($pairs as $pair) {
            $this->assertSame(
                json_decode($response->response)->$pair[0],
                $pair[1]
            );
        }
        $this->request->status = 200;
    }
}
