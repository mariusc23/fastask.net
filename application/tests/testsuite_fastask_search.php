<?php

/**
 * This suite tests search functionality in the main controller.
 * @group application
 * @group loggedin
 * @group fastask
 * @group fastask.search
 */
class FastaskSearchTestSuite extends PHPUnit_Framework_TestSuite {
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_fastask_search.php');
        return new FastaskSearchTestSuite('FastaskSearchTest');
    }

    protected function setUp() {
        // Set database connection and log in the user.
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login(TEST_USERNAME, TEST_PASSWORD);

        // Index data and start up the search daemon
        exec('indexer --all --config ' . SPHINX_CONF);
        exec('searchd --config ' . SPHINX_CONF);
    }

    protected function tearDown() {
        // Reset logins and log out the user
        $test_user = Auth::instance()->get_user();
        $test_user->logins = 1;
        $test_user->save();

        // Stop the search daemon
        exec('killall searchd');
    }
}
