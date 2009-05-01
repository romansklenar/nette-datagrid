<?php

/**
 * Application bootstrap file.
 *
 * @copyright  Copyright (c) 2009 TOPSPIN, s.r.o.
 * @package    PodaciDenik
 * @version    $Id$
 */


// Step 1: Load Nette Framework
if (!is_dir(LIBS_DIR . '/Nette')) {
	die("Extract Nette Framework to library directory '" . realpath(LIBS_DIR) . "'.");
}
require_once LIBS_DIR . '/Nette/loader.php';

/** 1a) Load extension methods */
require_once APP_DIR . '/extensions.php';



// Step 2: Configure environment

/** 2a) load configuration from config.ini file */
Environment::setName(Environment::DEVELOPMENT);
$config = Environment::loadConfig();



/** 2b) check if needed directories are writable */
if (!is_writable(Environment::getVariable('tempDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('tempDir')) . "' writable!");
}

if (!is_writable(Environment::getVariable('logDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('logDir')) . "' writable!");
}


/** 2c) Setup Nette\Debug for better exception and error visualisation
 *
 * Nette\Debug::enable($level, $logFile, $email)
 * $level nastavuje úroveň logování chyb, je-li neuveden (NULL), výchozí úroveň je nastavena na E_ALL | E_STRICT
 * $logFile jméno error logu, výchozí výstupní soubor je %logDir%/php_error.log, 
 * jde docílit i zobrazování chyb místo logování uvedením jednoho z následujících dvou parametrů:
 *   FALSE - chyby nebudou logovány, ale zobrazovány rovnou na obrazovku (laděnka)
 *   NULL  - provede se autodetekce podle řežimu production: Environment::isProduction() ? 'logovat chyby' : 'zobrazovat chyby';
 * $email boolean hodnota případně pseudo hlavičky chybového mailu, který je posílán na zadanou adresu v případě logování chyb
 */
$emailHeaders = array(
    'From' => 'debug@%host%',
    'To'   => 'admin@%host%',
    'Subject' => 'Kritická chyba na serveru %host% !',
    'Body' => 'Mrkni na error log na serveru %host%, %date% došlo ke kritické chybě v aplikaci: %message%',
);
Debug::$maxDepth = 5;
Debug::$maxLen = 255;
//Debug::$productionMode = $_SERVER['REMOTE_ADDR'] !== '127.0.0.1';  // ip adresa adminova počítače
$mode = (Environment::isDebugging() && !Environment::getHttpRequest()->isAjax()) ? Debug::DEVELOPMENT : Debug::PRODUCTION;
Debug::enable($mode, NULL, $emailHeaders);


/** 2d) enable RobotLoader - this allows load all classes automatically */
$loader = new RobotLoader();
$loader->addDirectory(explode(';', $config->scanDirs));
$loader->autoRebuild = Environment::isDebugging() ? TRUE : FALSE; // pokud nenajdu třídu, mám se znovusestavit?
$loader->register();


/** 2e) enable Profiler and RoutingDebugger */
if (Environment::isDebugging() && !Environment::getHttpRequest()->isAjax()) {
	Debug::enableProfiler();
}


/** 2f) Session setup [optional] */
if (Environment::getVariable('sessionDir') !== NULL && !is_writable(Environment::getVariable('sessionDir'))) {
	die("Make directory '" . realpath(Environment::getVariable('sessionDir')) . "' writable!");
}
$session = Environment::getSession();
$session->setSavePath(Environment::getVariable('sessionDir'));



// Step 3: Configure application

/** 3a) Setup Application, ErrorPresenter & exceptions catching */
/* @var $application Application */
$application = Environment::getApplication();
$application->errorPresenter = 'Error';
$application->catchExceptions = Environment::isProduction();


Presenter::$invalidLinkMode = Environment::isProduction() ? Presenter::INVALID_LINK_SILENT : Presenter::INVALID_LINK_EXCEPTION;
Environment::setVariable('host', Environment::getHttpRequest()->getUri()->host);

/** 3b) establish database connection and initialize services */
$application->onStartup[] = 'BaseModel::initialize';
$application->onStartup[] = 'Services::initialize';



// Step 4: Setup application router
$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'presenter' => 'Example',
	'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<action>/', array(
'presenter' => 'Example',
	'action' => 'default',
));

$router[] = new SimpleRouter('Example:default');


// Step 5: Run the application!
$application->run();
