<?php

// DATA SAVE HANDLERS
global $firstField;
$firstField = null;

function tableform_init(&$form)
{
  $form->packCaption = '';
}

function tableform_save($p)
{
  return($_REQUEST[$p['name']]);
}

function tableform_multicheck_save($p)
{
  $values = array();
  foreach ($p['options'] as $k => $v)
  {
    $opthash = md5($p['name'].'-'.trim($k));
    if (isset($_REQUEST[$opthash])) $values[trim($k)] = true;
  }
  return(serialize($values));
}

function tableform_string_save($p)
{
  return(tableform_save($p));
}

function tableform_hidden_save($p)
{
  return(tableform_save($p));
}

function tableform_readonly_save($p)
{
  return(tableform_save($p));
}

function tableform_dropdown_save($p)
{
  return(tableform_save($p));
}

function tableform_radio_save($p)
{
  return(first($_REQUEST[$p['name'].':literal'], $_REQUEST[$p['name']]));
}

function tableform_checkbox_save($p)
{
  if($p['datatype'] == 'y/n')
    return(first($_REQUEST[$p['name']], 'N'));
  else
    return($_REQUEST[$p['name']] == 'Y');
}

function tableform_password_save($p)
{
  return(tableform_save($p));
}

function tableform_text_save($p)
{
  return(tableform_save($p));
}

// DISPLAY CODE
function tableform_documents($p, &$form)
{
  $id = $form->params[$form->defaultIdFieldname];
  $id = str_repeat('0', 4-strlen($id)).$id;
  $p['context'] = first($p['context'], $form->tableName.'/'.$id);
  $p['context'] = first($p['context'], 'default');
  if ($p['caption'] != '') print($p['caption']);
  @mkdir('resource/'.$form->tableName, 0777);
  ?> 
  <iframe src="lib/predef/stdform-documents-iframe.php?ctx=<?php echo urlencode($p['context']) ?>" 
    style="width: 100%; height: 220px; border: 0px;">
  </iframe>
  <?php
}

function tableform_handle_error(&$p, &$form)
{
	$error = $form->errors[$p['name']];
  if ($error != '') 
  { 
    $p['class'] .= ' fielderror'; 
    print(' <div class="errormsg"> ^ '.$error.'</div>'); 
  }
}

function tableform_readonly($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption"><div class="element-caption element-readonly"><?php echo $p['caption'] ?></div></td><td>
    <input readonly class="<?php echo $p['class'] ?> element-readonly" type="text" name="<?php echo $p['name'] ?>" value="<?php echo htmlspecialchars(first($p['value'])) ?>"/>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_string($p, &$form)
{
	global $firstField;
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <input class="<?php echo $p['class'] ?>" type="text" name="<?php echo $p['name'] ?>" id="fld_<?php echo $p['name'] ?>" 
      onchange="<?php echo $p['onchange'] ?>" placeholder="<?php echo htmlspecialchars($p['placeholder']) ?>"
      value="<?php echo htmlspecialchars(first($p['value'])) ?>"/>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); 
		if ($firstField == null && $form->formOptions['auto-focus'])
    {
      $firstField = $p;
      ?>
      <script>
      document.getElementById('fld_<?php echo $p['name'] ?>').focus();
      </script>
      <?php
    } ?>
  </td></tr><?php
}

function tableform_file($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption"><div class="element-caption"><?php echo $p['caption'] ?></div></td><td>
    <input class="<?php echo $p['class'] ?>" type="file" name="<?php echo $p['name'] ?>" id="<?php echo $p['name'] ?>" 
      onchange="<?php echo $p['onchange'] ?>"/>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_checkbox($p, &$form)
{
  if ($p['value'] === true || $p['value'] == 'Y') $checked = 'checked';
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption2'] ?></div></td><td>
    <input class="<?php echo $p['class'] ?>" <?php echo $checked ?> type="checkbox" onchange="<?php echo $p['onchange'] ?>" name="<?php echo $p['name'] ?>" id="<?php echo $p['name'] ?>" value="Y"/>
    <label for="<?php echo $p['name'] ?>"><?php echo $p['caption'] ?></label>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_dropdown($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <select class="<?php echo $p['class'] ?>" style="<?php echo $p['style'] ?>" onchange="<?php echo $p['onchange'] ?>" name="<?php echo $p['name'] ?>">
    <?php
    foreach ($p['options'] as $k => $v)
    {
      $selected = '';
      if (!$p['optionkeys']) $k = $v;
      if ($k == $p['value']) $selected = 'selected';
      print('<option '.$selected.' value="'.htmlspecialchars($k).'">'.htmlspecialchars(first($v, $k)).'</option>');
    }
    ?>  
    </select>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_radio($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <table>
    <?php
    foreach ($p['options'] as $k => $v)
    {
      $selected = '';
      if (trim($k) == trim($p['value'])) 
      {
        $selected = 'checked';
        $wasChecked = true;
      }
      print('<tr><td width="24" valign="top">
        <input id="'.$p['name'].'-'.$k.'" onchange="'.$p['onchange'].'" name="'.$p['name'].'" type="radio" '.$selected.' value="'.htmlspecialchars(first($k, $v)).'"/></td><td>
        <label for="'.$p['name'].'-'.$k.'">'.htmlspecialchars($v).'</label></td></tr>');
      $lastCapt = trim($v);
    }
    
    if (substr($lastCapt, -1) == ':')
    {
      if (!$wasChecked)
        $vle = $p['value'];
       print('<tr><td width="24" valign="top">
        &gt;</td><td>
        <input type="text" name="'.$p['name'].':literal" value="'.htmlspecialchars($vle).'"/></td></tr>');
    }
    ?>  
    </table><div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_multicheck($p, &$form)
{
  $values = unserialize($p['value']);
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <table>
    <?php
    foreach ($p['options'] as $k => $v)
    {
      $selected = '';
      $opthash = md5($p['name'].'-'.trim($k));
      if ($values[trim($k)]) $selected = 'checked';
      print('<tr><td width="24" valign="top">
        <input id="'.$opthash.'" name="'.$opthash.'" onchange="'.$p['onchange'].'"
        type="checkbox" '.$selected.' value="'.htmlspecialchars(first($k, $v)).'"/></td><td>
        <label for="'.$opthash.'">'.htmlspecialchars($v).'</label></td></tr>');
    }
    ?>  
    </table>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div>
    <?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_hidden($p, &$form)
{
  ?> 
  <input type="hidden" name="<?php echo $p['name'] ?>" value="<?php echo htmlspecialchars(first($p['value'])) ?>"/><?php
}

function tableform_html($p, &$form)
{
  ?><tr><td colspan="2"><?php echo $p['name'] ?></td></tr><?php
}

function tableform_section($p, &$form)
{
  if ($form->jquerySections)
  {
    ?></table></div>
    <h3 href="#"><?php echo $p['caption'] ?></h3>
    <div><table width="100%" class="cqform-table"><?php
  }
  else
  {
    ?><tr><td colspan="2"><h3><?php echo $p['caption'] ?></h3></td></tr><?php
  }
}

function tableform_sectionend($p, &$form)
{
  if ($form->jquerySections)
  {
    $form->sectionTerminated = true;
    ?></table></div>
    </div><table class="cqform-table" width="100%"><?php
  }
  else
  {
    
  }
}

function tableform_text($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <textarea class="<?php echo $p['class'] ?>" style="<?php echo $p['style'] ?>" onchange="<?php echo $p['onchange'] ?>" id="fld_<?php echo $p['name'] ?>"
    placeholder="<?= $p['placeholder'] ?>" name="<?php echo $p['name'] ?>"><?php echo htmlspecialchars(first($p['value'])) ?></textarea>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div><?php
    tableform_handle_error($p, $form);
    if ($firstField == null && $p['focus'] === true)
    {
      $firstField = $p;
      ?>
      <script>
      document.getElementById('fld_<?php echo $p['name'] ?>').focus();
      </script>
      <?php
    }
  ?></td></tr><?php
}

function tableform_password($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <input class="<?php echo $p['class'] ?>" type="password" <?php echo $p['attr'] ?> placeholder="<?php echo htmlspecialchars($p['placeholder']) ?>"
      id="fld_<?php echo $p['name'] ?>" name="<?php echo $p['name'] ?>" value="<?php echo htmlspecialchars(first($p['value'])) ?>"/>
    <div class="infomarker"><?php echo $p['infomarker'] ?></div><?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_passworddouble($p, &$form)
{
  ?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>">
  <div class="element-caption" style="<?= $form->formOptions['style-td-caption'] ?>"><?php echo $p['caption'] ?></div></td><td>
    <input class="<?php echo $p['class'] ?>" autocomplete="off" type="password" <?php echo $p['attr'] ?> onkeyup="$('#fld_<?php echo $p['name'] ?>_msg').text(checkPwdEquivalency('fld_<?php echo $p['name'] ?>'));"
      id="fld_<?php echo $p['name'] ?>" name="<?php echo $p['name'] ?>" value="<?php echo htmlspecialchars(first($p['value'])) ?>"/><br/>
    <input class="<?php echo $p['class'] ?>" autocomplete="off" type="password" <?php echo $p['attr'] ?> onkeyup="$('#fld_<?php echo $p['name'] ?>_msg').text(checkPwdEquivalency('fld_<?php echo $p['name'] ?>'));"
      id="fld_<?php echo $p['name'] ?>_2" name="<?php echo $p['name'] ?>_2" value="<?php echo htmlspecialchars(first($p['value'])) ?>"/> 
    <span id="fld_<?php echo $p['name'] ?>_msg">(repeat)</span><br/>
			
    <div class="infomarker"><?php echo $p['infomarker'] ?></div><?php tableform_handle_error($p, $form); ?>
  </td></tr><?php
}

function tableform_start($p, &$form)
{
  ?><form action="/<?= actionUrl($_REQUEST['action'], $_REQUEST['controller']) ?>" id="<?php echo $form->name ?>" enctype="multipart/form-data" method="post"><?php
  if ($form->jquerySections)
  {
    ?><div id="accordion"><h3 href="#"><?php echo $p['caption'] ?></h3>
<div><?php
  }
  ?><table class="cqform-table" width="100%"><?php
}

function tableform_submit($p, &$form)
{
	?><tr><td valign="top" class="form-td-caption" style="<?= $form->formOptions['style-td-caption'] ?>">&nbsp;</td><td>
	<?php 
	if(!isset($form->formOptions['ajax']))
	{
    ?><input type="submit" name="submitbtn" value="<?php echo $p['pure-caption'] ?>"/><?php 
	}
	else
	{
    $fvals = array();
		foreach($form->elements as $el) if($el['name'] != '') $fvals[] = "'".$el['name']."' : ".'$(\'#fld_'.$el['name'].'\').val()';
    ?><input type="button" name="submitbtn" value="<?php echo $p['pure-caption'] ?>"
      onclick="<?= $form->formOptions['ajax'] ?>({ <?= implode(', ', $fvals) ?> });"/><?php 
	}
  ?></td></tr><?php  
}

function tableform_button($p, &$form)
{
	?><tr><td valign="top" class="form-td-caption">&nbsp;</td><td>
	<?php 
  $fvals = array();
  ?><input type="button" name="btn<?= $el['name'] ?>" value="<?php echo $p['caption'] ?>"
    onclick="<?= $p['onclick'] ?>"/><?php 
  ?></td></tr><?php  
}

function tableform_end($p, &$form)
{
  if(is_array($form->params)) foreach ($form->params as $k => $v)
    print('<input type="hidden" name="'.$k.'" id="param_'.$k.'" value="'.htmlspecialchars($v).'"/>');
  ?></table><?php
    if ($form->jquerySections && !$form->sectionTerminated)
  {
    ?></div></div><?php
  }
  ?></form><?php if ($form->jquerySections) { ?><script>
      $(function() {
        $('#accordion').accordion({ autoHeight: false });
        });
    </script><?php
    }
}

?><script>//<![CDATA[ 

function checkPwdEquivalency(idf)
{
  var p1 = $('#'+idf).val();
	var p2 = $('#'+idf+'_2').val();
  if(p1.length < 4) 
    return('password too short');
  else
  {
	  if(p1 != p2)
      return('passwords do not match');
	  else
      return(':-)');
  }
}

//]]></script>