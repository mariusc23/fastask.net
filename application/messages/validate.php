<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'not_empty'    => '<em>:field</em> must not be empty',
	'matches'      => '<em>:field</em> must be the same as <em>:param1</em>',
	'regex'        => '<em>:field</em> does not match the required format',
	'exact_length' => '<em>:field</em> must be exactly <em>:param1</em> characters long',
	'min_length'   => '<em>:field</em> must be at least <em>:param1</em> characters long',
	'max_length'   => '<em>:field</em> must be less than <em>:param1</em> characters long',
	'in_array'     => '<em>:field</em> must be one of the available options',
	'digit'        => '<em>:field</em> must be a digit',
	'decimal'      => '<em>:field</em> must be a decimal with <em>:param1</em> places',
	'range'        => '<em>:field</em> must be within the range of <em>:param1</em> to :param2',
    'username_available' => '<em>:field</em> is already taken',
    'email_available' => '<em>:field</em> is already taken',
    'invalid' => 'We have no record of this :field, <a href="/user/register">click here to register</a>.',
);