<?php
define('IN_PRODUCTION', TRUE);
define('SITE_SEPARATOR', ' - ');
define('SITE_NAME', 'Tasklist');
define('DATE_MYSQL_FORMAT', 'Y-m-d H:i:s');

setlocale(LC_ALL, 'en_US.utf8');

// sphinx settings
define('SPHINX_MAXRESULTS', 1000);
define('SPHINX_RANKER', 0);
define('SPHINX_INDEX', 'tasks');
// set host, port to access sphinxd
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 3312);

// pagination
define('TASKS_PER_PAGE', 10);
$PERIODS_NAMES   = array('seconds', 'minutes', 'hours', 'days', 'months', 'years', 'decades');
$PERIODS         = array('s', 'm', 'h', 'd', 'mo', 'yr', 'decade');
$LENGTHS         = array('60','60','24','30','12','10');
define('LENGTHS_COUNT', 6);
