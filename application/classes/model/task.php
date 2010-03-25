<?php
class Model_Task extends ORM {
    protected $_belongs_to = array('user' => array('model' => 'user', 'foreign_key' => 'user_id'));
    protected $_has_many = array('followers' => array('model' => 'user', 'through' => 'follow_task')
                               , 'groups' => array('model' => 'group', 'foreign_key' => 'group_id'));


    public function __construct($id = NULL) {
        parent::__construct($id);
        /*
        $this->_object['categories_list'] = $this->categories
            ->order_by('name', 'asc')
            ->find_all();
        if (Auth::instance()->get_user()) {
            $this->_object['user'] = true;
        }*/
    }
}
