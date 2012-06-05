<?php

class NotificationsModel extends H2Model
{

  function __construct()
  {
    $this->userId = o('user')->id;  
    $this->api = new H2NotificationAPI();
  }
  
  function getUser($id)
  {
    return(DB_GetDataset('users', $id));
  }
  
  function getPost($id)
  {
    return(DB_GetDataset('posts', $id));
  }
  
  function getShortText($t)
  {
    if(strlen($t) > 0)
      return(' <span style="color:gray">"'.H2Syntax::abbreviate($t, 40).'"</span> ');
    else
      return(' ');
  }

}


?>