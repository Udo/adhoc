<?php

class PostModel extends H2Model
{

  function __construct()
  {
    $this->communityId = o('community')->id;
  }

  function userMayChangePost($post)
  {
    return(sizeof($post->ds) > 0 && 
	    ($post->ds['p_owner'] == o('user')->id || o('user')->ds['u_role'] == 'A'));  
  }

  function deletePost($pid)
  {
	  $post = new H2Post($this->communityId, $pid);
	  if($this->userMayChangePost($post))
	  {
	    $post->remove();
	  }
  }

  function undeletePost($pid)
  {
	  $post = new H2Post($this->communityId, $pid);
	  if($this->userMayChangePost($post))
	  {
	    $post->undoRemove();
	  }
  }

  function likePost($pid, $liketype = 'like')
  {
	  $post = new H2Post($this->communityId, $pid);
	  $post->like($liketype);
  }
  
  function createComment($pid, $text)
  {
	  $parent = new H2Post($this->communityId, $pid);
	  $text = substr(trim($text), 0, MAX_POSTTEXTSIZE);
 	  if(sizeof($parent->ds) > 0)
	  {
	    $comment = o(new H2Post($this->communityId))
         ->initNew(o('user')->id, array(
           'text' => $text,
           'parent' => $parent->id,
           ));
      $parent->registerComment($comment); 
      $this->comment = $comment;
      return(true);
	  }
	  else
	    return(false);
  }

  function getQueuedAttachments()
  {
    $this->attachmentPath = 'static/temp/'.$_SESSION['uid'].'/';
    $this->destinationPath = 'static/usr/'.$_SESSION['uid'].'/';
    if(!file_exists($this->destinationPath)) mkdir($this->destinationPath, 0777, true);
    return(getFiles($this->attachmentPath));
  }
  
  function createPost($text)
  {
    $this->posts = array();
    
    $attachments = $this->getQueuedAttachments();
    $text = substr(trim($text), 0, MAX_POSTTEXTSIZE);

    o('user')->ds['u_postcount']++;
    o('user')->commit();

    if($text != '' || sizeof($attachments) > 0)
    {
      foreach($attachments as $attachment)
      {
        $info = array();
        $size = getimagesize($this->attachmentPath.$attachment, $info);

        if($size['mime'] == 'image/jpeg' || $size['mime'] == 'image/gif' || $size['mime'] == 'image/png')
        {
          $post = o(new H2Post($this->communityId))
            ->initNew(o('user')->id, array(
              'text' => $text,
              ));
          $this->posts[] = $post;
  
          $pi = pathinfo($attachment);
          $newFilename = $_SESSION['uid'].'.'.h2_make_uid(16, true).'.'.$pi['extension'];
          rename($this->attachmentPath.$attachment, $this->destinationPath.$newFilename);
          
          $post->data['attachments'][] = array(
            'url' => $this->destinationPath.$newFilename,
            'size' => $size,
            'info' => $info);
  
          $post->commit();
        }

        if(file_exists($this->attachmentPath.$attachment)) unlink($this->attachmentPath.$attachment);
        if(file_exists($this->attachmentPath.'tn/'.$attachment)) unlink($this->attachmentPath.'tn/'.$attachment);
      }
      
      if(sizeof($attachments) == 0) 
      {
        $this->posts[] = o(new H2Post($this->communityId))
          ->initNew(o('user')->id, array(
            'text' => $text,
            ));
      }
    }
    return(true);
  }

}

?>