<?php

class HomeController extends H2Controller
{
	function __init()
	{
    $this->accessPolicy('auth');
    $this->makeMenu('index');
	}
	
	function index()
	{

	}
	
	function tag()
	{
	  $this->tagName = urldecode($_REQUEST['parts'][2]);
	  $this->tagApi = new H2TagAPI();
	  $this->tagDS = $this->tagApi->getTag($this->tagName);
	  $this->tagKey = $this->tagDS['t_key'];
	  $GLOBALS['l10n']['tag'] = '#'.htmlspecialchars($this->tagName);
	}
	
}

?>