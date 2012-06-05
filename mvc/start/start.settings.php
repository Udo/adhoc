<h1>Your Profile Settings</h1>
<br/><br/>
<?php

$profileKey = 'profile/'.o('user')->ds['u_key'];
$profile = nv_retrieve($profileKey);

$settings = o(new CQForm('settings'))
  ->ds(o('user')->ds)
  ->add('readonly', 'u_username', array('caption' => 'Your nickname', 'default' => o('user')->ds['u_username']))
  ->add('html', '<div style="margin-left: 200px;">&gt; go to my <a href="/~'.o('user')->ds['u_key'].'">profile page</a><br/><br/>')
  ->add('string', 'pwd1', 'caption=Change password')
  ->add('file', 'pic', 'caption=Change picture')
  ->add('text', 'aboutme', array('caption' => 'About me', 'style' => 'height: 80px', 'default' => $profile['aboutme'], 'placeholder' => 'write something about yourself…'))
  ->add('submit', 'submit', 'caption=&gt; Save changes')
  ->onsubmit(function(&$data, $f) use($profile, $profileKey) {

    $data['pwd1'] = strtolower(trim($data['pwd1']));
    if($data['pwd1'] != '')
    {
      o('user')->ds['u_password'] = md5($data['pwd1']);
      $f->msg[] = 'Your password has been changed to "'.htmlspecialchars($data['pwd1']).'".';
    }
    
    if($_FILES['pic'] && is_uploaded_file($_FILES['pic']['tmp_name'])) 
    {
      
      $ftypes = array(
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif', 
        'image/png' => 'png',
        );
      
      $info = getimagesize($_FILES['pic']['tmp_name'], $info2);
      if($info[0] > 320 || $info[1] > 320 || !isset($ftypes[$info['mime']]))
        $f->errors['pic'] = 'user pictures must be JPEGs, GIFs, or PNGs smaller than 320x320 pixels';
      else
      {
        $path = 'static/avatars/'.o('user')->ds['u_community'].'/';
        if(!file_exists($path)) mkdir($path, 0777, true);
        $fn = o('user')->ds['u_key'].'.'.$ftypes[$info['mime']];
        move_uploaded_file($_FILES['pic']['tmp_name'], $path.$fn);
        o('user')->ds['u_pic'] = '/'.$path.$fn;
        o('user')->commit();
        $f->msg[] = 'Your user picture has been changed.';
      }
    }

    $data['aboutme'] = H2Syntax::saneText($data['aboutme']);
    if($data['aboutme'] != $profile['aboutme'])
    {
      $profile['aboutme'] = $data['aboutme'];
      nv_store($profileKey, $profile);
      $f->msg[] = 'Your info text has been changed.';
    }

  })
  ->display();

  if($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['cmd'] == 'deletepic')
  {
    o('user')->ds['u_pic'] = '';
    o('user')->commit();
    $settings->msg[] = 'Your picture has been deleted.';
  }
  if(o('user')->ds['u_pic'] != '')
  {
    ?><div style="margin-left: 200px;">
    <img src="<?= o('user')->ds['u_pic'] ?>"/><br/>
    <form action="/start/settings" method="post">
      <input type="submit" name="" value="Delete this picture"/>
      <input type="hidden" name="cmd" value="deletepic"/>
    </form>
    </div><?php
  }
  
  if(sizeof($settings->msg) > 0)
    foreach($settings->msg as $m)
      print('<div class="banner">'.$m.'</div>');

?>