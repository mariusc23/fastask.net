<?php

/**
 * Tests info controller.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 * @group application
 * @group loggedin
 * @group info
 */
class InfoTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login(TEST_USERNAME, TEST_PASSWORD);
    }

    protected function tearDown()
    {
        $test_user = Auth::instance()->get_user();
        $test_user->logins = 1;
        $test_user->save();
    }

    /**
     * Test info logged in.
     */
    function testInfo()
    {
        $info = new Controller_Info(new Request('/'));
        $info->before();
        $info->action_index();
        $response = $info->request;
        $this->assertSame($response->headers['Content-Type'],
                          'text/html; charset=' . Kohana::$charset);
        $this->assertSame($response->status, 200);
    }
}
