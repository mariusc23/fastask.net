<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 **/

return array(
    // 'js' => array('//js/file1.js', '//js/file2.js'),
    // 'css' => array('//css/file1.css', '//css/file2.css'),

    // custom source example
    /*'js2' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => create_function('$a', 'return $a;')
        ))
    ),//*/

    /*'js3' => array(
        dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
        // do NOT process this file
        new Minify_Source(array(
            'filepath' => dirname(__FILE__) . '/../min_unit_tests/_test_files/js/before.js',
            'minifier' => array('Minify_Packer', 'minify')
        ))
    ),//*/
    'js' => array('//js/jquery.min.js', '//js/jqModal.js',
        '//js/jquery.history.js', '//js/jquery.autocomplete.pack.js',
        '//js/constants.js', '//js/url.js', '//js/notification.js',
        '//js/row.js', '//js/list.js', '//js/workbox.js', '//js/modal.js',
        '//js/profile.js', '//js/main.js'),

    'lr' => array('//js/jquery.min.js', '//js/register.js'),
    'css' => array('//css/main.css', '//css/modal.css', '//css/list.css',
        '//css/workbox.css', '//css/profile.css', '//css/notification.css',
        '//css/autocomplete.css'),

);
