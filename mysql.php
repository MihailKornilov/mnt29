<?php

$mysql = array(
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '4909099',
    'database' => 'mnt29',
    'names' => NAMES
);
/*
$mysql = array(
    'host' => 'a6460.mysql.mchost.ru',
    'user' => 'a6460_mnt29',
    'pass' => '4909099',
    'database' => 'a6460_mnt29',
    'names' => NAMES
);
*/

$dbConnect = mysql_connect($mysql['host'], $mysql['user'], $mysql['pass'], 1) or die("Can't connect to database");
mysql_select_db($mysql['database'], $dbConnect) or die("Can't select database");
$sqlQuery = 0;
query('SET NAMES `'.NAMES.'`', $dbConnect);


function query($sql) {
    global $sqlQuery;
    $res = mysql_query($sql) or die($sql);
    $sqlQuery++;
    return $res;
}


