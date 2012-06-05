<form id="fileupload" action="/post/upload" method="POST" enctype="multipart/form-data">
<?php
  include_once('templates/post.php');
  include('templates/post.uploader.php');
?>
<table width="100%" cellspacing="0" cellpadding="6">

  <tr>

    <!--<td valign="top" width="32">
      <?= getUserImage(getUserRecord(o('user')->ds)) ?>
    </td>-->
    
    <td valign="top" width="40%">
      <textarea placeholder="enter a message here" id="post" style="height: 74px;"></textarea> <br/>
      <input style="float:left;width:100px;margin-right:8px;" type="button" value="Post" onclick="doPost();"/>
      <span style="float:left;width:80px;" class="btn btn-success fileinput-button">
          <i class="icon-plus icon-white"></i>
          <span>Add files...</span>
          <input type="file" name="files[]" multiple>
      </span>
      <div id="loader" style="display:none;">
        <img src="/themes/default/ajax-loader.gif" align="absmiddle"/>
        posting...
      </div>
    </td>  
    
    <td width="10"></td>
    
    <td valign="top" width="*">
      <div id="preview-container" class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></div>
    </td>
  
  </tr>

</table>

      
  </form>
