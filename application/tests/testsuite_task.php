<?php

/**
 * @group loggedin
 * @group task
 * @group task.general
 */
class TaskSuite extends PHPUnit_Framework_TestSuite {
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_task.php');
        return new TaskSuite('TaskTest');
    }

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login(TEST_USERNAME, TEST_PASSWORD);
    }

    protected function tearDown() {
        $test_user = Auth::instance()->get_user();
        $test_user->logins = 1;
        $test_user->save();
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
