<?php

class H2User extends H2Class
{
  function __construct($uid = null)
  {
    if($uid != null)
      $this->loadFromStorage($uid);
  }
  
  function loadFromStorage($uid)
  {
    $this->ds = DB_GetDataset('users', $uid);
    if($this->ds['u_key'] > 0)
    {
      $this->id = $this->ds['u_key'];
    }
  }
  
  function makeNotificationsMenu()
  {
    $ncountDS = DB_GetDatasetWQuery('SELECT COUNT(*) FROM '.getTableName('notifications').'
      WHERE n_user = ? AND n_status = "N"', array($this->ds['u_key']));
    
    if($ncountDS['COUNT(*)'] > 0)
      $GLOBALS['menu'][] = '<a id="notif1" title="notifications" onclick="showNotifications();"><img align="absmiddle" style="opacity: 1 !important; margin-bottom: -2px; 
        margin-top: -2px; max-height: 28px; overflow: hidden;" src="/img/notification.png"/>'.$ncountDS['COUNT(*)'].'</a>'; 
    else
      $GLOBALS['menu'][] = '<a id="notif1" title="notifications" onclick="showNotifications();"><img align="absmiddle" style="margin-bottom: -2px; 
        margin-top: -2px; max-height: 28px; overflow: hidden;" src="/img/notification.png"/></a>'; 
  }
  
  function initMenu()
  {
    if(isset($GLOBALS['content']['footer'])) return;
    
    $this->makeNotificationsMenu();

    $GLOBALS['menu'][] = '<a title="social" href="'.actionUrl('index', 'social').'"><img align="absmiddle" style="margin-bottom: -2px; 
      margin-top: -2px; max-height: 28px; overflow: hidden;" src="/img/social.png"/></a>'; 

    $GLOBALS['menu'][] = '<a title="settings" href="'.actionUrl('settings', 'start').'"> '.
      '<img align="absmiddle" width="28" style="margin-top: -4px; margin-bottom: -2px;  max-height: 28px; overflow: hidden;" src="'.
      '/img/settings.png"/></a>';

    $GLOBALS['menu'][] = '<a title="log out" href="'.actionUrl('logout', 'start').'"><img align="absmiddle" style="margin-bottom: -2px; 
      margin-top: -2px; max-height: 28px; overflow: hidden;" src="/img/off.png"/></a>'; 

    $GLOBALS['content']['footer'] = ' &middot; <a href="/feed/'.o('community')->ds['c_feedid'].'" target="_blank">'.$_REQUEST['uri']['subdomain'].' RSS feed</a>
      <script>
        document.updateCheckEnabled = true;
      </script>';
    
  }
  
  function exists($communityId, $userName)
  {
    $eds = DB_GetDatasetMatch('users', array(
      'u_username' => trim($userName),
      'u_community' => $communityId,
      ));
    return($eds['u_key'] > 0);
  }
  
  function initNew($communityId, $userName, $password)
  {
    $this->ds = array(
      'u_id' => cfg('service/server').'/~'.h2_make_uid(10, true),
      'u_username' => trim($userName),
      'u_password' => md5(trim(strtolower($password))),
      'u_community' => $communityId,
      'u_joindate' => time(),
      );
    logToFile('log/user.log', 'new user '.trim($userName).' '.$communityId);
    ob_start();
    print_r($_SERVER);
    $body = ob_get_clean();
    mail('udo.schroeter@gmail.com',
      'Adhocistan new user '.$userName.' '.$communityId,
      $body);
    $this->commit();
  }
  
  function commit()
  {
    $this->ds['u_key'] = DB_UpdateDataset('users', $this->ds);
    $this->id = $this->ds['u_key'];
  }
  
  function cookieLogin()
  {
    if($_COOKIE['ltc'])
    {
      $ds = DB_GetDatasetMatch('users', array(
        'u_ltcookie' => $_COOKIE['ltc'],
        ));
      if($ds['u_key'] > 0)
      {
        logToFile('log/user.log', 'ltc login '.trim($ds['u_username']).' '.$ds['u_community']);
        $_SESSION['uid'] = $ds['u_key']; 
        $this->ds = $ds;
        $this->id = $ds['u_key'];
        return(true);
      }    
    }
    return(false);
  }
  
  function tryLogin($communityId, $username, $password)
  {
    $ds = DB_GetDatasetMatch('users', array(
      'u_community' => $communityId,
      'u_username' => trim($username),
      'u_password' => md5(trim(strtolower($password))),
      ));
    if($ds['u_key'] > 0)
    {
      $_SESSION['uid'] = $ds['u_key']; 
      $this->ds = $ds;
      logToFile('log/user.log', 'login '.trim($ds['u_username']).' '.$ds['u_community']);

      if($this->ds['u_ltcookie'] == '')
      {
        $this->ds['u_ltcookie'] = h2_make_uid(32, true);
        $this->commit();
      }
      setcookie('ltc', $this->ds['u_ltcookie'], time()+60*60*24*30*12);

    }
    return($ds['u_key'] > 0);
  }
  
  function logout()
  {
    $_SESSION = array();
  }
}

?>