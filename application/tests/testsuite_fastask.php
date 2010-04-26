<?php

/**
 * @group loggedin
 * @group fastask
 * @group fastask.general
 */
Class FastaskSuite extends PHPUnit_Framework_TestSuite {
    public $test_username = 'paul';
    public $test_password = 'testpass';
    public static function suite() {
        require_once('/var/www/task/application/testcases/test_fastask.php');
        return new FastaskSuite('FastaskTest');
    }

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login($this->test_username, $this->test_password);
        $this->test_user = Auth::instance()->get_user();
    }

    protected function tearDown() {
        $this->test_user->logins = 1;
        $this->test_user->save();
        Auth::instance()->logout();
    }
}
