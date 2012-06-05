<?php
    l10n_load('templates/openidlogin');

    $openidProviders = array(
      'google' => array('icon' => 'google.png', 'url' => 'https://www.google.com/accounts/o8/id', 'caption' => 'Google'),
      'yahoo' => array('icon' => 'yahoo.png', 'url' => 'https://me.yahoo.com', 'caption' => 'Yahoo'),
      );
    require('ext/lightopenid/openid.php');
    try {
        if(!isset($_REQUEST['openid_mode'])) {
            if(isset($_REQUEST['identity'])) {
                $openid = new LightOpenID;
                $openid->identity = $_REQUEST['identity'];
                header('Location: ' . $openid->authUrl());
            }
          } elseif($_REQUEST['openid_mode'] == 'cancel') {
              echo '<div class="banner">'.l10n('openid.cancel').'</div>';
          } else {
              $openid = new LightOpenID;
              if($openid->validate())
              {
              	$this->onOpenIDLogin($openid);
              }
              else
              {
                ?><div class="banner"><?= l10n('openid.error') ?> :-(</div><?
              }
          }
      } catch(ErrorException $e) {
          echo $e->getMessage();
      }
    ?>
    <form action="<?= actionUrl() ?>#tabs-2" method="post">
        <div style="padding-bottom: 8px;">
          <?= l10n('openid.balloon') ?>
        </div>
        <table style="margin-left: 16px;">
          <tr class="hovercell">
            <?
            foreach($openidProviders as $p)
            {
              ?><td style="border: 1px solid #bbb; cursor:pointer;" valign="center" align="center"
                onclick="document.location.href='<?= actionUrl(null, null, array('identity' => $p['url'])) ?>#tabs-2';"
                ><img src="img/openid/<?= $p['icon'] ?>"/></td><?
            }
            ?>
          </tr>
          <tr>
            <?
            foreach($openidProviders as $p)
            {
              ?><td align="center"><a href="<?= actionUrl(null, null, array('identity' => $p['url'])) ?>#tabs-2"><?= $p['caption'] ?></a></td><?
            }
            ?>
          </tr>
        </table>
        <div style="padding-top: 8px;">
          <?= l10n('openid.manual') ?>
          <div style="margin-left: 16px;padding-top: 8px;">
            <input type="text" name="identity" value="https://www.google.com/accounts/o8/id"/>
            <br/>
            <input type="submit" value="Log in"/>
          </div>
        </div>
    </form>