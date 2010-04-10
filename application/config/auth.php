<?php defined('SYSPATH') OR die('No direct access allowed.');

return array (
    'driver' => 'ORM',
    'hash_method' => 'sha1',
    'salt_pattern' => '7, 12, 13, 29, 31',
    'lifetime' => 43200,
    'session_key' => 'fastask_user',
    'users' => array
    (
        // 'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
    ),
);
