<?php

class H2Controller
{
	function __construct($name)
	{
    $this->name = $name;
		$this->user = &$GLOBALS['obj']['user'];
		$this->menu = array();
		$this->subControllers = array();
		$GLOBALS['submenu'] = array();
		l10n_load('mvc/'.$this->name.'/l10n');
	}		
	
  /* require enhanced security for calls that modify data */
  function accessPolicy($types = 'auth')
  {  
    foreach(explode(',', str_replace('write', 'origin,post', $types)) as $type)
      switch(trim($type))
      {
        case('auth'): {
          /* allow only sessions with a valid uid, if not present redirect to signin page */
          if($_SESSION['uid']+0 == 0)
          {
            $usr = new H2User();
            $usr->cookieLogin();
          }  
          if($_SESSION['uid']+0 == 0)
        		$this->redirect('index', 'start');
          break; 
        }
        case('origin'): {
          // require the origin to be the same server
          $ref = parse_url($_SERVER['HTTP_REFERER']);
          if(!is_this_host($ref['host'])) die('Error: Hubbub access policy violation (origin)');
          break; 
        }
        case('post'): {
          // require the POST HTTP method
          if($_SERVER['REQUEST_METHOD'] != 'POST') die('Error: Hubbub access policy violation (POST required)');
          break; 
        }
        case('admin'): {
          if(o('user')->ds['u_role'] != 'A') die('Error: admin access required'); 
          break; 
        }
      }
  }
  
	function redirect($action, $controller = null, $params = array())
	{
		if($controller == null) $controller = $_REQUEST['controller'];
		ob_clean();
    header('X-Redirect: '.$_SERVER['REQUEST_URI']);
    header('location: /'.actionUrl($action, $controller, $params));
		ob_end_flush();
		die();
	}
	
	/*
	 * creates contextual menu items by letting controllers specify what actions should be menu items
	 */
	function makeMenu($str, $url = null)
	{
	  if($url == null)
	  {
	    $url = actionUrl($str, $this->name);
	    $str = l10n($str);
	  }
	  $GLOBALS['submenu'][] = '<a href="'.$url.'">'.htmlspecialchars($str).'</a>';	
	}
	
	function integrate($subController)
	{
	  // first let's init the sub controller
	  // fixme: technically, we'd need this only if it's actually used so it would make
	  // sense to only init the sub controller as needed when it's called
	  $this->subControllers[$subController] = h2_getController($subController, false);
	  $this->subControllers[$subController]->parent = &$this;
	  // sub controllers are awesome to compartmentalize functionality into smaller
	  // packages, but they break the default behavior of actionUrl() - so we need to
	  // make sure actionUrl() knows that this controller is slaved to its parent or
	  // else we're screwing up the URL call path for it:
	  $GLOBALS['subcontrollers'][$subController] = $this->name;
  }

	function invokeAction($action, $params = null)
  {
    $action = first($action, cfg('service/defaultaction'));
		$this->lastAction = $action;
    if($params == null) $params = &$_REQUEST;

    // let's see if we have a sub controller for this, if so: call it!
    $subAction = $action;
    $subController = CutSegment(URL_CA_SEPARATOR, $subAction);
    if(isset($this->subControllers[$subController]))
    {
      $this->subControllers[$subController]->invokeAction($subAction, $params);
      $this->deferredViewController = &$this->subControllers[$subController];
      return;
    } 

    // by convention, actions starting with "ajax_" don't return the whole page template
    // since they're intended to be partial content 
    if(substr($action, 0, 5) == 'ajax_')
    {
      $this->skipView = true;
      $GLOBALS['config']['page']['template'] = 'blank';
    }
        
    if(is_callable(array($this, $action)))
      $output = $this->$action($params);
    else
      h2_errorhandler(0, 'Action not defined: '.$this->name.'.'.$action);
      
    if($output != null) print($output);
      
    $GLOBALS['config']['page']['title'] = $action;      
  }

  function view($viewName)
  {
    $this->currentViewName = $viewName;
    $this->skipView = false;
    $this->viewFile = null;
    $this->{$viewName}();
    if($this->viewFile) $viewName = $this->viewFile;
    if(!$this->skipView)
      include('mvc/'.strtolower($this->name).'/'.strtolower($this->name).'.'.first($viewName).'.php');
  }

	function invokeView($action)
	{
	  // if we have invoked a sub controller for this, we'll also need to call its view now:
	  if(is_object($this->deferredViewController)) 
	    return($this->deferredViewController->invokeView($this->deferredViewController->lastAction));
    ob_start();
    $action = first($action, cfg('service/defaultaction'));
		if(!$this->skipView)
		{
		  $viewFile = 'mvc/'.strtolower($this->name).'/'.strtolower($this->name).'.'.first($this->viewName, $action).'.php';
		  if(!file_exists($viewFile))
		    print('<div class="banner fail">Error: this page does not exist ('.htmlspecialchars($_REQUEST['uri']['path']).')</div>');
		  else
        include($viewFile);
		} 
    $this->pageTitle = first($this->pageTitle, l10n($action.'.title', true), l10n($action));
    cfg('page/title', $this->pageTitle, true);
    return(ob_get_clean());			
	}
	
	function invokeModel($modelname = null)
	{
		// model instances should be singletons, so we're making sure they are
		$modelname = strtolower(first($modelname, $this->name));
		if($GLOBALS['models'][$modelname])
		{
			$this->$modelname = &$GLOBALS['models'][$modelname];
			return($GLOBALS['models'][$modelname]);
		}
		else
		{
      $modelClassName = $modelname.'Model';
      require_once('mvc/'.$modelname.'/'.$modelname.'.model.php');
      $thisModel = new $modelClassName($modelname);
      $GLOBALS['models'][$modelname] = &$thisModel;
			$this->$modelname = &$thisModel;
			if($modelname == $this->name) 
				$this->model = &$thisModel;
			return($thisModel);
		}
	}
}

?>