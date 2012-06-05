<?php

class H2Syntax extends H2Class
{
  function esc($txt)
  {
    return(explode(' ', trim(htmlspecialchars($txt))));
  }

  function markLinks($txt)
  {
    // there comes a certain point in a man's life where he has to concede that his carefully
    // crafted regex is shit and that a combination of explode() and if() does a much cleaner
    // job of it. while this might turn out to be a gigantic fail, I'm just sick of preg_replace()
    $result = array();
    if(!is_array($txt)) $txt = explode(' ', $txt);
  
    foreach($txt as $w)
    {
      $w = trim($w);
      if($w != '')
      {
        $sp = strpos($w, '://');
        if($sp > 0 && $sp < 7 && !inStr($w, '(') && !inStr($w, '='))
        {
          $ws = $w;
          // if the URL is too long, we'll display it shortened
          if(strlen($ws) > 20) $ws = substr($ws, 0, 20).'…';
          $result[] = '<a href="'.$w.'" title="'.$w.'" target="_blank">'.$ws.'</a>';
        }
        else
          $result[] = $w;
      }
    }

    return(implode(' ', $result));  
  }

  function textToHtml($txt)
  {
    return(nl2br(H2Syntax::markLinks(H2Syntax::esc($txt))));
  }
  
  function abbreviate($txt, $toLength = 100)
  {
    $result = array();
    $slen = 0;
    $words = H2Syntax::esc($txt);
    foreach($words as $w)
    {
      $w = trim($w);
      if($w != '')
      {
        $slen += strlen($w);
        if($slen > $toLength) 
          return(H2Syntax::markLinks($result).' <span style="color: gray">[…]</a>');
        $result[] = $w;
      }
    }
    return(H2Syntax::markLinks($result));
  }

  // enforces length limits
  function saneText($raw)
  {
    return(substr(trim($raw), 0, 4096));
  }
  
  // makes an input string safe by only allowing a-z, 0-9, and underscore (might not work correctly) 
  function safeName($raw, $allow = array())
  {
    $s = '/[^a-z|0-9';
    foreach($allow as $a)
      $s .= '|\\'.$a;
    $s .= ']*/';
  	return(preg_replace($s, '', strtolower($raw)));
  }
  

}


?>