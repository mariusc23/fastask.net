<?php

/**
 * @group loggedin
 * @group fastask
 * @group fastask.search
 */
Class FastaskSearchTestSuite extends PHPUnit_Framework_TestSuite {
    public $test_username = 'paul';
    public $test_password = 'testpass';
    public static function suite() {
        require_once('/var/www/task/application/testcases/' .
            'test_fastask_search.php');
        return new FastaskSearchTestSuite('FastaskSearchTest');
    }

    protected function setUp() {
        Kohana::config('database')->default = Kohana::config('database')
                                                ->unit_testing;
        Auth::instance()->login($this->test_username, $this->test_password);
        $this->test_user = Auth::instance()->get_user();

        $output = null;
        exec('indexer --all --config ' . SPHINX_CONF, $output);
        exec('searchd --config ' . SPHINX_CONF);
    }

    protected function tearDown() {
        $this->test_user->logins = 1;
        $this->test_user->save();
        Auth::instance()->logout();

        exec('killall searchd');
    }
}
