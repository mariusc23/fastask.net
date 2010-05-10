<?php

/**
 * Tests for group controller.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 * @group application
 * @group loggedin
 * @group group
 */
class GroupTest extends PHPUnit_Framework_TestCase
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
     * Test group listing.
     */
    function testList()
    {
        Request::instance()->action = 'l';
        $group = new Controller_Group(new Request('group/l'));
        $group->before();
        $group->action_l();
        $response = $group->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 200);
        $json = json_decode($response->response);
        $this->assertSame(count($json->results), 7);
        $this->assertSame($json->results[1]->name, 'paul_2: ');
    }
}
