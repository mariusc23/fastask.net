<?php

/**
 * @group application
 * @group loggedin
 * @group fastask
 * @group fastask.search
 */
class FastaskSearchTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets the data for search.
     */
    function providerSearch()
    {
        /* format for each test:
            array(
                search term
                status code to expect
        */
        return array(
            array('s' => 'asdfgh', 404),
            array('s' => '', 404),
            array('s' => 'nulla', 200), // One of those lorem-ipsum results
        );
    }

    /**
     * Test search.
     *
     * @param string $search search term
     * @param int $status http return status code to expect
     *
     * @dataProvider providerSearch
     */
    function testSearch($search, $status)
    {
        $_GET = array('ep' => 1, 's'  => $search,);
        // Need to reset status because of repeated calls and only one setup.
        $fastask = new Controller_Fastask(new Request('in/t'));
        $fastask->before();
        $fastask->request->status = 200;
        $fastask->action_t();
        $response = $fastask->request;

        $this->assertSame($response->headers['Content-Type'],
                          'application/json' );
        $this->assertSame($response->status, $status);

        $json = json_decode($response->response);
        $count = count($json->tasks);
        if ($count > 0) {
            foreach ($json->tasks as $task) {
                $follower_ids = array();
                foreach ($task->followers as $follower) {
                    $follower_ids[] = $follower->id;
                }
                $follower_ids[] = $task->user_id;

                $this->assertContains(TEST_USER_ID, $follower_ids);
            }
        }
    }
}
