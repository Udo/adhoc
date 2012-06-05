<h2>
Log In To Your Community
</h2>
<div class="description">
  <?= $this->description ?>
</div>
<?

$login = o(new CQForm('login'))
  ->add('hidden', 'community', array('caption' => 'Community Name', 'default' => $_REQUEST['uri']['subdomain']))
  ->add('string', 'username', 'caption=Your Username')
  ->add('password', 'password', 'caption=Your Password')
  ->add('submit', 'login', 'caption=Log In &gt;')
  ->onsubmit(function($ds, $f) { 

    $u = new H2User();
    $co = new H2Community($ds['community']);
    if($u->tryLogin($co->id, $ds['username'], $ds['password']))
    {
      o('controller')->redirect('index', 'home');
    }
    else
    {
      $f->errors['password'] = 'wrong username or password :-(';
    }
    
  })
  ->display();

?>