<?php

/**
 * @group fastask
 * @group fastask.search
 */
Class FastaskSearchTest extends PHPUnit_Framework_TestCase {
    private $fastask = null;
    private $test_user_id = 1;
    private $test_username = 'paul';
    private $test_password = 'testpass';

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')->unit_testing;
        Auth::instance()->login($this->test_username, $this->test_password);
        $this->fastask = new Controller_Fastask(Request::instance());
        $this->fastask->before();

        $output = null;
        exec('indexer --all --config ' . SPHINX_CONF);
        exec('searchd --config ' . SPHINX_CONF);
    }

    protected function tearDown() {
        exec('killall searchd');
    }

    /**
     * Sets the data for search.
     */
    function providerSearch() {
        /* format for each test:
            array(
                array $_GET - will be assigned to global $_GET,
                array $pairs - array of key=>value to test in the first task
                    array($property, $value)
                tasks per page to expect
        */
        return array(
            array('s'  => 'asdfgh', 404),
            array('s'  => '', 404),
            array('s'  => 'ajaxify', 200),
        );
    }

    /**
     * Test search.
     * @dataProvider providerSearch
     */
    function testSearch($search, $status) {
        $_GET = array(
            'ep' => 1,
            's'  => $search,
        );
        // Need to reset status because of repeated calls and only one setup.
        $this->fastask->request->status = 200;
        $this->fastask->action_t();
        $response = $this->fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            $status
        );
        $json = json_decode($response->response);
        $count = count($json->tasks);
        if ($count > 0) {
            foreach ($json->tasks as $task) {
                $follower_ids = array();
                foreach($task->followers as $follower) {
                    $follower_ids[] = $follower->id;
                }
                $follower_ids[] = $task->user_id;
                $this->assertContains(
                    $this->test_user_id,
                    $follower_ids
                );
            }
        }
    }
}

