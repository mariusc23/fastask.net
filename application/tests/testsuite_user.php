<?php

/**
 * Test suite for user controller.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 * @group application
 * @group loggedin
 * @group user
 */
class UserSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        include_once '/var/www/task/application/testcases/test_user.php';
        return new UserSuite('UserTest');
    }

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
}
