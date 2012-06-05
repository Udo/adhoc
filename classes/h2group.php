<?php

class H2Group extends H2Class
{

  function __construct($groupId)
  {
    $this->groupId = $groupId;
  }
  
  function updateMembers($memberList)
  {
    DB_Update('DELETE FROM '.getTableName('groupmembers').' WHERE grp_key = ?', array($this->groupId));
    $insertStatements = array(); 
    if(is_array($memberList))
    {
      foreach($memberList as $member)
        $insertStatements[] = '("'.DB_Safe($grpKey).'","'.DB_Safe($member).'","grp")';
      DB_Update('INSERT INTO '.getTableName('groupmembers').' (grp_key,grp_member,grp_status) VALUES '.implode(', ', $insertStatements));
    }  
  }

}

?>