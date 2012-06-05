<?php

class FeedController extends H2Controller
{
	function __init()
	{
    #$this->accessPolicy('auth');
    #$this->menu = $this->makeMenu('index');
    #include_once('mvc/home/wikilist.php');
    $GLOBALS['config']['page']['template'] = 'blank';
		header('content-type: application/xml;charset=UTF-8');
	}
	
	function index()
	{
	  include_once('templates/post.php');
    $this->items = array();
    foreach(DB_GetList('SELECT * FROM '.getTableName('posts').' 
      WHERE p_community = ? AND p_deleted != "Y" AND p_parent = 0
      ORDER BY p_key DESC
      LIMIT 30
      ', array(o('community')->id)) as $ds)
    {
      $data = json_decode($ds['p_data'], true);
      $data['owner_record'] = getUserRecord($data['owner']);
      
      if(!$this->newestFeedItem) $this->newestFeedItem = $data['time'];
      
      $this->items[] = $data;
    }
	}
	
	function __call($name, $params)
	{
	  $this->viewName = 'index';
	  $this->feedId = $name;
	  $this->community = new H2Community($_REQUEST['uri']['subdomain']);
	  if($this->community->ds['c_feedid'] != $this->feedId)
	  {
	    die('Error: no such feed');
	  }
	  else
	  {
	    $this->index();
	  }
	}
	
}

?>