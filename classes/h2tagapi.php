<?php

class H2TagAPI extends H2Class
{
  function __construct($communityId = null)
  {
    if($communityId)
      $this->communityId = $communityId;
    else
      $this->communityId = o('community')->id;
  }
  
  function getTag($tagName)
  {
    if($tagName == '') return(array());
    $ds = DB_GetDatasetMatch('tagnames', array(
      't_name' => $tagName,
      't_community' => $this->communityId,
      ));
    if($ds['t_key'] == 0)
      $ds['t_key'] = DB_UpdateDataset('tagnames', $ds);
    return($ds);
  }

  function removeTagsFromPost($postId)
  {
    DB_Update('DELETE FROM '.getTableName('tagrel').' WHERE tr_post = ?', array($postId));
  }

  function registerTagToPost($postId, $tagName)
  {
    $tag = $this->getTag($tagName);
    if(sizeof($tag) > 0)
    {
      $ds = array( 
        'tr_tag' => $tag['t_key'],
        'tr_post' => $postId,
        );
      DB_UpdateDataset('tagrel', $ds);
      DB_Update('UPDATE '.getTableName('tagnames').' SET t_lastupdated = ?, t_postcount = t_postcount+1 WHERE t_key = ?', array(time(), $tag['t_key']));
    }
  }

  function tagPost($postId, $tags = array())
  {
    $this->removeTagsFromPost($postId);    
    foreach($tags as $tagName)
      $this->registerTagToPost($postId, $tagName);
  }
  
}

?>