<?php
define('TIME', microtime(true));
define('DEBUG', 0);
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'utf8');
define('DOMAIN', $_SERVER["SERVER_NAME"]);
define('URL', 'http://'.DOMAIN);

define('REGEXP_NUMERIC', '/^[0-9]{1,20}$/i');
define('REGEXP_BOOL', '/^[0-1]$/');
define('REGEXP_PAGENAME', '/^[\sa-zA-Zа-яА-ЯёЁ0-9_\.\,\"\'@-]{1,50}$/iu');

if(DEBUG) {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
}

session_name('mnt29');
session_start();

require_once('mysql.php');
require_once('auth.php');
getVersionAndPass();
define('AUTH', _auth());



header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

$MonthFull = array(
    1=>'января',
    2=>'февраля',
    3=>'марта',
    4=>'апреля',
    5=>'мая',
    6=>'июня',
    7=>'июля',
    8=>'августа',
    9=>'сентября',
    10=>'октября',
    11=>'ноября',
    12=>'декабря',
    '01'=>'января',
    '02'=>'февраля',
    '03'=>'марта',
    '04'=>'апреля',
    '05'=>'мая',
    '06'=>'июня',
    '07'=>'июля',
    '08'=>'августа',
    '09'=>'сентября'
);
$MonthCut = array(
    1=>'янв',
    2=>'фев',
    3=>'мар',
    4=>'апр',
    5=>'мая',
    6=>'июн',
    7=>'июл',
    8=>'авг',
    9=>'сент',
    10=>'окт',
    11=>'ноя',
    12=>'дек',
    '01'=>'янв',
    '02'=>'фев',
    '03'=>'мар',
    '04'=>'апр',
    '05'=>'мая',
    '06'=>'июн',
    '07'=>'июл',
    '08'=>'авг',
    '09'=>'сен'
);
function FullDataTime($value, $cut = 0) {
    // 14 апреля 2010 в 12:45
    global $MonthFull,$MonthCut;
    $arr = explode(" ", $value);
    $d = explode("-", $arr[0]);
    $t = explode(":", $arr[1]);
    return abs($d[2])." ".
           ($cut == 0 ? $MonthFull[$d[1]] : $MonthCut[$d[1]]).
           (date('Y') == $d[0] ? '' : ' '.$d[0])." в ".abs($t[0]).":".$t[1];
}

function getVersionAndPass() {
    if(DEBUG)
        query("UPDATE `setup` SET `version`=`version`+1 LIMIT 1");
    $q = query("SELECT * FROM `setup` LIMIT 1");
    $r = mysql_fetch_assoc($q);
    define('VERSION', $r['version']);
    define('PASSWORD', $r['password']);
}//end of getVersionAndPass()


