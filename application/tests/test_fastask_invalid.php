<?php

/**
 * @group fastask
 */
Class LoggedOutTest extends PHPUnit_Framework_TestCase {
    private $fastask = null;

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')->unit_testing;
        $this->fastask = new Controller_Fastask(Request::instance());
    }

    protected function tearDown() {
    }

    /**
     * Logged out, 403 forbidden and JSON.
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
            403
        );
    }
}

