<?php
require_once('config.php');
require_once('view/main.php');

if(!AUTH)
    $content = _login();
else {
    $title = 'Страница не существует';
    $content = 'Страница не существует';
    if(!isset($_GET['p']))
        $_GET['p'] = 'client';
    switch($_GET['p']) {
        case 'client':
            $content = show_clients(@$_GET['d']);
        break;
        case 'zakaz':
            $content = show_zakaz(@$_GET['d']);
        break;
        case 'exit': _logout(); break;
    }
}

_header('mnt29 - '.$title);
_menu(@$_GET['p']);
_center($content);
_footer();

mysql_close();

echo $html;
//phpinfo();