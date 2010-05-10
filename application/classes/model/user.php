<?php
/**
 * User model, based on Kohana's auth.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 */
class Model_User extends Model_Auth_User
{
    protected $_has_many = array(
        'tasks' => array('model' => 'task', 'foreign_key' => 'user_id'),
        'groups' => array('model' => 'group', 'foreign_key' => 'user_id'),
        'roles' => array('model' => 'role', 'through' => 'roles_users'),
        'caches' => array('model' => 'usercache', 'foreign_key' => 'user_id'),
        'followers' => array('model' => 'user', 'through' => 'follow_user'),
    );

    /**
     * Validates user creation. Includes Kohana's default validation.
     *
     * @param array $array the user data (e.g. $_POST or $_GET)
     *
     * @return array $array modified input
     */
    public function validate_create($array)
    {
        // Initialize the validation library and setup rules
        $array = Validate::factory($array)
            ->rules('password', $this->_rules['password'])
            ->rules('password_confirm', $this->_rules['password_confirm'])
            ->rules('username', $this->_rules['username'])
            ->rules('email', $this->_rules['email'])
            ->rule('username', 'min_length', array(3))
            ->rule('username', 'max_length', array(20))
            ->rule('password', 'min_length', array(5))
            ->rule('password', 'max_length', array(50))
            ->rule('username', 'alpha')
            ->filter('username', 'trim')
            ->filter('password', 'trim')
            ->filter('password_confirm', 'trim');

        // run username callbacks from parent
        foreach ($this->_callbacks['username'] as $callback) {
            $array->callback('username', array($this, $callback));
        }

        // run email callbacks
        foreach ($this->_callbacks['email'] as $callback) {
            $array->callback('email', array($this, $callback));
        }

        return $array;
    }

     /**
     * Validates user creation. Includes Kohana's default validation.
     *
     * @param array $array the user data (e.g. $_POST or $_GET)
     *
     * @return array $array modified input
     */
    public function validate_change($array)
    {
        $array = Validate::factory($array)
            ->rule('email', 'email')
            ->rule('name', 'max_length', array(100))
            ->filter('name', 'trim');

        return $array;
    }
}
