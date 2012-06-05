<?php

include('templates/tagnav.php');

include('templates/post.thingy.php');

if(isset($this->tagKey))
  $selectTagKey = $this->tagKey;
include('templates/stream.php');

?>