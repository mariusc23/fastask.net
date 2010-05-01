<?php

/**
 * This suite tests general functionality of the main controller.
 * @group application
 * @group loggedin
 * @group fastask
 * @group fastask.general
 */
class FastaskSuite extends PHPUnit_Framework_TestSuite {
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_fastask.php');
        return new FastaskSuite('FastaskTest');
    }

    protected function setUp() {
        // Set database connection and log in the user.
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login(TEST_USERNAME, TEST_PASSWORD);
    }

    protected function tearDown() {
        // Reset logins and log out the user
        $test_user = Auth::instance()->get_user();
        $test_user->logins = 1;
        $test_user->save();
    }
}
