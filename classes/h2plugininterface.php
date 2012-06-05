<?php

class H2PluginInterface extends H2Class
{

  function __construct()
  {
    $this->data = array();
    $this->loadFromStorage();
  }

  function loadFromStorage()
  {
    $this->data = array();
    foreach($this->getPluginList() as $pluginName)
    {

    }
  }

  function getPluginList()
  {
    return(getFiles('plugins/', true));
  }
  
}

?>