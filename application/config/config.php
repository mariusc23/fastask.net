<?php

define('IN_PRODUCTION', TRUE);
error_reporting(E_ALL ^ E_NOTICE);
define('SITE_SEPARATOR', ' - ');
define('SITE_NAME', 'Tasklist');

setlocale(LC_ALL, 'en_US.utf8');

// sphinx settings
define('SPHINX_MAXRESULTS', 200);
define('SPHINX_RANKER', 0);
define('SPHINX_INDEX', 'tasklist');
// set host, port to access sphinxd
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 3311);

// pagination
define('PAGES_BEFORE', 5);
define('PAGES_AFTER', 6);
define('TASKS_PER_PAGE', 10);
$PERIODS_NAMES   = array('seconds', 'minutes', 'hours', 'days', 'months', 'years', 'decades');
$PERIODS         = array('s', 'm', 'h', 'd', 'mo', 'yr', 'decade');
$LENGTHS         = array('60','60','24','30','12','10');
define('LENGTHS_COUNT', 6);

// planning
define('SECONDS_IN_DAY', 86400);
// '1985-00-00 00:00:00' <-- before is planned
define('TIMESTAMP_PLANNED', 470649600);

// username validation
define('USERNAME_REGEX', '/[a-z]{3,50}/i');

// notification codes
define('NOTIFICATION_PASSWORD_RESET', 1);

// cache types
define('CACHE_COUNTS', 1);
