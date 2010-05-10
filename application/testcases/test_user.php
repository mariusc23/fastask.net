<?php

/**
 * @group application
 * @group loggedin
 * @group user
 */
class UserTest extends PHPUnit_Framework_TestCase {
    public $user = null;

    /**
     * Provider to test username available.
     */
    function providerAvailable() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value)
        */
        return array(
            array('', 400),
            array('123invalid', 400),
            array('xx', 400),
            array('valid', 200, 1),
            array('paul', 200, 0),
        );
    }

    /**
     * Test username available.
     * @dataProvider providerAvailable
     */
    function testAvailable($username, $status, $available = 0) {
        $_POST['username'] = $username;
        $user = new Controller_user(new Request('user/available'));
        $user->before();
        $user->action_available();
        $response = $user->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, $status);

        if (200 === $response->status) {
            $json = json_decode($response->response);
            $this->assertSame($json->available, $available);
        }
    }


    /**
     * Test username list.
     */
    function testList() {
        $user = new Controller_user(new Request('user/l'));
        $user->before();
        $user->action_l();
        $response = $user->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, 200);

        $json = json_decode($response->response);
        $this->assertSame(count($json->users), 2);
        $this->assertSame($json->users[0]->username, 'paul');
    }


    /**
     * Provider to update user credentials.
     */
    function providerUpdate() {
        /* format for each test:
            array(
                array $_POST - will be assigned to global $_POST,
                array $pairs - array of key=>value to test in the response
                    array($property, $value)
        */
        return array(
            array(array(), 400),
            array(array(
                    'current_password' => TEST_PASSWORD,
                    'password' => TEST_PASSWORD,
                    'password_confirm' => 'doesnotmatch',
                  ),
                  400
            ),
            array(array(
                    'current_password' => 'invalid',
                    'password' => TEST_PASSWORD,
                    'password_confirm' => TEST_PASSWORD,
                  ),
                  400
            ),
            array(array(
                    'name' => TEST_NAME,
                    'email' => TEST_EMAIL,
                    'current_password' => TEST_PASSWORD,
                    'password' => '123',
                    'password_confirm' => '123',
                  ),
                  400
            ),
            array(array(
                    'name' => TEST_NAME,
                    'email' => TEST_EMAIL,
                    'current_password' => TEST_PASSWORD,
                    'password' =>  str_repeat('*', 51),
                    'password_confirm' => str_repeat('*', 51),
                  ),
                  400
            ),
            array(array(
                    'name' => str_repeat('*', 101),
                    'email' => TEST_EMAIL,
                  ),
                  400
            ),
            array(array(
                    'name' => TEST_NAME,
                    'email' => 'invalid@email',
                  ),
                  400
            ),
            array(array(
                    'name' => TEST_NAME,
                    'email' => TEST_EMAIL,
                    'current_password' => TEST_PASSWORD,
                    'password' => TEST_PASSWORD,
                    'password_confirm' => TEST_PASSWORD,
                  ),
                  200
            ),
            array(array(
                    'name' => TEST_NAME,
                    'email' => TEST_EMAIL,
                  ),
                  200
            ),
        );
    }

    /**
     * Test user update.
     * @dataProvider providerUpdate
     */
    function testUpdate($post, $status) {
        $_POST = $post;
        $user = new Controller_user(new Request('user/update'));
        $user->before();
        $user->action_update();
        $response = $user->request;
        $this->assertSame($response->headers['Content-Type'],
                          'application/json');
        $this->assertSame($response->status, $status);
        $json = json_decode($response->response);
        if (200 !== $status) {
            $this->assertEquals(is_string($json->error), true);
        }
    }
}
