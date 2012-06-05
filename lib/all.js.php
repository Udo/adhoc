<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: combines JavaScript files for output to optimize browser loading
 */

chdir('../');

ob_start("ob_gzhandler");

header('content-type: text/javascript; charset=UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+60*60*4) . " GMT");

// fixme: this causes the cached content to be compressed when stored, then uncompressed and re-compressed when accessed
// ...which is stupid.

$cfgCategory = 'config';
@include('conf/default.php');
require('lib/genlib.php');

cache_delete('all.js');
cache_region('all.js', function() {
  foreach(array(
    'ext/jq/jquery.min.js',
    'ext/jq/masonry.min.js',
    'ext/jq/infinitescroll.js',
    'lib/adhoc.js') as $inc)
  {
    ob_start();
    include($inc);
    $js = ob_get_clean();
    $info = '/* '.$inc.' ('.number_format(strlen($js)/1024, 2).' kB) */'.chr(10);
    $debugInfo[] = $info;
    print($info.chr(10).$js.chr(10)); 
  }
  print(implode('', $debugInfo));
}, true);

?>  

