<?php



?><div class="toolbar"><?

if($this->tagName != '')
{
  ?>Showing <a href="/home/tag/<?= urlencode($this->tagName) ?>">#<?= htmlspecialchars($this->tagName) ?></a><?
}

$activeTags = DB_GetList('SELECT * FROM '.getTableName('tagnames').'
  WHERE t_community = ? AND t_postcount > 1
  ORDER BY t_postcount DESC, t_lastupdated DESC
  LIMIT 25', array(o('community')->id));
  
$tagLinks = array();
foreach($activeTags as $tds)
  if($tds['t_name'] != $this->tagName)
    $tagLinks[] = '<a href="/home/tag/'.urlencode($tds['t_name']).'">#'.htmlspecialchars($tds['t_name']).'</a>';

if(sizeof($tagLinks) > 0) 
  print(' Trending tags: '.implode(' &middot; ', $tagLinks));
  

?>

</div>
