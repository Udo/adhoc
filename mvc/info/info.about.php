<div>
  <h2>About Adhocistan</h2>
  
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
  
</div>
<br/>
<div>

  <h2>Credits</h2>
  <div>
  <?php
  
  $credits = array();
  $credits[] = '<a href="http://php.net/">PHP</a>';
  $credits[] = '<a href="http://www.mysql.com/">MySQL</a>';
  $credits[] = '<a href="http://memcached.org/">Memcache</a>';
  $credits[] = '<a href="http://jquery.com/">jQuery</a>';
  $credits[] = '<a href="http://masonry.desandro.com/">Masonry</a>';
  $credits[] = '<a href="https://github.com/blueimp/jQuery-File-Upload">blueimp/fileupload</a>';
  $credits[] = '<a href="http://thenounproject.com/">The Noun Project</a>';
  $credits[] = '<a href="http://hubbub-project.org/">Hubbub Project</a>';
  $credits[] = '<a href="http://placekitten.com/">Place Kitten</a>';
  $credits[] = '<a href="https://gravatar.com/">Gravatar</a>';
  
  ?>
  This project was made possible by: <?= implode(' &middot; ', $credits) ?>
  
  </div>
  

</div>
<br/>
<div>
    <h2>Feedback</h2>

    This is an experiment and feedback is always appreciated,
    send it to: <a href="mailto:udo.schroeter@gmail.com">udo.schroeter@gmail.com</a>.

</div>