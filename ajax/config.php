<?php
require_once(dirname(dirname(__FILE__)).'/config.php');

function jsonError($text = 'Пустой запрос')
{
    $send = array(
        'error' => 1,
        'text' => $text
    );
    die(json_encode($send));
}//end of returnJsonError()

function jsonSuccess($values = array())
{
    $send = array(
        'success' => 1
    );
    die(json_encode($send + $values));
}//end of jsonSuccess()
