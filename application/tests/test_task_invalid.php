<?php

/**
 * @group loggedin
 * @group task
 * @group task.add.invalid
 */
Class TaskInvalidSuite extends PHPUnit_Framework_TestSuite {
    public $test_username = 'paul';
    public $test_password = 'testpass';
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_task_invalid.php');
        return new TaskInvalidSuite('TaskInvalidTest');
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
