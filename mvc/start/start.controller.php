<?php

class StartController extends H2Controller
{
	function __init()
	{
    include_once('lib/cq-forms.php');
    o()->value('content_class', 'narrow');
  }
	
	function index()
	{
	  if($_REQUEST['uri']['subdomain'])
	  {
	    $this->viewName = 'index-subdomain';
	    $this->header = 'Welcome to '.first(o('community')->ds['c_caption'], o('community')->ds['c_name']);
	    $this->description = 'Are you already a member of this community? Log in here:';
	  }
	  else
	  {
	    $this->description = 'Already part of an Adhocistan community? Log in here:';
	  }
	}
	
	function settings()
	{
	  $this->accessPolicy('auth');
	
	}

	function created()
	{
	  if(!isset($_SESSION['communitySettings']))
	    $this->redirect('index');
	  $this->community = new H2Community($_SESSION['communitySettings']['community']);
	}
	
	function logout()
	{
	  o('user')->logout();
	  $this->redirect('index', 'start');
	}
}

?>