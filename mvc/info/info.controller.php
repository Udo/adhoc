<?php

class InfoController extends H2Controller
{
	function __init()
	{
    o()->value('content_class', 'narrow');
	}
	
	function index()
	{
	  
	}
	
	function about()
	{
	
	}
	
	function bug()
	{
	
	}
	
	function changeUserProperty($field, $value)
	{
	  $this->accessPolicy('post,admin');
    if($this->loadUserDS($_REQUEST['id']))
    {
      $this->uds[$field] = $value;
      DB_UpdateDataset('users', $this->uds);
    }	  
	}

	function ajax_promote()
	{
    $this->changeUserProperty('u_role', 'A');
	}

	function ajax_demote()
	{
    $this->changeUserProperty('u_role', 'U');
	}

	function ajax_ban()
	{
    $this->changeUserProperty('u_banned', 'Y');
	}

	function ajax_unban()
	{
    $this->changeUserProperty('u_banned', 'N');
	}

  function loadUserDS($id)
  {
    $uds = DB_GetDataset('users', $_REQUEST['id'], 'u_key');
    if(sizeof($uds) > 0 && o('community')->id == $uds['u_community'])
    {
      $this->uds = $uds;
      return(true);
    }
    return(false);
  }
	
	function user()
	{
    $this->accessPolicy('auth');
    o()->value('content_class', 'wide');

	  include_once('templates/post.php');
	  $this->killFile = new H2KillFile();

    if($this->loadUserDS($_REQUEST['id']))
    {
      $profileKey = 'profile/'.$this->uds['u_key'];
      $this->profile = nv_retrieve($profileKey);
      $GLOBALS['l10n']['user'] = $this->uds['u_username'];
    }
    else
      die('Error: user not found.');
	}
	
}

?>