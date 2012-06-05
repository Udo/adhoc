<?php

  # init environment
  $GLOBALS['profiler_start'] = microtime();
  $GLOBALS['APP.BASEDIR'] = dirname(__FILE__);

  ob_start("ob_gzhandler");
  ob_start();
  chdir($GLOBALS['APP.BASEDIR']);
  
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/database.php'); 
  require('lib/config.php'); 
  
  o(new HubbubDispatcher($_REQUEST))
    ->receiveData()
    ->initEnvironment()
    ->initController($_REQUEST['controller'])
    ->invokeAction($_REQUEST['action'])
    ->invokeView('main')
    ->invokeTemplate(cfg('page/template', 'default'))
    ->cleanUp();
  	  	
?> 