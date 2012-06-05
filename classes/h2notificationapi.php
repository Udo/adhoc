<?php

class H2NotificationAPI extends H2Class
{

  function __construct($userId = null)
  {
    $this->communityId = o('community')->id;
    if($userId == null)
      $this->userId = o('user')->id;
    else
      $this->userId = $userId;
  }

  function updateNotification($nds)
  {
    $nds['n_user'] = first($nds['n_user'], $this->userId);
    $nds['n_stamp'] = time();
    $nds['n_count']++;
    DB_UpdateDataset('notifications', $nds);
  }  

  function getNotification($key)
  {
    $nds = DB_GetDataset('notifications', $key);
    $nds['n_key'] = $key;
    return($nds);
  }

  function addReplyNotification($fromUserId, $postId)
  {
    $this->addGeneralReplyNotification('r', $fromUserId, $postId);
  }
  
  function addConversationNotification($participantId, $fromUserId, $postId)
  {
    $this->addGeneralReplyNotification('c', $fromUserId, $postId, $participantId);
  }
  
  function markPostAsRead($postId)
  {
    DB_Update('UPDATE '.getTableName('notifications').' 
      SET n_status = "R"
      WHERE n_user = ? AND n_postref = ?', array($this->userId, $postId));
  }
  
  function addGeneralReplyNotification($type, $fromUserId, $postId, $participantId = null)
  {
    if($participantId == null) $participantId = $this->userId;
    $key = $participantId.'/'.$type.'/'.$postId;

    $nds = $this->getNotification($key);
    $nds['n_user'] = $participantId;

    $nds['n_type'] = $type;
    $nds['n_postref'] = $postId;
    $nds['n_fromuser'] = $fromUserId;
    $nds['n_status'] = 'N';
    
    $this->updateNotification($nds);
  }
  
  function getCurrentItems()
  {
    return(DB_GetList('SELECT * FROM '.getTableName('notifications').' WHERE
      n_user = ?
      ORDER BY n_status ASC, n_stamp DESC
      LIMIT 10', array($this->userId)));
  }

}

?>