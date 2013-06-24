<?php
function _auth()
{
    if(isset($_SESSION['auth']) && $_SESSION['auth'] == 1)
        return true;
    return false;
}//end of _auth()

function _login()
{
    global $title;
    $title = 'вход';
    return '<div class="login">'.
        '<input type="password" id="pass" /> '.
        '<button id="login_button">Вход</button>'.
        '<br /><br />'.
        '<div class="error"></div>'.
    '<div>';
}//end of _login()

function _logout()
{
    session_destroy();
    unset($_SESSION);
    header('Location: '.URL);
    exit;
}//end of _logout()