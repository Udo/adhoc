<?php

define('MAX_KILLFILE_ENTRIES', 128);

class H2KillFile extends H2Class
{

  function __construct()
  {
    $this->data = array();
    $this->userid = o('user')->id;
    $this->kfKey = 'kill/'.$this->userid;
    $this->loadFromStorage();    
  }
  
  function getKillConditions()
  {
    $result = array();
    
    if(is_array($this->data['blocked']))
      foreach($this->data['blocked'] as $blk => $t)
      {
        $result[] = ' p_owner != '.($blk+0).' ';
      }
    
    return($result);
  }
  
  function loadFromStorage()
  {
    $this->data = array();
    $data = nv_retrieve($this->kfKey);
    if(is_array($data)) $this->data = $data;
    return($this);
  }
  
  function commit()
  {
    // enforcing max killfile length (first in/first out)
    if(sizeof($this->data['blocked']) > MAX_KILLFILE_ENTRIES)
      array_splice($this->data['blocked'], 0, sizeof($this->data['blocked']) - MAX_KILLFILE_ENTRIES);
    nv_store($this->kfKey, $this->data);
  }
  
  function blockUserById($id)
  {
    $this->data['blocked'][$id] = true;
    $this->commit();
    return($this);
  }
  
  function unblockUserById($id)
  {
    unset($this->data['blocked'][$id]);
    $this->commit();
    return($this);
  }

  function isBlocked($id)
  {
    return($this->data['blocked'][$id]);
  }

}

?>