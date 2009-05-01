<?php

// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__));

// absolute filesystem path to the application root
define('APP_DIR', realpath(WWW_DIR . '/../app'));

// absolute filesystem path to the libraries directory
define('LIBS_DIR', realpath(WWW_DIR . '/../libs'));

// load bootstrap file
require APP_DIR . '/bootstrap.php';
