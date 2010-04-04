<?php
class Model_Usercache extends ORM {
    protected $_table_name = 'user_cache';

    protected $_belongs_to = array(
        'user' => array('model' => 'user', 'foreign_key' => 'user_id'),
    );
}
