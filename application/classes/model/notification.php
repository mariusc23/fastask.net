<?php
/**
 * Model for saving and sending out email notifications.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 */
class Model_Notification extends ORM
{
    protected $_belongs_to = array('user' =>
                                     array('model' => 'user',
                                           'foreign_key' => 'user_id'), );
}
