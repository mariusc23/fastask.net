<?php
class Model_Group extends ORM {
    protected $_belongs_to = array('user' => array('model' => 'user', 'foreign_key' => 'user_id'));
    protected $_has_many = array('followers' => array('model' => 'user', 'through' => 'follow_group')
                               , 'tasks' => array('model' => 'task', 'through' => 'task_group'));
}
