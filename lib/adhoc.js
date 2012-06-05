function layoutReflow()
{
  $('.masonry_container').masonry('reload'); 
}

notificationsVisible = false;
overlayGracePeriod = false;

function hideOverlay()
{
  if(notificationsVisible && !overlayGracePeriod)
  {
    $('#m1').fadeOut();
    setTimeout(function() { $('#m1').css('display', 'none'); notificationsVisible = false; }, 500);
    notificationsVisible = false;
  }
}

function showNotifications()
{
  if(notificationsVisible)
  {
    overlayGracePeriod = false;
    hideOverlay();
  }
  else
  {
    overlayGracePeriod = true;
    notificationsVisible = true;
    $('#m1')
      .css('display', 'block')
      .css('opacity', 1)
      .html('<img src="/themes/default/ajax-loader.gif" align="absmiddle"/> loading…');
    $.post('/notifications/ajax_menu', function(data) {
    
      $('#m1').html(data);
      overlayGracePeriod = false;
    
    });
  }
}

// this fixes a JavaScript stupidity where all outstanding interval calls are executed
// together once an inactive tab comes back into focus (a huge ECMA design fuckup)
var updateChecker;

function setFocus() {
  updateChecker = window.setInterval("checkForUpdates();", 10000);
}

function removeFocus() {
  window.clearInterval(updateChecker);
}

window.addEventListener('focus', setFocus);    
window.addEventListener('blur', removeFocus);
window.addEventListener('click', hideOverlay);
setFocus();

function checkForUpdates()
{
  if(document.updateCheckEnabled) {
    $.post('/notifications/ajax_check', function(htmlData) {
      $('#notif1').replaceWith(htmlData); 
    });
  }
}

function doPost()
{
  $('#loader').css('display', 'block');
  $.post('/post/ajax_do', { 'text' : $('#post').val() }, function(data) {
    $('#loader').css('display', 'none');
    if(data.html)
    {
      $('#stream').prepend(data.html);
      $('#post').val('');
      $('#preview-container').empty();
      layoutReflow();
    }
    }, 'json');
}

function doSwitch(ctr, switchCommand, data)
{
  if(!data) data = {};
  $.post('/'+ctr+'/ajax_'+switchCommand, data, function(result) {
    window.location.href = window.location.href;
  }); 
}

function blockUser(id, tgt)
{
  if(tgt) $(tgt).replaceWith('<a class="red" onclick="unblockUser('+id+', event.target);">unblock</a>');
  $.post('/social/ajax_blockuser', { 'id' : id } );
}

function unblockUser(id, tgt)
{
  if(tgt) $(tgt).replaceWith('<a onclick="blockUser('+id+', event.target);">block</a>');
  $.post('/social/ajax_unblockuser', { 'id' : id } );
}

function infiniLoad(selTagKey)
{
  if(document.infiniLoaded == 'complete') return;
  document.infiniOffset++;
  document.scrollLoading = true;
  if(!selTagKey) selTagKey = 0; 
  $.post('/post/ajax_load', { 'o' : document.infiniOffset, 'tag' : selTagKey }, function(data) {
    document.scrollLoading = false;
    var postItems = $(data.html);
    $('.masonry_container').append(postItems).masonry( 'appended', postItems, 'isAnimatedFromBottom' );  
    if(data.finished) document.infiniLoaded = 'complete';
  }, 'json');
}
  

function doLike(pid)
{
  var liketype = $('#like'+pid).text();
  var newtxt = '';
  
  $.post('/post/ajax_like', { 'pid' : pid, 'liketype' : liketype }, function(data) {} );
  
  if(liketype == 'like') newtxt = 'unlike'; else newtxt = 'like';
  $('#like'+pid).text(newtxt);
}

function doComment(pid)
{
  $('#cloadr'+pid).css('display', 'block');
  $.post('/post/ajax_comment', { 'parent' : pid, 'text' : $('#comta'+pid).val() }, function(data) {
    $('#loader').css('display', 'none');
    if(data.html)
    {
      $('#commenteditor'+pid).html($('#commentshim'+pid).html());
      $('#comments'+pid).append(data.html);
    }
  }, 'json');
}

function deleteComment(cid)
{
  $.post('/post/ajax_delete', { 'pid' : cid }, function(data) {} );
  $('#cid'+cid).fadeOut();
  setTimeout(function() {
    document['del'+cid] = $('#cid'+cid).html(); 
    $('#cid'+cid).html(
      '<div class="smallmsg indent">Comment deleted.<br/><a onclick="undoCommentDelete(\''+cid+'\');">Click here to undo this</a>.</div>'
      ).fadeIn(); 
    layoutReflow(); }, 300);
}

function openComment(pid)
{
  $('#commenteditor'+pid).html('<textarea id="comta'+pid+'" class="commenteditor" onkeyup="if(event.keyCode == 13) doComment(\''+pid+'\');"></textarea>'+
    '<div id="cloadr'+pid+'" style="display:none;"><img src="/themes/default/ajax-loader.gif" align="absmiddle"/> posting…</div>');
  document.getElementById('comta'+pid).focus();
  layoutReflow();
}

function deletePost(pid)
{
  $.post('/post/ajax_delete', { 'pid' : pid }, function(data) {} );
  $('#post'+pid).fadeOut();
  setTimeout(function() {
    document['del'+pid] = $('#post'+pid).html(); 
    $('#post'+pid).html(
      'Post deleted.<br/><a onclick="undoPostDelete(\''+pid+'\');">Click here to undo this</a>.'
      ).fadeIn(); 
    layoutReflow(); }, 500);
}

function undoPostDelete(pid)
{
  $.post('/post/ajax_undelete', { 'pid' : pid }, function(data) {
  } );
  $('#post'+pid).html(document['del'+pid]);
  layoutReflow(); 
  document['del'+pid] = null;
}

function undoCommentDelete(pid)
{
  $.post('/post/ajax_undelete', { 'pid' : pid }, function(data) {} );
  $('#cid'+pid).html(document['del'+pid]);
  layoutReflow(); 
  document['del'+pid] = null;
}