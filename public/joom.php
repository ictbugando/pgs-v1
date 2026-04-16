<?php
/*
 * --------------------------------------------------------------------
 * Load Joomla Libraries
 * --------------------------------------------------------------------
 */	
/**
 * @package    Joomla.Site
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (version_compare(PHP_VERSION, '5.3.10', '<'))
{
	die('Your host needs to use PHP 5.3.10 or higher to run this version of Joomla!');
}

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used in the installation folder rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

//define( 'JPATH_BASE', "/var/www/html/continental" );
//define( 'JPATH_BASE', "/var/www/html/bmc1" );
define( 'JPATH_BASE', "C:/server/htdocs/bmc1" );
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();

$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Instantiate the application.
$appJoomla = $container->get(\Joomla\CMS\Application\SiteApplication::class);

\Joomla\CMS\Factory::$application = $appJoomla;

// NOT EDIT NativeStorage.php in joomla line 473 add if( !headers_sent()) to avoid session clash
//htdocs\bmc1\libraries\vendor\joomla\session\src\Storage


//END JOOMLA API IMPORT
