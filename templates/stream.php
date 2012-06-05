<?php

include_once('templates/post.php');

$bucketSize = 30;
$offset = $_REQUEST['o'];

if(isset($selectTagKey))
{
  $join = ' LEFT JOIN '.getTableName('tagrel').' ON (tr_post = p_key AND tr_tag = '.$selectTagKey.') ';
  $condition = ' AND tr_tag > 0 ';
}

$killConditions = '';
if(!$ignoreKillfile)
{
  $kf = new H2KillFile();
  $killList = $kf->getKillConditions();
  if(sizeof($killList) > 0)
    $killConditions = ' AND ('.implode(' OR ', $killList).')';
}

$postList = DB_GetList('SELECT * FROM '.getTableName('posts').'
  '.$join.'
  WHERE p_community = ? AND p_deleted != "Y" AND p_parent = 0 '.first($condition, '').'
  '.$killConditions.'
  ORDER BY p_key DESC
  LIMIT '.($offset*$bucketSize).','.$bucketSize.'
  ', array(o('community')->id));
  
$postIds = array();
foreach($postList as $pds)
  $postIds[] = 'l_post = '.$pds['p_key'].' OR l_parent = '.$pds['p_key'];
$GLOBALS['mylikes'] = array();
if(sizeof($postIds) > 0) foreach(DB_GetList('SELECT l_text, l_post FROM '.getTableName('likes').' 
  WHERE l_user = ? AND ('.implode(' OR ', $postIds).')', 
  array(o('user')->id)) as $likeDS)
  $GLOBALS['mylikes'][$likeDS['l_post']] = $likeDS['l_text'];

if(!$isAjax && !$skipContainer)
{
  ?><div id="stream" class="masonry_container"><?
}

if(isset($displayItems))
{
  print($displayItems);
}

if(o('user')->ds['u_postcount'] < 1 && $offset == 0 && time()-o('user')->ds['u_joindate'] < 60*10)
  include('templates/newuser.posts.php');

foreach($postList as $postItem)
{
  $postData = json_decode($postItem['p_data'], true);
  $postData['id'] = $postItem['p_key'];
  if($postItem['p_meta'] != '') $postData['meta'] = json_decode($postItem['p_meta'], true);
  displaySinglePost($postData);
}

if(!$isAjax)
{
  if(!$skipContainer) print('</div>');
  ?>
  <script>
  
  // endless scrolling fiasco
  
  document.infiniOffset = 0;
  document.scrollLoading = false;
  
  $(window).scroll(function() {
    var docheight = $(document).height();
    var height = $(window).height();
    var scrollTop = $(window).scrollTop();
    var scrollHeight = ((docheight-scrollTop)-height);
    if(scrollHeight < 128 && !document.scrollLoading) infiniLoad(<?= ($selectTagKey > 0 ? $selectTagKey : '')  ?>);
    });
  
  
  </script><div id="scrollind"></div><?php
}


