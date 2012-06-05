<h2>Submit a Bug Report</h2>
<div style="margin-top: 16px; margin-bottom: 16px;">
 
  Found something that's not working? Let us know!

</div>
<div>
<?php

  include_once('lib/cq-forms.php');

  $f = o(new CQForm('bug'))
    ->add('string', 'name', 'caption=My name,validate=notempty')
    ->add('string', 'email', 'caption=My email address')
    ->add('text', 'name', array('caption' => 'Description', 'style' => 'height: 100px', 'validate' => 'notempty'))
    ->add('submit', 'submit', 'caption=Submit Report')
    ->onsubmit(function(&$ds, $f) {
    
      ob_start();
      print_r($ds);
      print_r($_SERVER);
      $body = ob_get_clean();
    
      mail(
        'udo.schroeter@gmail.com',
        'Adhocistan Bug Report '.gethostbyaddr($_SERVER['REMOTE_ADDR']),
        $body
        );

      ?><div class="banner">Thanks for submitting this report!</div><?php
      $f->hidden = true;
    
    })
    ->display();

?>
</div>