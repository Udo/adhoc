<?php

$isAjax = true;

ob_start();

if(isset($this->tagKey))
  $selectTagKey = $this->tagKey;
include('templates/stream.php');

print(json_encode(array('finished' => sizeof($postList) < $bucketSize, 'html' => ob_get_clean())));

?>