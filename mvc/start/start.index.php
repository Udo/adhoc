<img src="/themes/default/adhocistan2.png" align="left" style="margin-right: 16px;"/>

<h1>
simple ad-hoc social networks.
</h1>
<?

#include('mvc/start/login.php');

WriteToFile('log/visitors.log', $_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_REFERER'].chr(13).chr(10));

?>
<h2>
Create Your Own Community
</h2>
<div class="description">
  First your new community needs a short name for its address (you can give it a longer,
  better readable title later):
</div>
<?

$create = o(new CQForm('create'))
  ->add('string', 'community', 'caption=New community name,validate=notempty')
  ->add('html', '<div class="description">You will be the administrator. Please choose a username and a password for yourself:</div>')
  ->add('string', 'username', 'caption=Your new username,validate=notempty')
  ->add('password', 'password', 'caption=Your new password,validate=notempty')
  ->add('html', '<div class="description">
    You can restrict who can join your community by protecting it with a password.
    If you want anyone who knows the address to be able to join your community, leave this field empty:</div>')
  ->add('string', 'cpwd', 'caption=Community password')
  /*->add('checkbox', 'review', array('caption' => 'I want to review new users before they can join', 'caption2' => 'Review', 
    'default' => o('controller')->community->ds['c_review'] == 'Y'))*/
  ->add('submit', 'login', 'caption=Create &gt;')
  ->receive(function(&$ds, $f) {
    $ds['community'] = strtolower(H2Syntax::safename($ds['community']));
    if(strlen($ds['community']) < 1)
      $f->errors['community'] = 'community names can only consist of numbers and letters';
    else
    {
      $co = new H2Community($ds['community']);
      if(sizeof($co->ds) > 0)
        $f->errors['community'] = 'sorry, a community with that name already exists';
    }
  })
  ->onsubmit(function($ds) { 
    
    $co = new H2Community($ds['community']);
    $co->initNew($ds);
    
    o('controller')->redirect('created');
    
  })
  ->display();

?>
<h2>How It Works</h2>
<table width="100%"><tr>
 
  <td width="532" valign="top"><img style="margin-left: -20px;" src="/img/screenshot01.png"/></td>
  <td width="50%" valign="top">
    <br/>
    <img src="/img/citizen.png" align="right" style="margin-left: 8px"/>
    Adhocistan is a way for people to create small
    social communities on the net spontaneously
    and anonymously.<br/>
    <br/>
    No formal sign-up is required. You just choose your
    desired username and password - then you're ready to
    go. No email addresses, no link to any of your other
    social networks.<br/>
    <br/>
    Optionally, you can protect your community from
    outsiders by requiring people to enter the community
    password before they can create an account.
  
  </td>

</tr></table>