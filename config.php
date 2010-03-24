<?php
define('IN_PRODUCTION', TRUE);
define('QUOTES_ITEMS_PER_PAGE', 10);
define('QUOTES_AUTHORS_PER_PAGE', 20);
define('QUOTES_MIN_TOP_RATED', 3);
define('SITE_SEPARATOR', ' - ');

define('SPHINX_MAXRESULTS', 1000);
define('SPHINX_RANKER', 0);

define('SPHINX_INDEX', 'quotes');

// set host, port to access sphinxd
define('SPHINX_HOST', 'localhost');
define('SPHINX_PORT', 3312);

define('SITE_NAME', 'Quote Den');

setlocale(LC_ALL, 'en_US.utf8');

define('CATEGORIES_LIST_COUNT', 8);
define('AUTHORS_LIST_COUNT', 8);
define('AUTHOR_SHORT_NAME_LENGTH', 15);
