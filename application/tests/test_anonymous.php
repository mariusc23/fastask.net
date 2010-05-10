<?php

/**
 * Logged out, 403 forbidden everywhere.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 * @group application
 * @group anonymous
 */
class AnonymousTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->logout();
    }

    function testFastask()
    {
        $fastask = new Controller_Fastask(new Request('in/t'));
        $fastask->action_t();
        $response = $fastask->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 403);
    }


    function testTask()
    {
        $task = new Controller_Task(new Request('task/add?an=an'));
        $task->before();
        $response = $task->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 403);
    }


    function testGroup()
    {
        $group = new Controller_Group(new Request('group/l'));
        $group->before();
        $group->action_l();
        $response = $group->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 403);
    }


    function testErrors()
    {
        $errors = new Controller_Errors(new Request('a404page'));
        $errors->before();
        $errors->action_404();
        $response = $errors->request;
        $this->assertSame($response->headers['Content-Type'],
                          'text/html; charset=' . Kohana::$charset);
        $this->assertSame($response->status, 404);
    }


    /**
     * SECURITY: Cannot update user info if not logged in.
     */
    function testUpdate()
    {
        $user = new Controller_User(new Request('user/update'));
        $user->before();
        $user->action_update();
        $response = $user->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 403);
    }
}
