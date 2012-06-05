<?php

class PostController extends H2Controller
{
	
	function __init()
	{
    include_once('templates/post.php');
    $this->accessPolicy('auth');
    $this->invokeModel();
  }
	
	function index()
	{

	}
	
	function ajax_load()
	{
    $this->skipView = false;
    if($_REQUEST['tag'] > 0)
    {
	    $this->tagKey = $_REQUEST['tag'];
    }
	}
	
	function upload()
	{
	 
	}
	
	function read()
	{
	
	}
	
	function ajax_delete()
	{
	  $this->accessPolicy('post');	  
	  $this->model->deletePost($_REQUEST['pid']+0);
	}
	
	function ajax_like()
	{
	  $this->accessPolicy('post');
	  $this->model->likePost($_REQUEST['pid']+0, $_REQUEST['liketype']);
	}
	
	function ajax_comment()
	{
	  #$this->accessPolicy('post');
	  $result = array();

	  if($this->model->createComment($_REQUEST['parent']+0, H2Syntax::saneText($_REQUEST['text'])))
	  {
      ob_start();
      displaySingleComment($this->model->comment->data);
      $result['html'] = ob_get_clean();
	  }

	  print(json_encode($result));
	}

	function ajax_undelete()
	{
	  $this->accessPolicy('post');
	  $this->model->undeletePost($_REQUEST['pid']+0);
	}

	function ajax_do()
	{
	  $result = array();
	  $this->accessPolicy('post');

	  if($this->model->createPost(H2Syntax::saneText($_REQUEST['text'])))
	  {
      ob_start();
      foreach($this->model->posts as $post)
        displaySinglePost($post->data);
      $result['html'] = ob_get_clean();
	  }

    print(json_encode($result));
	}
	
}

?>