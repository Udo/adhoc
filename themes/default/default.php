<?php

header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: SAMEORIGIN');
header('X-UA-Compatible: chrome=1');

$co = o('community');
if($co) $cname = $co->ds['c_caption'];

$usr = o('user');
if($usr && $usr->id > 0) $usr->initMenu();

?><!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
  <head>
    <title><?php echo htmlspecialchars(cfg('page/title', 'unnamed')).' &middot; '.first($cname, $_REQUEST['uri']['host']) ?></title>
		<base href="<?= $_REQUEST['uri']['scheme'] ?>://<?= $_REQUEST['uri']['host'] ?>/"/>
    <script type="text/javascript" src="lib/all.js.php"></script>   
    <script type="text/javascript" src="ext/blueimp/js/all.js.php"></script>   
    <link type="text/css" rel="stylesheet" href="ext/blueimp/css/all.css.php"/> 
    <link type="text/css" rel="stylesheet" href="themes/default/all.css.php"/> 
		<link rel="icon" type="image/png" href="/themes/default/adhocistan2.png"/>
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
  </head>
  <body class="<?= o()->value('content_class') ?>">
  	<noscript><div></div><p>Attention: Javascript is disabled on your browser. Hubbub does not work without Javascript. Please enable it before you proceed.</p></noscript>
  
    <div id="header"><div id="header-inner">
      <div id="menu"><?= implode(' &nbsp; ', $GLOBALS['menu']) ?></div>
      <div id="site-title">
      <a href="<?= $_REQUEST['uri']['scheme'] ?>://<?= htmlspecialchars($_REQUEST['uri']['host']) ?>"><?= 
        htmlspecialchars(first($cname, $_REQUEST['uri']['host'])) ?></a></div></div>
	  </div>
	    
    <div id="content">
      <div id="content-inner"><?php echo $GLOBALS['content']['startuperrors'].$GLOBALS['content']['main'].$GLOBALS['content']['addendum'] ?></div>
    </div>

    <div id="footer">
      <div id="footer-inner">
        <br/><br/>
        <a href="/info/about">about</a> &middot;
        <a href="/info/bug">bug report</a> <?= $GLOBALS['content']['footer'] ?>
      </div>
    </div>

    <div style="display:none;" class="overlay" id="m1">123</div>

    <script>
    
      $('.masonry_container').masonry({ 
        'gutterWidth' : 12
      }); 
      
    </script>
  </body>
  <?php profile_point('page template'); ?><!--
   
<?= implode("\n", $GLOBALS['profiler_log']); ?> 
RAM usage: <?= ceil(memory_get_peak_usage()/1024) ?> kBytes 

-->
</html>