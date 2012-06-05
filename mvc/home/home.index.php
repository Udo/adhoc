<?php

/*
foreach(DB_GetList('SELECT p_key, p_community FROM ah_posts') as $postDS)
{
  $post = new H2Post($postDS['p_community'], $postDS['p_key']);
  $post->extractTagsFromText();
  $post->commit();
}
*/
include('templates/tagnav.php');

include('templates/post.thingy.php');

include('templates/stream.php');

?>