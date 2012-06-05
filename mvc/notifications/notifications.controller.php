<?php

class NotificationsController extends H2Controller
{

	function __init()
	{
    $this->accessPolicy('auth');
    $this->invokeModel();
    include_once('templates/post.php');
	}
	
	function index()
	{

	}
	
	function ajax_menu()
	{
	  $this->skipView = false;
	  $this->items = $this->model->api->getCurrentItems();
	}
	
	function ajax_check()
	{
	  o('user')->makeNotificationsMenu();
	  print(implode($GLOBALS['menu']));
	}

}


?>