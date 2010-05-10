<?php
/**
 * Model for sending out email invitations.
 *
 * @author Paul Craciunoiu <paul@craciunoiu.net>
 */
class Model_Invitation extends ORM
{
    protected $_belongs_to = array('user' =>
                                     array('model' => 'user',
                                           'foreign_key' => 'user_id'), );
}
