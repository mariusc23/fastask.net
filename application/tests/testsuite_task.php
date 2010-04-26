<?php

/**
 * @group loggedin
 * @group task
 * @group task.general
 */
Class TaskSuite extends PHPUnit_Framework_TestSuite {
    public $test_username = 'paul';
    public $test_password = 'testpass';
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_task.php');
        return new TaskSuite('TaskTest');
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

        DB::delete('tasks')
            ->where('id', '>', 69)
            ->execute();
        DB::delete('follow_task')
            ->where('task_id', '>', 69)
            ->execute();
        DB::delete('groups')
            ->where('id', '>', 13)
            ->execute();
        // reset AUTO_INCREMENT
        DB::query(Database::UPDATE, 'ALTER TABLE groups AUTO_INCREMENT = 14')
            ->execute();
        DB::query(Database::UPDATE, 'ALTER TABLE tasks AUTO_INCREMENT = 70')
            ->execute();
    }
}
