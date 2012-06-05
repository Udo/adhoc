<?php

class H2Post extends H2Class
{

  function __construct($community, $id = null)
  {
    $this->data = array();
    $this->meta = array();
    $this->communityId = $community;
    if($id != null)
      $this->loadFromStorage($id);
  }

  function loadFromStorage($id)
  {
    $ds = DB_GetDataset('posts', $id);
    if(sizeof($ds) > 0 && $ds['p_community'] == $this->communityId)
    {
      $this->ds = $ds;
      $this->data = json_decode($ds['p_data'], true);
      if($ds['p_meta'] != '') $this->meta = json_decode($ds['p_meta'], true);
      $this->data['id'] = $this->ds['p_key'];
      $this->id = $this->ds['p_key'];
    }
    return($this);
  }

  function extractTagsFromText()
  {
    $tags = array();

    foreach(explode(' ', 
      str_replace(array(chr(13), chr(10), '.', '!', '>', '<'), ' ', $this->data['text'])) as $w)
        if(substr($w, 0, 1) == '#')
          $tags[] = trim(substr($w, 1));
    
    return($tags);        
  }

  function commitTags()
  {
    $tapi = new H2TagAPI($this->communityId);
    if(sizeof($this->data['tags']) > 0)
      $tapi->tagPost($this->ds['p_key'], $this->data['tags']);
  }
  
  function getConversationParticipants($parentId)
  {
    $result = array();
    
    foreach(DB_GetList('SELECT DISTINCT(p_owner) FROM '.getTableName('posts').' WHERE p_parent = ?', array($parentId)) as $ds)
      $result[] = $ds['p_owner'];
    
    return($result);
  }

  function updateNotifications()
  {
    // if this is a response to a parent post, we need to notify the owner
    if($this->ds['p_parent'] > 0)
    {
      $pds = DB_GetDataset('posts', $this->ds['p_parent']);
      $napi = new H2NotificationAPI($pds['p_owner']);
      // notify the parent's owner if he's not identical with replier
      if($pds['p_owner'] != o('user')->id)
        $napi->addReplyNotification(o('user')->id, $pds['p_key']);
      // also, we should notify the other participants except the replier
      foreach($this->getConversationParticipants($pds['p_key']) as $uid) 
        if($pds['p_owner'] != $uid && $uid != o('user')->id)
          $napi->addConversationNotification($uid, o('user')->id, $pds['p_key']);
    }
  }

  function initNew($userid, $msg)
  {
    $msg['owner'] = $userid;
    $this->data = $msg;
    $this->data['tags'] = $this->extractTagsFromText();
    if(o('user')->ds['u_banned'] == 'N')
    {
      $this->commit();
      $this->commitTags();
      $this->updateNotifications();
    }
    return($this);
  }
  
  function countComments()
  {
    $countDS = DB_GetDatasetWQuery('SELECT COUNT(*) FROM '.getTableName('posts').' WHERE p_parent = ? AND p_deleted = "N"', array($this->data['id']));
    return($countDS['COUNT(*)']);
  }
  
  function countLikes()
  {
    $countDS = DB_GetDatasetWQuery('SELECT COUNT(*) FROM '.getTableName('likes').' WHERE l_post = ?', array($this->data['id']));
    return($countDS['COUNT(*)']);
  }
  
  function removeChild($childId)
  {
    $this->meta['commentcount'] = $this->countComments();
    $coList = $this->meta['lastcomments'];
    $this->meta['lastcomments'] = array();
    foreach($coList as $cdata)
      if($cdata['id'] != $childId) $this->meta['lastcomments'][] = $cdata;
    $this->commit();
  }
  
  function parentCacheLike($parentKey)
  {
    $parent = new H2Post($this->communityId, $parentKey);
    $coList = $parent->meta['lastcomments'];
    $parent->meta['lastcomments'] = array();
    foreach($coList as $co)
    {
      if($co['id'] == $this->data['id'])
      {
        $co = $this->data;
        $co['meta'] = $this->meta;
      }  
      $parent->meta['lastcomments'][] = $co;
    }
    $parent->commit();
  }
  
  function like($type)
  {
    $likeDS = DB_GetDatasetMatch('likes', array(
      'l_post' => $this->data['id'],
      'l_user' => o('user')->id,
      'l_parent' => $this->data['parent'],
      ));
    if($type == 'unlike')
    {
      DB_RemoveDataset('likes', $likeDS['l_key'], 'l_key');
      if($this->meta['lastlike'] == $likeDS['l_user']) unset($this->meta['lastlike']);
    }
    else
    {
      $likeDS['l_text'] = 'like';
      DB_UpdateDataset('likes', $likeDS);    
      $this->meta['lastlike'] = $likeDS['l_user'];
    }
    $this->meta['likes'] = $this->countLikes();
    if($this->data['parent'] > 0)
      $this->parentCacheLike($this->data['parent'], $type);
    $this->commit();
    return($this);
  }
  
  function remove()
  {
    $this->ds['p_deleted'] = 'Y';
    $this->commit();
    if($this->data['parent'] > 0)
    {
      $parent = new H2Post($this->communityId, $this->data['parent']);
      $parent->removeChild($this->data['id']);
    }
    return($this);
  }
  
  function registerComment($comment)
  {
    $this->meta['commentcount'] = $this->countComments();
    $this->meta['lastcomments'][] = $comment->data;
    if(sizeof($this->meta['lastcomments']) > 2)
      array_splice($this->meta['lastcomments'], 0, sizeof($this->meta['lastcomments'])-2);
    $this->commit();
  }
  
  function undoRemove()
  {
    $this->ds['p_deleted'] = 'N';
    $this->commit();
    return($this);
  }

  function commit()
  {
    if(o('user')->ds['u_banned'] == 'Y') return;
    $this->data['time'] = first($this->data['time'], time());
    $this->ds['p_community'] = $this->communityId;
    $this->ds['p_data'] = json_encode($this->data);
    $this->ds['p_meta'] = json_encode($this->meta);
    $this->ds['p_owner'] = $this->data['owner'];
    $this->ds['p_parent'] = $this->data['parent'];
    $this->ds['p_key'] = DB_UpdateDataset('posts', $this->ds);
    $this->data['id'] = $this->ds['p_key'];
    $this->id = $this->ds['p_key'];
    return($this);      
  }

}

?>