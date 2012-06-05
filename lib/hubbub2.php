<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: Hubbub-specific functions and objects
 */

# set up the class loader
function __autoload($class_name) {
  $classFile = 'classes/'.strtolower($class_name).'.php';
  if(file_exists($classFile))
    include_once($classFile);
}

#--------------------------------------------------------------------------------------

/* output something to a template field */
function h2_content_area($name, $function)
{
  if(is_string($function))
  {
    $GLOBALS['content'][$name] .= $function;
  }
  else
  {
    ob_start();
    $function();
    $GLOBALS['content'][$name] .= ob_get_clean(); 
  }
}

function tmpl($type, $data)
{
  switch($type)
  {
    case('error'): {
      ?><pre class="fail"><?
      foreach($data as $k => $v) 
      {
        print($k.': ');
        if($k == 'backtrace')
        {
          print(chr(13));
          foreach($v as $error)
            print('  '.$error['function'].'('.implode(',', $error['args']).')'.chr(13));
        }
        else
        {
          if(is_array($v)) print_r($v); else print(htmlspecialchars($v));
        }
        print(chr(13));
      }
      ?></pre><?
      break;
    }
  }
}

function h2_exceptionhandler($exception)
{
  if($GLOBALS['errorhandler_ignore']) return;
  tmpl('error', array(
    'backtrace' => debug_backtrace(),
    'no' => 1,
    'msg' => 'Exception: '.$exception->getMessage(),
    'file' => basename($exception->getFile()),
    'line' => $exception->getLine()));
  $report = 'Exception: '.$exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine();
  if(cfg('debug/verboselog')) logError('log/error.log', $report);
  return(true);
}

/* replaces the standard PHP error handler, mainly because we want a stack trace */
function h2_errorhandler($errno, $errstr = '', $errfile = __FILE__, $errline = -1)
{
  if($GLOBALS['errorhandler_ignore']) return;
  tmpl('error', array(
    'backtrace' => debug_backtrace(),
    'no' => $errno,
    'msg' => $errstr,
    'file' => basename($errfile),
    'line' => $errline));
  $report = 'Error: '.$errstr.' in '.basename($errfile).':'.$errline."\r\n";
  logToFile('log/error.log', $report);
  return(true);
}
	
/* this error handler is for Hubbub message requests (where we only got console output) */
function h2_errorhandler_msg($errno, $errstr, $errfile = __FILE__, $errline = -1)
{
  if($GLOBALS['errorhandler_ignore']) return;
  $GLOBALS['errorhandler_ignore'] = true;
  $traceId = h2_make_uid();
  $response = array(
    'result' => 'fail',
    'reason' => 'server error (trace '.$traceId.')');
  $GLOBALS['req'][] = $response;
  logError('log/error.log', '#'.$traceId.' '.$errstr.' in '.basename($errfile).' line '.$errline);
  die(json_encode($GLOBALS['req']));
  return(true);
}

function mode($mode = null)
{
  if($mode == null) return(first($GLOBALS['mode'], 'web'));
  $GLOBALS['mode'] = $mode;
  switch($mode)
  {
    case('headless'): {
      set_error_handler('h2_errorhandler_msg', E_ALL ^ E_NOTICE);
      break;
    }
  
  }
}

#--------------------------------------------------------------------------------------
// decides what to do with a given request
class HubbubDispatcher
{
  function __construct(&$params)
  {
    $this->params = &$params; 
  }
  
  function receiveData()
  {
   	if(isset($_REQUEST['hubbub_msg']))
  	{
      // hubbub event handler stub
      die();		
  	}
  	return($this);
  }
  
  function initEnvironment()
  {
    if (substr($_SERVER['REQUEST_URI'], 0, 1) == '/' && !isset($_REQUEST['controller']))
      interpretQueryString($_SERVER['REQUEST_URI']);  
      
    if(substr($_REQUEST['parts'][0], 0, 1) == '~')
    {
      $_REQUEST['controller'] = 'info';
      $_REQUEST['action'] = 'user';
      $_REQUEST['id'] = substr($_REQUEST['parts'][0], 1);
    }

    if($_REQUEST['uri']['host'] != cfg('service/server'))
    {
      $host = $_REQUEST['uri']['host'];
      $_REQUEST['uri']['subdomain'] = CutSegment('.', $host); 
      o('community', new H2Community($_REQUEST['uri']['subdomain']));
    }
    else
      $_REQUEST['uri']['rootdomain'] = true; 
    
    if(substr($_REQUEST['controller'], 0, 1) == '@' || substr($_REQUEST['controller'], 0, 1) == '~')
    {
      // if this happens, it's a link to a personal page
      $_REQUEST['user'] = substr($_REQUEST['controller'], 1); 
      $_REQUEST['controller'] = 'profile';
      $_REQUEST['action'] = 'view';
    }
	  o('user', new H2User($_SESSION['uid']));
  	profile_point('  - user');
	  l10n_load('mvc/l10n');
  	profile_point('  - l10n');
    h2_content_area('startuperrors', trim(ob_get_clean()));
  	return($this);
  }
  
  function initController($controllerName, $isRootController = true)
  {
    $controllerName = first($controllerName, cfg('service/defaultcontroller'));
  	$this->controllerName = H2Syntax::safeName($controllerName);
  	$this->controllerFile = 'mvc/'.strtolower($this->controllerName).'/'.strtolower($this->controllerName).'.controller.php';
  	if(!file_exists($this->controllerFile) && $isRootController)
  	{
	    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	    header('Status: 404 Not Found');	
	    die('File not found: '.$_SERVER['REQUEST_URI'].'<br/>'.$this->controllerName);     
  	}
  	require_once($this->controllerFile);
    $this->controllerClassName = $this->controllerName.'Controller';
  	$this->controller = o(new $this->controllerClassName($this->controllerName), 'controller');
  	if (is_callable(array($this->controller, '__init'))) $this->controller->__init();
  	if($isRootController) o($this->controller, 'controller');
    profile_point('controller invoked');
  	return($this);
  }
  
  function invokeAction($actionName)
  {
    ob_start();
    $this->controller->invokeAction($actionName, $this->params);
    $this->actionOutput = trim(ob_get_clean());
    profile_point('action executed');
  	return($this);
  }
  
  function invokeView($partName = null)
  {
    h2_content_area($partName, 
      $this->controller->invokeView($this->controller->lastAction).$this->actionOutput);
    profile_point('view executed');
  	return($this);
  }
  
  function invokeTemplate($templateName)
  {
	  if(cfg('service/compact_html')) ob_start();
  	switch($templateName)
  	{
  		case('blank'): {
  			print($GLOBALS['content']['main']);
  			break;
  		}		
  		default: {
  			header('content-type: text/html;charset=UTF-8');
        require('themes/'.cfg('theme/name', 'default').'/'.$templateName.'.php');
        break;
  		}
  	}
  	if(cfg('service/compact_html'))
  	{
    	$html = '';
    	$hraw = ob_get_clean();
    	foreach(explode("\n", $hraw) as $l) $html .= trim($l);
    	print($html.'<!-- '.number_format(strlen($html)/1024, 2).'kb ('.(100-ceil(100*strlen($html)/strlen($hraw))).'%) -->');
    }
    return($this);
  }
  
  function cleanUp()
  {
    DB_DoPendingUpdates();
  }
}

function is_this_host($hostName)
{
  return(true);
  $hostName = strtolower($hostName);  
  return($hostName == strtolower(cfg('service/server')) || 
    $hostName == strtolower($_SERVER['SERVER_ADDR']) ||
    $hostName == strtolower($_SERVER['SERVER_NAME']) ||
    $hostName == strtolower($_SERVER['HTTP_HOST']));
}

// a simple name-value store interface
// defaults to per-user storage, for system-wide storage use $uid = 'sys'
function nv_store($name, $value, $uid = null)
{
  if($uid == null) $uid = $_SESSION['uid'];
  $nm = $uid.'/'.$name;
  if($value == null)
  {
    // a value of null removes the entry from the DB
    DB_RemoveDataset('nvstore', $nm, 'nv_name');
  }
  else
  {
  	$ds = array('nv_name' => $nm, 'nv_value' => json_encode($value));
  	DB_UpdateDataset('nvstore', $ds);
  }
}

function nv_retrieve($name, $uid = null)
{
  if($uid == null) $uid = $_SESSION['uid'];
  $nm = $uid.'/'.$name;
	$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('nvstore').' WHERE nv_name LIKE ?', array($nm));
	$arv = json_decode($ds['nv_value'], true);
	if(!is_array($arv)) $arv = array();
	return($arv);
}

class H2Class
{
  function getClassNameOfDelegate()
  {
    return('DefaultDelegate');
  }

  function callDelegate($actionName, $params = array(), $returnResult = false)
  {
    $className = $this->getClassNameOfDelegate();
    $delegateInstanceName = strtolower($className);
    
    // first check if that delegate class file was already included
    if(!isset($GLOBALS['DelegatesLoaded'][$delegateInstanceName]))
    {
      $GLOBALS['DelegatesLoaded'][$delegateInstanceName] = true;
      $delegateFile = 'delegates/'.strtolower(get_class($this)).'/'.substr($delegateInstanceName, 0, -8).'.php';
      if(file_exists($delegateFile)) include_once($delegateFile);
    }
    
    // try to get the delegate class, if it doesn't exist - that's fine, too (delegate call does nothing then)
    if(class_exists($delegateInstanceName)) 
    {
      // if such a class is defined, instantiate it
      if(!$this->{$delegateInstanceName}) 
      {
        $this->{$delegateInstanceName} = new $delegateInstanceName(); 
        $this->{$delegateInstanceName}->delegator = $this;
      }
      // if the delegate object has the method $actionName...
      if(is_callable(array($this->{$delegateInstanceName}, $actionName)))
      {
        #WriteToFile('log/delegate.log', __CLASS__.' : '.$delegateInstanceName.' : '.$actionName.' '.$this->data['id'].chr(10));
        $result = $this->{$delegateInstanceName}->{$actionName}($params);
      }
    }

    // whether there was a delegate call or not:    
    if($returnResult)
      return($result);
    else
      return($this);  
  }

  /* sets or returns values from object properties that are arrays */
  function aproperty($p, $k, $v = null, $alwaysReturnSelf = false)
  {
    $result = $this;
    if(is_array($k))
    {
      // array add mode
      foreach($k as $k1 => $v1)
        $this->{$p}[$k1] = $v1;
    }
    else if($v === null)
    {
      // array value-get mode
      $result = $this->{$p}[$k];
      if(substr($k, -4) == '_key') $result = $result+0;
    }
    else
    {
      // array value-set mode
      $this->{$p}[$k] = $v;
    }
    if($alwaysReturnSelf) return($this); else return($result); 
  }
  
  /* set a property */
  function set($k, $v)
  {
    $this->property($k, $v);
    return($this); 
  }
  
  /* Overloading calls to functions that have the same name as existing properties.
     This enables us to do stuff like "->options('whatever', 'false')" in order to set 
     properties in a fake-monad-like chain */
  function __call($identifier, $args = array())
  {
    // getDelegate*() returns the return value of the delegate method (null if none exists)
    if(strStartsWith($identifier, 'getdelegate'))
      return($this->callDelegate(substr($identifier, strlen('getdelegate')), $args, true));
    // whereas just delegate*() calls the delegate method but returns the current object afterwards
    else if(strStartsWith($identifier, 'delegate'))
      return($this->callDelegate(substr($identifier, strlen('delegate')), $args, false));
    // provide a shortcut to standard array fields "ds", "data", and "options"
    else if($identifier == 'ds' || $identifier == 'data' || $identifier == 'options')
      return($this->aproperty($identifier, $args[0], $args[1]));
    // if none of the above happened, throw an error because this method doesn't exist
    else
      trigger_error("Unknown method: ".get_class($this).'::'.$identifier.'()', E_USER_ERROR);
  }
  
}

# hubbub model base class
class H2Model extends H2Class { } 

function leadingzero($s, $n)
{
  while(strlen($s) < $n)
    $s = '0'.$s;
  return($s);
}

# make a random id
function h2_make_uid($length = 16, $notime = false)
{
  if(!$notime)
    $result = leadingzero(base_convert(time(), 10, 36), 8);
  else
    $result = '';
  $i = 'abcdefghijklmnopqrstuvwxyz0123456789';
  $il = strlen($i);
  for($a = strlen($result); $a < $length; $a++)
    $result .= $i[mt_rand(0, $il)];
  return($result);
}

// checks whether a given URL is a Hubbub URL and loads the entity record within it
function hubbub2_loadurl($url)
{
	$content = cqrequest($url);
	return(hubbub2_urldata2entity($content['body']));
}

function hubbub2_urldata2entity($html)
{
  $entity = array();
	// case 1, this is a json array
	if(substr($html, 0, 1) == '{')
	{
		$entity = json_decode($html);
	}
	else // if not, let's parse for a comment with an entity record in it 
	{
		if(stristr($html, '<!-- hubbub2:{') != '')
		{
			CutSegment('<!-- hubbub2:{', $html);
			$seg = '{'.trim(CutSegment('-->', $html));
			$entity = json_decode($seg, true);
		}
	}
	return($entity);
}

?>