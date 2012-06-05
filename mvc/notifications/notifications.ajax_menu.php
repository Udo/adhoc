<?php

if(sizeof($this->items) == 0)
{
  ?><div style="color: gray">no new notifications</div><?
}
else
  foreach($this->items as $item)
  {
    switch($item['n_type'])
    {
      case('r'): // reply 
      {
        $uds = getUserRecord($item['n_fromuser']);
        $pds = $this->model->getPost($item['n_postref']);
        $pdata = json_decode($pds['p_data'], true);
        ?><div class="<?= $item['n_status'] == 'N' ? 'unread' : '' ?>">
        
          <div style="float:left; margin-right: 8px;"><?= getUserImage($uds) ?></div>
          
          <div style="margin-left: 40px;">
            <? displayEntity($uds); ?>
            <a href="/post/read/<?= $pds['p_key'] ?>">replied to your post
            <?= strip_tags($this->model->getShortText($pdata['text'])) ?>
            from <?= ageToString($pdata['time']) ?></a>
          </div>
        
        </div><?
        break;
      }
      case('c'): // conversation
      {
        $uds = getUserRecord($item['n_fromuser']);
        $pds = $this->model->getPost($item['n_postref']);
        $ods = getUserRecord($pds['p_owner']);
        $pdata = json_decode($pds['p_data'], true);
        ?><div class="<?= $item['n_status'] == 'N' ? 'unread' : '' ?>">
        
          <div style="float:left; margin-right: 8px;"><?= getUserImage($uds) ?></div>
          
          <div style="margin-left: 40px;">
            <? displayEntity($uds); ?>
            replied to <? displayEntity($ods) ?>'s <a href="/post/read/<?= $pds['p_key'] ?>">post
            <?= strip_tags($this->model->getShortText($pdata['text'])) ?>
            from <?= ageToString($pdata['time']) ?></a>
          </div>
        
        </div><?
        break;
      }
    
    }
  }

?>