<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: basic initialization and config loading, please do not edit directly. Modify etc/yourserver.com.php instead
 */

  # mandate at least PHP 5.3
  $version = explode('.', phpversion());
  if(!($version[0] >= 5 && $version[1] >= 3)) die('Error: PHP 5.3 or greater needed'); 

  # register custom error handlers
  error_reporting(E_ALL ^ E_NOTICE);
  set_error_handler('h2_errorhandler', E_ALL ^ E_NOTICE);
  set_exception_handler('h2_exceptionhandler');

  # sadly, include paths aren't standardized, so we have to roll our own
  if(strpos(PHP_OS, "WIN") !== false) $config['os.path.separator'] = ';'; else $config['os.path.separator'] = ':';
	ini_set('include_path', implode($config['os.path.separator'], array($GLOBALS['APP.BASEDIR'].'/')));
  
  # default timezone is UTC
  date_default_timezone_set('UTC');
  
	# set cookies  
  session_name('adhoc');
  session_start();

	# log errors, basic ini stuff
  ini_set('error_log', 'log/error.log');
  ini_set('magic_quotes_gpc', 0);
	ini_set('magic_quotes_runtime', 0);
  ini_set('log_errors', true);
  ini_set('display_errors', 'on');
	// set httpOnly flag for more secure cookie handling
  ini_set('session.cookie_httponly', 1);
  
  # path separator for "pretty" URLs
  define('URL_CA_SEPARATOR', '/');
  define('MEMCACHE_TTL', 60*60);
  define('MAX_POSTTEXTSIZE', 1024*4); 

  sanitizeRequestVariable();

	$cfgCategory = 'config';
		
  require('conf/default.php');
  
  if(!isset($GLOBALS[$cfgCategory]['db']))
    die('Error: configuration file could not be loaded.');
  
  $GLOBALS['menu'] = array();
  
  // setting some default values
  $svc = &$GLOBALS['config']['service'];
  foreach(array(
    'dateformat' => 'H:i d.m.Y',
    'name' => 'AdHoc',
		'defaultcontroller' => 'home',
		'defaultaction' => 'index',
		'version' => 2012.100,
    // number of open account signups this server should provide
		'server' => $_SERVER['HTTP_HOST'],
    ) as $k => $v) if(!isset($svc[$k])) $svc[$k] = $v;
    
  // these speed up DB operations because they prevent key lookups
  $GLOBALS['config']['dbinfo'] = array(
    'communities' => array('keys' => array('c_key')),
    'users' => array('keys' => array('u_key')),
    'posts' => array('keys' => array('p_key')),
    );

  profile_point('config loaded');
?>