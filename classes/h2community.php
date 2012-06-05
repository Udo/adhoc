<?php

class H2Community extends H2Class
{
  function __construct($name = null)
  {
    if($name != null)
    {
      $this->name = H2Syntax::safename($name);
      if(strlen($this->name) > 0)
        $this->loadFromStorage();
    }
  }

  function loadFromStorage()
  {
    $this->ds = DB_GetDataset('communities', $this->name, 'c_name');
    $this->id = $this->ds['c_key'];
  }

  function initNew($fromDS)
  {
    $this->name = H2Syntax::safename($this->name);
    $fromDS['community'] = $this->name;
    $fromDS['username'] = substr(trim(strip_tags(str_replace(' ', '_', $fromDS['username']))), 0, 24);
    if($this->name != '')
    {
      $this->ds = array(
        'c_name' => $this->name,
        'c_pwd' => md5(trim(strtolower($fromDS['cpwd']))),
        'c_review' => $fromDS['review']==true ? 'Y' : 'N',
        'c_public' => trim($fromDS['cpwd']) == '' ? 'Y' : 'N',
        'c_feedid' => h2_make_uid(16, true),
        );
      $this->commit();
      $_SESSION['communitySettings'] = $fromDS;
      $user = new H2User();
      $user->initNew($this->ds['c_key'], $fromDS['username'], $fromDS['password']);
      $user->ds['u_role'] = 'A';
      $user->commit();
      ob_start();
      print_r($_SERVER);
      $body = ob_get_clean();
      mail('udo.schroeter@gmail.com',
        'Adhocistan new community '.$fromDS['community'].' '.$this->ds['c_key'],
        $body);
    }
  }

  function commit()
  {
    if(sizeof($this->ds) > 0)
    {
      $this->ds['c_key'] = DB_UpdateDataset('communities', $this->ds);    
      $this->id = $this->ds['c_key'];
    }
  }

}

