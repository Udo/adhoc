<h2>~<?= $this->uds['u_username'] ?></h2>
<?

  $actions = array();
  $actions[] = getUserBlockLink($this->uds['u_key'], $this->killFile).' this user';
  
  $actions[] = $this->uds['u_postcount'].' post'.($this->uds['u_postcount'] == 1 ? '' : 's');

  if($this->uds['u_role'] == 'A') 
    $actions[] = strtoupper(substr($this->uds['u_username'], 0, 1)).'. is an adminstrator';

  if($this->uds['u_joindate'] > 0) 
    $actions[] = 'joined on '.date('Y-m-d', $this->uds['u_joindate']);
    
  if(o('user')->ds['u_role'] == 'A')
  {
    if($this->uds['u_role'] == 'A') 
      $actions[] = '<a onclick="doSwitch(\'info\', \'demote\', { \'id\' : '.$this->uds['u_key'].' })">demote to user</a>';
    else
      $actions[] = '<a onclick="doSwitch(\'info\', \'promote\', { \'id\' : '.$this->uds['u_key'].' })">promote to admin</a>';
    if($this->uds['u_banned'] == 'N') 
      $actions[] = '<a onclick="doSwitch(\'info\', \'ban\', { \'id\' : '.$this->uds['u_key'].' })">ban user from posting</a>';
    else
      $actions[] = '<a onclick="doSwitch(\'info\', \'unban\', { \'id\' : '.$this->uds['u_key'].' })">lift ban</a>';
  }

  ob_start();
?>
<div class="postitem">
  <div style="height: 216px; overflow: auto;">
    <img src="<?= first($this->uds['u_pic'], 'http://www.gravatar.com/avatar/'.md5($this->uds['u_username']).'?s=216&d=monsterid') ?>" width="100%"/>
  </div>
  <div style="max-height: 300px; overflow: auto; margin-top: 8px;margin-bottom: 8px;">
    <? print(nl2br(htmlspecialchars($this->profile['aboutme']))); ?>
  </div>
  <ul><?
  foreach($actions as $a)
  {
    ?><li><?= $a ?></li><?
  }
  ?></ul>
</div>

<?php
  $displayItems = ob_get_clean();

  $ignoreKillfile = true;
  $condition = ' AND p_owner = "'.($this->uds['u_key']).'" ';
  include('templates/stream.php');
?>  

<script>

  refreshBase = '/~<?= $this->uds['u_key'] ?>';

</script>