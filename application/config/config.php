<?php
define('IN_PRODUCTION', TRUE);
define('SITE_SEPARATOR', ' - ');

define('SPHINX_MAXRESULTS', 1000);
define('SPHINX_RANKER', 0);

define('SPHINX_INDEX', 'tasks');

// set host, port to access sphinxd
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 3312);

define('SITE_NAME', 'Tasklist');

setlocale(LC_ALL, 'en_US.utf8');

