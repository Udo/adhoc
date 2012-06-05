<?= '<?xml version="1.0" encoding="UTF-8" ?>' ?> 
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">

<channel>
        <title>Adhocistan <?= $this->feedId ?></title>
        <link>http://<?= $_REQUEST['uri']['host'] ?>/</link>
        <lastBuildDate><?= gmdate('D, d M Y H:i:s') ?> +0000</lastBuildDate>
        <pubDate><?= gmdate('D, d M Y H:i:s', $this->newestFeedItem) ?> +0000</pubDate>
        <ttl>1800</ttl>
  <?php
  foreach($this->items as $item) {
  $title = htmlspecialchars('Post by '.$item['owner_record']['name'].' in ['.$_REQUEST['uri']['subdomain'].']');
  $content = $item['text'];
  $permaLink = 'http://'.$_REQUEST['uri']['host'].'/post/read/'.$item['id'];
  
  if(is_array($item['attachments']))
  {
    ob_start();
    foreach($item['attachments'] as $at) if($at['size'][0] > 0)
    {
      $size = $at['size'];
      $imgHeight = ($size[1]/$size[0])*216;
      ?><a href="<?= $permaLink ?>"><img src="<?= $at['url'] ?>" class="attachment" height="<?= $imgHeight ?>"/></a><?php
    }
    $content .= '<br/>'.ob_get_clean();
  }   

  ?>
        <item>
                <title><?= $title ?></title>
                <description>
                <![CDATA[
                  <?= $content ?> 
                ]]>
                </description>
                <content:encoded>
                <![CDATA[
                  <?= $content ?> 
                ]]>
                </content:encoded>
                <link><?= $permaLink ?></link>
                <guid><?= $permaLink ?></guid>
                <pubDate><?= gmdate('D, d M Y H:i:s', $item['time']) ?> +0000</pubDate>
        </item>
 <?php
 }
 ?>
</channel>
</rss>