<?php

/**
 * Logged out, 403 forbidden everywhere.
 * @group anonymous
 */
Class AnonymousTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')
            ->unit_testing;
    }

    function testFastask() {
        $fastask = new Controller_Fastask(new Request('in/t'));
        $fastask->action_t();
        $response = $fastask->request;
        $this->assertSame(
            $response->headers['Content-Type'],
            'application/json'
        );
        $this->assertSame(
            $response->status,
            403
        );
    }


    function testTask() {
        $task = new Controller_Task(new Request('task/add?an=an'));
        $task->before();
        $response = $task->request;
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
