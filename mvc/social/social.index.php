<h1>Community Members</h1>
<br/>
<div class="masonry_container"><?php

foreach($this->members as $mds)
{
  $id = $mds['u_key'];
  $actions = array();
  $permaLink = '/~'.urlencode($mds['u_key']);
  if($mds['u_joindate'] > 0) $actions[] = date('Y-m-d', $mds['u_joindate']);
  if($mds['u_postcount'] > 0) $actions[] = '<a href="'.$permaLink.'">'.$mds['u_postcount'].' post'.($mds['u_postcount'] == 1 ? '' : 's').'</a>';
  $actions[] = getUserBlockLink($mds['u_key'], $this->model->killFile);
  ?><div class="postitem" id="pi<?= $id ?>">
    <div class="image"><?= getUserImageFromDS($mds, 32) ?></div>
      <div class="postext"><?= '<a href="'.$permaLink.'" style="color: gray">'.$mds['u_username'].'</a>' ?>
        <div class="postactions">
          <?= implode(' &middot; ', $actions) ?>
        </div>
      </div>
  </div><? 
}  

?></div>