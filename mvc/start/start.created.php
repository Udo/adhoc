<?php
  $url = $_REQUEST['uri']['scheme'].'://'.$_SESSION['communitySettings']['community'].'.'.cfg('service/server').'/';
?><h1>Your community "<?= $_SESSION['communitySettings']['community'] ?>" has been created.</h1>
<div class="description">
  Congratulations, you successfully created your community.  
</div>
<h2>➊ Please review your data</h2>
<div class="banner">
  Your community: <b><a href="<?= $url ?>" target="_blank"><?= htmlspecialchars($_SESSION['communitySettings']['community']) ?><span style="color:gray;">.<?= cfg('service/server') ?></span></a></b><br/>
  Your username: <b><?= htmlspecialchars($_SESSION['communitySettings']['username']) ?></b><br/>
  Your password: <b><?= htmlspecialchars($_SESSION['communitySettings']['password']) ?></b><br/>
  Your community password: <b><?= htmlspecialchars(first($_SESSION['communitySettings']['cpwd'], '(none)')) ?></b><br/>
</div>
<h2>➋ Enter your community</h2>
<div class="description">Your new community is now ready.</div>
Click here to go to <a href="<?= $url ?>" target="_blank"><?= $url ?></a> and log in with your administrator username and password (as shown above).