<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: functional MySQL database wrapper (provides functions that have the prefix DB_*)
 */

$DBERR = '';

function getTableName($table)
{
  checkTableName($table);
  return(DB_Safe($table));
}

function checkTableName(&$table)
{
	$prefix = cfg('db/prefix');
  $l = strlen($prefix);
  if (substr($table, 0, $l) != $prefix)
    $table = $prefix.$table;
  return($table);
}

function DB_Safe($raw)
{
  if($GLOBALS['db_link'] == null)
    return(addslashes($raw));
  else
    return(mysql_real_escape_string($raw, $GLOBALS['db_link']));
}

function DB_StripPrefix($tableName)
{
  $preFix = substr($tableName, 0, strlen(cfg('db/prefix')));
  if ( $preFix == cfg('db/prefix') ) 
    $tableName = substr($tableName, strlen(cfg('db/prefix')));
  return($tableName); 
}

// create a comma-separated list of keys in $ds
function MakeNamesList(&$ds)
{
  $result = '';
  if (sizeof($ds) > 0)
    foreach ($ds as $k => $v)
    {
      if ($k!='')
        $result = $result.','.$k;
    }
  return substr($result, 1);
}

// make a name-value list for UPDATE-queries
function MakeValuesList(&$ds)
{
  $result = '';
  if (sizeof($ds) > 0)
    foreach ($ds as $k => $v)
    {
      if ($k!='')
        $result = $result.',"'.DB_Safe($v).'"';
    }
  return substr($result,1);
}

function DB_UpdateField($tableName, $rowId, $fieldName, $value)
{
  DB_Connect();
	if(is_array($value)) $value = $value[$fieldName];
	$keys = DB_GetKeys($tableName);
	DB_Update('UPDATE '.getTableName($tableName).' SET `'.$fieldName.'` = "'.DB_Safe($value).'" WHERE `'.$keys[0].'` = '.($rowId+0));
}

function DB_GetFields($tablename)
{
  DB_Connect();
  $fields = array();
  foreach(DB_GetList('SHOW COLUMNS FROM '.getTableName($tablename)) as $field)
    $fields[$field['Field']] = $field;
  profile_point('DB_GetFields('.$tablename.')');
  return($fields);
}

// gets a list of keys for the table
function DB_GetKeys($tablename)
{
  $oTableName = DB_StripPrefix($tablename);
  $tablename = getTableName($oTableName);
  if(isset($GLOBALS['config']['dbinfo'][$oTableName]['keys']))
    return($GLOBALS['config']['dbinfo'][$oTableName]['keys']);

  $result = cache_data('db/keys/'.$tablename, function() use ($tablename) {
      DB_Connect();
      $pk = Array();
      $sql = 'SHOW KEYS FROM `'.$tablename.'`';
      $res = mysql_query($sql, $GLOBALS['db_link']) or $DBERR = (mysql_error().'{ '.$sql.' }');
      if (trim($DBERR)!='') logError('error_sql: '.$DBERR);
        
      while ($row = @mysql_fetch_assoc($res))
      {
        if ($row['Key_name']=='PRIMARY')
          array_push($pk, $row['Column_name']);
      }
      profile_point('DB_GetKeys('.$tablename.') REBUILD KEY CACHE');
      return($pk);
    });

  return $result;
}

// updates/creates the $dataset in the $tablename
function DB_UpdateDataset($tablename, &$dataset, $options = array())
{
  DB_Connect();
  checkTableName($tablename);
  $keynames = DB_GetKeys($tablename);
  $keyname = $keynames[0]; 
  
  unset($GLOBALS['dbdatatmp'][$tablename][$keyname]);
		 
  $query='REPLACE INTO '.$tablename.' ('.MakeNamesList($dataset).
      ') VALUES('.MakeValuesList($dataset).');';
  
  mysql_query($query, $GLOBALS['db_link']) or $DBERR = (mysql_error().'{ '.$query.' }');
  if (trim($DBERR)!='') logError($DBERR);
  $dataset[$keyname] = first($dataset[$keyname], mysql_insert_id($GLOBALS['db_link']));
  
  profile_point('DB_UpdateDataset('.$tablename.', '.DB_UpdateDataset.')');
  return mysql_insert_id($GLOBALS['db_link']);
}

function DB_UpdateDatasetDelayed($opname, $tablename, &$dataset, $options = array())
{
  $GLOBALS['db']['updatequeue'][$opname] = array($tablename, $dataset, $options);
}

function DB_DoPendingUpdates()
{
  if(is_array($GLOBALS['db']['updatequeue'])) foreach($GLOBALS['db']['updatequeue'] as $q)
  {
    DB_UpdateDataset($q[0], $q[1], $q[1]);
  }
}

// get all the tables in the current database
function DB_GetTables()
{
  DB_Connect();
  $result = mysql_list_tables(cfg('db/database'), $GLOBALS['db_link']);
  $tableList = array();
  while ($row = mysql_fetch_row($result))
      $tableList[$row[0]] = $row[0];
  sort($tableList);
  return($tableList);
}

function DB_GetDatasetMatch($table, $matchOptions, $fillIfEmpty = true, $noMatchOptions = array())
{
  DB_Connect();
  $where = array('1');
  if (!is_array($matchOptions))
    $matchOptions = stringParamsToArray($matchOptions);
  foreach($matchOptions as $k => $v)
    $where[] = '('.$k.'="'.DB_Safe($v).'")';
  foreach($noMatchOptions as $k => $v)
    $where[] = '('.$k.'!="'.DB_Safe($v).'")';
  $iwhere = implode(' AND ', $where);
	$query = 'SELECT * FROM '.getTableName($table).
    ' WHERE '.$iwhere;
  $resultDS = DB_GetDatasetWQuery($query);
  if ($fillIfEmpty && sizeof($resultDS) == 0)
    foreach($matchOptions as $k => $v)
      $resultDS[$k] = $v;
  return($resultDS);
}

// from table $tablename, get dataset with key $keyvalue
function DB_GetDataSet($tablename, $keyvalue, $keyname = '', $options = array())
{
  if($keyvalue == '0') return(array());
  DB_Connect();
  $fields = @$options['fields'];
  $fields = first($fields, '*'); 
  if (!$GLOBALS['db_link']) return(array());

  checkTableName($tablename);
  if ($keyname == '')
  {
    $keynames = DB_GetKeys($tablename);
    $keyname = $keynames[0];
  }
  
  $cache_entry = $tablename.':'.$keyname.':'.$keyvalue;
  
  if(isset($GLOBALS['dbdatatmp'][$cache_entry])) return($GLOBALS['dbdatatmp'][$cache_entry]);

  $query = 'SELECT '.$fields.' FROM '.$tablename.' '.$options['join'].' WHERE '.$keyname.'="'.DB_Safe($keyvalue).'";';
  $rs = mysql_query($query, $GLOBALS['db_link']) or $DBERR = mysql_error($GLOBALS['db_link']).' { Query: "'.$query.'" }';
  if ($DBERR != '') logError('error_sql: '.$DBERR);

  if ($line = @mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    mysql_free_result($rs);
    $GLOBALS['dbdatatmp'][$cache_entry] = $line;
	  profile_point('DB_GetDataSet('.$tablename.', '.$keyvalue.')');
    return($line);    
  }
  else
    $result = array();

	profile_point('DB_GetDataSet('.$tablename.', '.$keyvalue.') #fail');
  return $result;
}

function DB_RemoveDataset($tablename, $keyvalue, $keyname = null)
{
  DB_Connect();
  checkTableName($tablename);
  if ($keyname == null)
  {
    $keynames = DB_GetKeys($tablename);
    $keyname = $keynames[0];
  }
  $rs = mysql_query('DELETE FROM '.$tablename.' WHERE '.$keyname.'="'.
  DB_Safe($keyvalue).'";', $GLOBALS['db_link'])
    or $DBERR = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';
  profile_point('DB_RemoveDataset('.$tablename.', '.$keyvalue.')');
  if (trim($DBERR)!='') logError('error_sql: '.$DBERR);
}

function DB_ParseQueryParams($query, $parameters = null)
{
  if ($parameters != null)
  {
    $pctr = 0;
    $result = '';
    for($a = 0; $a < strlen($query); $a++)
    {
      $c = substr($query, $a, 1);
      if ($c == '?')
      {
        $result .= '"'.DB_Safe($parameters[$pctr]).'"';
        $pctr++;
      }
      else
        $result .= $c;
    }
  }
  else
    $result = $query;
    
  return($result);
}

// retrieve dataset identified by SQL $query
function DB_GetDataSetWQuery($query, $parameters = null)
{
  DB_Connect();
  $query = DB_ParseQueryParams($query, $parameters);

  $rs = mysql_query($query, $GLOBALS['db_link'])
    or $DBERR = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';

  if (trim($DBERR)!='') logError('error_sql: '.$DBERR);
	
	if ($line = mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    $result = $line;
    mysql_free_result($rs);
  }
  else
  $result = array();
	profile_point('DB_GetDataSetWQuery('.$query.')');
  return $result;
}

// execute a simple update $query
function DB_Update($query, $parameters = null)
{
  DB_Connect();
  $query = trim($query);
  $query = DB_ParseQueryParams($query, $parameters);
  if (substr($query, -1, 1) == ';')
    $query = substr($query, 0, -1);
  $rs = mysql_query($query)
    or $DBERR = mysql_error().'{ '.$query.' }';
	profile_point('DB_Update('.$query.')');
  if (trim($DBERR)!='') logError('error_sql: '.$DBERR);
}

// get a list of datasets matching the $query
function DB_GetList($query, $parameters = null, $opt = array())
{
  DB_Connect();
  $result = array();
  $error = '';

  $query = DB_ParseQueryParams($query, $parameters);

  $lines = mysql_query($query, $GLOBALS['db_link']) or $error = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';

  if (trim($error) != '')
  {
    $DBERR = $error;
    logError($DBERR.' in '.$query);
  }
  else
  {
    while ($line = mysql_fetch_array($lines, MYSQL_ASSOC))
    {
      if (isset($keyByField))
        $result[$line[$keyByField]] = $line;
      else
        $result[] = $line;
    }
    mysql_free_result($lines);
  }
	profile_point('DB_GetList('.substr($query, 0, 40).'...)');
  return $result;
}

function DB_Connect()
{
  if($GLOBALS['db_link']) return;
  profile_point('DB_Connect() start');
  $DBERR = null;
  $GLOBALS['db_link'] = mysql_pconnect(cfg('db/host'), cfg('db/user'), cfg('db/password')) or
    $DBERR = 'The database connection to server '.cfg('db/user').'@'.cfg('db/host').' could not be established (code: '.@mysql_error($GLOBALS['db_link']).')';
  if($DBERR == null)
    mysql_select_db(cfg('db/database'), $GLOBALS['db_link']) or
      $DBERR = 'The database connection to database '.cfg('db/database').' on '.cfg('db/user').'@'.cfg('db/host').' could not be established. (code: '.@mysql_error($GLOBALS['db_link']).')';
  if ($DBERR != null)
  {
    $startupErrors = 'Seems like something went wrong with the Hubbub database :-(<br/>'.$DBERR;
    #h2_errorhandler(0, $startupErrors);	
  	die();
  }
  else
  {
    #mysql_query("SET NAMES 'utf8'", $GLOBALS['db_link']);
    if(mysql_client_encoding() != 'utf8') mysql_set_charset('utf8');
    profile_point('DB_Connect() done');
  }
}

?>