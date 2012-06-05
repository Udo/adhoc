<div id="stream" class="masonry_container"><?php

include_once('templates/post.php');

$post = new H2Post($this->model->communityId, $_REQUEST['parts'][2]);
$data = $post->data;
$data['meta'] = $post->meta;
$pid = $post->ds['p_key'];
$ownerRecord = getUserRecord($data['owner']);

$napi = new H2NotificationAPI();
$napi->markPostAsRead($pid);

?><div class="postitem" style="width: 516px;margin-top: 8px;">
  <? displayEntity($ownerRecord); ?>: 
  <? displayText($data, false); ?>
  <div class="postactions">
    <div><?
      $likeList = DB_GetList('SELECT l_user, u_username FROM '.getTableName('likes').' 
        LEFT JOIN '.getTableName('users').' ON (u_key = l_user)
        WHERE l_post = ?
        ORDER BY l_key DESC
        LIMIT 100', array($pid));
      if(sizeof($likeList) > 0)
      {
        foreach($likeList as $li) 
        {
          if($li['l_user'] == o('user')->id)
            $GLOBALS['mylikes'][$pid] = true;
          $likeUsrs[] = '<a href="/~'.$li['l_user'].'">'.htmlspecialchars($li['u_username']).'</a>';
        }
        print(implode(', ', $likeUsrs).' like'.(sizeof($likeList) == 1 ? 's' : '').' this post.');
      }
    ?></div>
    <?= getLikeLinkFor($pid) ?> &middot;
    <a onclick="openComment('<?= $pid ?>');">comment</a> &middot;
    <a onclick="window.location.href = window.location.href;">refresh</a>
  </div>
</div><?

if(is_array($data['attachments']))
{
  foreach($data['attachments'] as $at) if($at['size'][0] > 0)
  {
    $size = $at['size'];
    if($size[0] > 700)
    {
      $size[1] = ($size[1]/$size[0])*700;
      $size[0] = 700;
    }
    ?><div style="width: <?= $size[0] ?>px; height: <?= $size[1] ?>px; overflow: auto;"><?php
    ?><img width="100%" src="<?= $at['url'] ?>"/><?php
    ?></div><?php
  }
} 

?><?

$comments = array_reverse(DB_GetList('SELECT * FROM '.getTableName('posts').' 
  WHERE p_parent = ? AND p_deleted = "N" 
  ORDER BY p_key DESC
  LIMIT 200', array($pid)));
  
?>
<div style="width: 518px; margin-top: 8px;">

  <div class="commentlist" id="comments<?= $pid ?>"><?php

foreach($comments as $cds)
{
  $cdata = json_decode($cds['p_data'], true);
  $cdata['meta'] = json_decode($cds['p_meta'], true);
  $cdata['id'] = $cds['p_key'];
  displaySingleComment($cdata, true);
}

?>
  </div>

  <div>
      <div id="commenteditor<?= $pid ?>"><div onclick="openComment('<?= $pid ?>');" class="commentshim">click here to comment</div></div>
      <div style="display:none" id="commentshim<?= $pid ?>"><div onclick="openComment('<?= $pid ?>');" class="commentshim">click here to comment</div></div>
  </div>

</div>
</div>