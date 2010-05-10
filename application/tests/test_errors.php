<?php
/**
 * Tests errors controller.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 * @group application
 * @group loggedin
 * @group errors
 */
class ErrorsTest extends PHPUnit_Framework_TestCase
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
     * Test errors logged in. At the moment, this only checks 404 errors.
     */
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
}
