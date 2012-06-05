<?php

class SocialModel extends H2Model
{
  function __construct()
  {
	  $this->killFile = new H2KillFile();  
	  $this->communityId = o('community')->id;
  }
  
  function getMembers($communityId)
  {
    return(DB_GetList('SELECT * FROM '.getTableName('users').' 
      WHERE u_community = ?
      ORDER BY u_username', array($communityId)));
  }
  
  function isMember($id)
  {
    $uds = DB_GetDataset('users', $id);
    return($uds['u_community'] == $this->communityId);
  }
  
  function blockUser($id)
  {
    if($this->isMember($id))
      $this->killFile->blockUserById($id);
  }
  
  function unblockUser($id)
  {
    if($this->isMember($id))
      $this->killFile->unblockUserById($id);
  }

}


?>