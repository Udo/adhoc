<?php

if($this->header) print('<h1>'.$this->header.'</h1>');

include('mvc/start/login.php');

$cds = DB_GetDataset('communities', $_REQUEST['uri']['subdomain'], 'c_name');
if(sizeof($cds) == 0)
{
  die('Error: this community does not exist.');
}

?>
<h2>
Create an account
</h2>
<div class="description">
  Want to join this community? Simply create an account now:
</div>
<?

$nlogin = o(new CQForm('nlogin'))
  ->add('string', 'username', 'caption=Enter a username')
  ->add('password', 'password', 'caption=Enter a password,validate=notempty')
  ->add('password', 'password2', 'caption=(repeat password),validate=notempty');
$nlogin->cds = $cds;

if($cds['c_public'] != 'Y')
{
  $nlogin->add('html', '<div class="description">To join this community, you need to know the secret community password:</div>');
  $nlogin->add('string', 'cpwd', 'caption=Community password');
}  

$nlogin
  ->add('submit', 'login', 'caption=Create My Account &gt;')
  ->receive(function(&$ds, $f) {
  
    if($ds['password'] != $ds['password2'])
      $f->errors['password2'] = 'error: passwords do not match';
      
    $ds['username'] = substr(trim(strip_tags(str_replace(' ', '_', $ds['username']))), 0, 24);
    if($ds['username'] == '')
      $f->errors['username'] = 'this field may not by empty';
  
  })
  ->onsubmit(function($ds, $f) { 
    
    if($f->cds['c_public'] != 'Y' && md5($ds['cpwd']) != trim(strtolower($f->cds['c_pwd'])))
    {
      $f->errors['cpwd'] = 'this password is not correct';
    }
    else
    {
      $exu = new H2User();
      if($exu->exists($f->cds['c_key'], $ds['username']))
        $f->errors['username'] = 'this username is already taken';
      else
      {
        $u = new H2User();
        $u->initNew($f->cds['c_key'], $ds['username'], $ds['password']);
        $u->tryLogin($f->cds['c_key'], $ds['username'], $ds['password']);
        o('controller')->redirect('index', 'home');
      }
    }
    
  })
  ->display();

?>