<?php

class SocialController extends H2Controller
{
	function __init()
	{
    $this->accessPolicy('auth');
    $this->makeMenu('index');
    $this->invokeModel();
	}
	
	function index()
	{
	  include_once('templates/post.php');
	  $this->members = $this->model->getMembers(o('community')->id);
	}
	
	function ajax_blockuser()
	{
    $this->model->blockUser($_REQUEST['id']);
	}
	
	function ajax_unblockuser()
	{
    $this->model->unblockUser($_REQUEST['id']);
	}
	
}

?>