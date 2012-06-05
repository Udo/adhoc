<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: provides the CQForm object that abstracts HTML forms
 */

function saveValueToArray(&$array, $key, $value)
{
  $arq = array();
  foreach(explode('/', $key) as $ks)
    $arq[] = "['".H2Syntax::safeName($ks)."']";
  eval('$array'.implode('', $arq).' = $value;');
}

function getValueFromArray($array, $key)
{
  foreach(explode('/', $key) as $ks)
    $array = $array[$ks];
  return($array);
}

class CQForm
{
  function CQForm($name = 'unnamed', $fopt = array())
  {
    global $config;
    $this->name = $name;
    $this->elements = array();
    $this->presentationDir = $GLOBALS['app.basepath'].'lib/predef/';
    $this->presentationName = first($config['site.formlayout'], 'tableform');
    $this->add('start', $name, $name, array());
    $this->params = array();
    $this->submitted = ($_REQUEST['formsubmit'] == $name);
    $this->packStart = '<div>';
    $this->packEnd = '</div>';
    $this->packCaption = '<br/>';
    $this->defaultIdFieldname = 'id';
    $this->mandatoryMarker = ' <span class="form-mandatory">*</span>';
    $this->infoMarker = ' <a title="$" class="form-info">i</a>';
    if (!is_array($fopt))
      $fopt = stringParamsToArray($fopt);
    $this->formOptions = $fopt;
  }

  function updateDataset()
  {
    if ($this->tableName != '')
    {
      $this->getData();
      DB_UpdateDataset($this->tableName, $this->ds);
    }
    else
    {
      logError('form', 'CQForm::updateDataset() unspecified database table');
    }
  }
  
  function receive($handler)
  {
    // registers a data handler 
    $this->handlers['receive'] = $handler;
    return($this);
  }
  
  function onsubmit($func)
  {
    $this->getData();
    if($this->submitted && sizeof($this->errors) == 0)
      $func($this->ds, $this);
    return($this);
  }
  
  function getData()
  {
    global $config;
    if(!$this->submitted) return;
    $this->ds = array();
    $this->errors = array();
    include_once($this->presentationDir.$this->presentationName.'.php');
    foreach ($this->elements as $e)
    {
      $dFunction = $this->presentationName.'_'.$e['type'].'_save';
      if (is_callable($dFunction))
      {
        $value = $dFunction($e);
				switch($e['filter'])
				{
					case('safe'): {
						$value = H2Syntax::safeName($value);
						break;
					}
				}
				saveValueToArray($this->ds, $e['name'], $value);
        #$this->ds[$e['name']] = $value;
        switch ($e['validate'])
        {
          case('notempty'): {
            if (trim($value) == '') 
						{
              $this->errors[$e['name']] = l10n('field.cannotbeempty'); 
						}
            break;
          }
          case('email'): {
            require_once('lib/is_email.php');
            if(is_email($value, true, E_WARNING) != ISEMAIL_VALID)
              $this->errors[$e['name']] = l10n('field.invalidemail'); 
            break;
          }
        }
        if(!isset($this->errors[$e['name']]) && isset($e['onvalidate']))
        {
          $valid = $e['onvalidate']($value, $e, $this);
          if(!($valid === true || $valid == '')) $this->errors[$e['name']] = $valid;
        }
      }
    }      
    if(isset($this->handlers['receive']) && sizeof($this->errors) == 0)
    {
      $this->handlers['receive']($this->ds, $this);
    }
    return($this->ds);
  }
  
  function ds($ds = array())
  {
    $this->ds = $ds;
    $this->getDataOnDisplay = true;
    return($this); 
  }
  
  function add($type, $name = null, $properties = array())
  {
    if($type == 'param')
    {
      $this->params[$name] = $properties;
      return($this); 
    }

    if (!is_array($properties))
      $properties = stringParamsToArray($properties);
    if($properties['caption'] == '') $properties['caption'] = l10n($name, true);
    if($properties['caption'] == '') $properties['caption'] = '['.trim($name).']';
        
    $properties['name'] = $name;
    $properties['type'] = first($type, 'string');
    $elname = md5($name); $ectr = 1;
    if($this->formOptions['placeholders'] == 'auto')
      $properties['placeholder'] = l10n($name.'.placeholder');
    if (isset($this->elements[$elname]))
    {
      while (isset($this->elements[$elname.$ectr]))
        $ectr++;
      $elname = $elname.$ectr;
    }
    if (isset($properties['textoptions']))
      foreach(explode(first($properties['textoptions.separator'], ';'), $properties['textoptions']) as $opt) $properties['options'][trim($opt)] = $opt;
    $this->elements[$elname] = $properties;
    return($this);
  }

  function display($opt = array())
  {
    if ($this->getDataOnDisplay) $this->getData();
    if ($this->hidden) return;
    if ($this->formClosed != true)
    {
      $this->params['formsubmit'] = $this->name;
      $this->add('end', $name, $name, array('params' => $this->params));
      $this->formClosed = true;
    }
    include_once($this->presentationDir.$this->presentationName.'.php');
    $templateInitFunction = $this->presentationName.'_init';
    if (is_callable($templateInitFunction)) $templateInitFunction($this);
    foreach ($this->elements as $e)
    {
      $sessionFieldName = $this->name.'-'.$e['name'];
      $e['pure-caption'] = $e['caption'];
      $e['caption'] .= $this->packCaption;
      if ($e['validate'] == 'notempty' && isset($this->mandatoryMarker))
        $e['caption'] = $e['caption'].$this->mandatoryMarker;
      if (trim($e['info']) != '')
        $e['infomarker'] = str_replace('$', $e['info'], $this->infoMarker);
      print($this->packStart);
      print(first($opt['field-start']));
      $dFunction = $this->presentationName.'_'.$e['type'];
      if ($e['sessiondefault'] == true)
        $e['default'] = first($_SESSION[$sessionFieldName], $e['default']);
      $e['value'] = first(getValueFromArray($this->ds, $e['name']), $e['default']);
      if ($e['sessiondefault'] == true)
        $_SESSION[$sessionFieldName] = $e['value'];
      $e['error'] = $this->errors[$e['name']];
      if (is_callable($dFunction))
        $dFunction($e, $this);
      else
        logError('form', 'CQForm: unknown form element type "'.$e['type'].'"');
      print(first($opt['field-end']));
      print($this->packEnd);
    }
    return($this);
  }
}

?>
