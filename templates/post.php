<?php

$GLOBALS['userds'] = o('user')->ds;

function getUserRecord($id)
{
  if(is_array($id))
    $ds = $id;
  else
    $ds = DB_GetDataset('users', $id, 'u_key');
  return(array(
    'name' => $ds['u_username'],
    'id' => $ds['u_key'],
    'pic' => $ds['u_pic'],
    ));
}

function getUserImage($entity, $size = 32)
{
  return(
    '<a href="/~'.$entity['id'].'"><img width="'.$size.'" style="max-height: '.$size.'px; overflow: hidden;" src="'.
    first($entity['pic'], 'http://www.gravatar.com/avatar/'.md5($entity['name']).'?s='.$size.'&d=monsterid').
    '"/></a>');
}

function getUserImageFromDS($ds, $size = 32)
{
  return(
    '<a href="/~'.$ds['u_key'].'"><img width="'.$size.'" style="max-height: '.$size.'px; overflow: hidden;" src="'.
    first($ds['u_pic'], 'http://www.gravatar.com/avatar/'.md5($ds['u_username']).'?s='.$size.'&d=monsterid').
    '"/></a>');
}

function getLikeLinkFor($id)
{
  $liketxt = 'like';
  if(isset($GLOBALS['mylikes'][$id])) $liketxt = 'unlike';
  $result = '<a onclick="doLike('.$id.');" id="like'.$id.'">'.$liketxt.'</a>';
  return($result);
}

function displayEntity($entity)
{
  print('<a href="/~'.urlencode($entity['id']).'" style="color: gray">'.$entity['name'].'</a>');
  return;
}

function displayLikeStats($data)
{
  if($data['meta']['likes'] > 0)
  {
    ?><div class="smallmsg" style="margin-bottom: 4px;"><?php
    if($data['meta']['likes'] > 0 && isset($data['meta']['lastlike']))
      displayEntity(getUserRecord($data['meta']['lastlike']));
    if($data['meta']['likes'] > 1 && isset($data['meta']['lastlike']))
      print(' and ');
    if($data['meta']['likes'] > 1 && isset($data['meta']['lastlike']))
      print(' '.($data['meta']['likes']-1).' '.($data['meta']['likes']-1 == 1 ? 'person likes' : 'people like').' this.');
    else if($data['meta']['likes'] > 1 || !isset($data['meta']['lastlike']))
      print(' '.($data['meta']['likes']+0).' '.($data['meta']['likes']+0 == 1 ? 'person likes' : 'people like').' this.');
    else if(isset($data['meta']['lastlike']))
      print(' likes this.');
    ?></div><?     
  }
}

function displayText($data, $abbreviate = true)
{
  if($abbreviate)
    $tx = H2Syntax::abbreviate($data['text']);
  else
    $tx = H2Syntax::textToHtml($data['text']);
  if(is_array($data['tags'])) foreach($data['tags'] as $tag)
  {
    $tagLink = '<a href="/home/tag/'.urlencode($tag).'">#'.htmlspecialchars($tag).'</a>';
    $tx = str_replace('#'.$tag, $tagLink, $tx);
  }
  print(' '.$tx);
}

function displaySinglePost($data)
{  
  $actions = array();
  $ownerRecord = getUserRecord($data['owner']);
  $pid = $data['id'];
  $permaLink = '/post/read/'.$pid;

  if($data['meta']['commentcount'])
    $actions[] = '<a href="'.$permaLink.'">'.($data['meta']['commentcount']+0).' comment'.($data['meta']['commentcount'] == 1 ? '' : 's').'</a>';
  else
    $actions[] = '<a href="'.$permaLink.'">read</a>';
  $actions[] = getLikeLinkFor($data['id']);
  if($GLOBALS['userds']['u_key'] == $data['owner'] || $GLOBALS['userds']['u_role'] == 'A')
    $actions[] = '<a onclick="deletePost(\''.$pid.'\');">delete</a>';
  
  ?><div class="postitem" id="post<?= $pid ?>" data-dsid="<?= $data['id'] ?>">
    <?php
    if(sizeof($data['attachments']) == 0)
    {
    ?>
      <div class="image"><?= getUserImage($ownerRecord, 32) ?></div>
      <div class="postext">
    <?php
    }
    else
      print('<div>');
    
      displayEntity($ownerRecord); 
      displayText($data);
      
      if(is_array($data['attachments']))
      {
        $helpText = 'Tip: press the CTRL or STRG key while you click to open this post in another browser tab.';
        ?><div><?php
        foreach($data['attachments'] as $at) if($at['size'][0] > 0)
        {
          $fileSize = filesize($at['url']) / (1024*1024);
          if($fileSize < 1)
          {
            $size = $at['size'];
            $imgHeight = ($size[1]/$size[0])*216;
            ?><a title="<?= $helpText ?>" href="<?= $permaLink ?>"><img src="<?= $at['url'] ?>" class="attachment" height="<?= $imgHeight ?>"/></a><?php
          }
        }
        ?></div><?php
      }   
      ?>
      <?php
      displayLikeStats($data);
      ?>
      <div class="postactions">
      <?php
        print(ageToString($data['time']).'<br/>');
        print(implode(' &middot; ', $actions));
      ?>
      </div>
    </div>
    
    <div class="postcomments" id="comments<?= $pid ?>"><?php
    if(sizeof($data['meta']['lastcomments']) > 0)
    {
      if($data['meta']['commentcount'] > 2)
      {
        ?><a class="readmore" href="<?= $permaLink ?>">&gt; read all <?= $data['meta']['commentcount'] ?> comments</a><?php
      }
      else
      {
        ?><div class="hseparator"></div><?php    
      }
      foreach($data['meta']['lastcomments'] as $commentData) 
        displaySingleComment($commentData);
    }
    ?></div>
    <div id="commenteditor<?= $pid ?>"><div onclick="openComment('<?= $pid ?>');" class="commentshim">click here to comment</div></div>
    <div style="display:none" id="commentshim<?= $pid ?>"><div onclick="openComment('<?= $pid ?>');" class="commentshim">click here to comment</div></div>
  </div><?
}

function displaySingleComment($data, $showFullText = false)
{
  $actions = array();
  $ownerRecord = getUserRecord($data['owner']);
  $pid = $data['id'];

  $actions[] = ageToString($data['time']);
  $actions[] = getLikeLinkFor($data['id']);
  if($GLOBALS['userds']['u_key'] == $data['owner'] || $GLOBALS['userds']['u_role'] == 'A')
    $actions[] = '<a onclick="deleteComment(\''.$pid.'\');">delete</a>';

  ?><div class="comment" id="cid<?= $pid ?>">
    <div class="image"><?= getUserImage($ownerRecord, 32) ?></div>
    <div class="comtext">
      <div class="text">
        <? displayEntity($ownerRecord) ?>
        <?= $showFullText ? H2syntax::textToHtml($data['text']) : H2syntax::abbreviate($data['text']) ?>
      </div>
      <?php displayLikeStats($data); ?>
      <div class="postactions"><?php
        print(implode(' &middot; ', $actions));
      ?></div>
    </div>
  </div><?php
}

function getUserBlockLink($id, $killFile = null)
{
  if($killFile == null) $killFile = new H2KillFile();

  if($killFile->isBlocked($id)) 
    return('<a class="red" onclick="unblockUser('.$id.', event.target);">unblock</a>');
  else
    return('<a onclick="blockUser('.$id.', event.target);">block</a>'); 
}

?>