<?php
require_once('config.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

if($_POST['op'] == 'login') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $q = query("SELECT * FROM `login_log` WHERE `ip`='".$ip."' LIMIT 1");
    $r = mysql_fetch_assoc($q);
    if(!$r)
        query("INSERT INTO `login_log` (`ip`) values ('".$ip."')");
    else {
        if(time() > (strtotime($r['dtime_last']) + 1800)) {
            query("UPDATE `login_log` SET `count`=1,`dtime_last`=CURRENT_TIMESTAMP WHERE `ip`='".$ip."'");
            $r['count'] = 1;
        } else
            query("UPDATE `login_log` SET `count`=`count`+1 WHERE `ip`='".$ip."'");
        if($r['count'] > 4)
            jsonError('Превышено количество максимальных попыток. Попробуйте позднее.');
    }
    if(AUTH)
        jsonSuccess();
    $p['pass'] = trim(@$_POST['pass']);
    if(empty($p['pass']))
        jsonError('Не введён пароль');
    if(PASSWORD != md5($p['pass']))
        jsonError('Неверный пароль');
    query("UPDATE `login_log` SET `count`=1,`dtime_last`=CURRENT_TIMESTAMP WHERE `ip`='".$ip."'");
    $_SESSION['auth'] = 1;

    jsonSuccess();
}

if(!AUTH)
    jsonError('Необходимо авторизироваться');

switch($_POST['op']) {
    case 'client_add':
        $fio = trim($_POST['fio']);
        if(empty($fio))
            jsonError('Не указано ФИО.');
        $fio = htmlspecialchars($fio, ENT_QUOTES);
        $telefon = htmlspecialchars(trim($_POST['telefon']), ENT_QUOTES);
        $adres = htmlspecialchars(trim($_POST['adres']), ENT_QUOTES);
        $sql = "INSERT INTO `client`
                    (`fio`,`telefon`,`adres`,`dtime_add`)
                VALUES
                    ('".$fio."','".$telefon."','".$adres."',CURRENT_TIMESTAMP)";
        query($sql);
        $res['id'] = mysql_insert_id();
        jsonSuccess($res);
    break;
    case 'client_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError('Ошибка данных');
        $id = intval($_POST['id']);
        $fio = trim($_POST['fio']);
        if(empty($fio))
            jsonError('Не указано ФИО.');
        $fio = htmlspecialchars($fio, ENT_QUOTES);
        $telefon = htmlspecialchars(trim($_POST['telefon']), ENT_QUOTES);
        $adres = htmlspecialchars(trim($_POST['adres']), ENT_QUOTES);
        $sql = "UPDATE `client` SET
                    `fio`='".$fio."',
                    `telefon`='".$telefon."',
                    `adres`='".$adres."'
                WHERE `id`=".$id;
        query($sql);
        jsonSuccess();
    break;
    case 'client_search':
        $res['spisok'] = show_clients_spisok($_POST['val']);
        jsonSuccess($res);
    break;
    case 'client_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError('Ошибка данных');
        $res['spisok'] = show_clients_spisok($_POST['val'], intval($_POST['page']));
        jsonSuccess($res);
    break;
    case 'zakaz_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError('Ошибка данных');
        $cond['status'] = intval($_POST['status']);
        $cond['client_id'] = intval($_POST['client_id']);
        $res['spisok'] = show_zakaz_spisok($cond, intval($_POST['page']));
        jsonSuccess($res);
    break;
    case 'zakaz_search':
        if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
            jsonError('Ошибка данных');
        $cond['status'] = intval($_POST['status']);
        $res['spisok'] = show_zakaz_spisok($cond);
        jsonSuccess($res);
    break;
    case 'zakaz_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            jsonError('Ошибка данных');
        $client_id = intval($_POST['client_id']);
        $work_about = htmlspecialchars(trim($_POST['work_about']), ENT_QUOTES);
        $work_adres = htmlspecialchars(trim($_POST['work_adres']), ENT_QUOTES);
        $responsible = htmlspecialchars(trim($_POST['responsible']), ENT_QUOTES);
        $date_exec = htmlspecialchars(trim($_POST['date_exec']), ENT_QUOTES);
        $images = trim($_POST['images']);
        $sql = "INSERT INTO `zakaz`
                    (`client_id`,`work_about`,`work_adres`,`responsible`,`date_exec`,`images`,`dtime_add`)
                VALUES
                    (".$client_id.",'".$work_about."','".$work_adres."','".$responsible."','".$date_exec."','".$images."',CURRENT_TIMESTAMP)";
        query($sql);
        $res['id'] = mysql_insert_id();

        $cost_osmotr = intval(trim($_POST['cost_osmotr']));
        if($cost_osmotr > 0) {
            $sql = "INSERT INTO `accrual`
                        (`zakaz_id`,`client_id`,`sum`,`about`,`dtime_add`)
                    VALUES
                        (".$res['id'].",".$client_id.",".$cost_osmotr.",'Выезд мастера для осмотра',CURRENT_TIMESTAMP)";
            query($sql);
            if($_POST['oplata_osmotr'] == 1) {
                $sql = "INSERT INTO `money`
                        (`zakaz_id`,`client_id`,`sum`,`about`,`dtime_add`)
                    VALUES
                        (".$res['id'].",".$client_id.",".$cost_osmotr.",'За выезд мастера для осмотра',CURRENT_TIMESTAMP)";
                query($sql);
            }
        }
        clientBalansUpdate($client_id);

        $comment = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES);
        if(!empty($comment)) {
            $sql = "INSERT INTO `zakaz_comment`
                        (`zakaz_id`,`txt`,`dtime_add`)
                    VALUES
                        (".$res['id'].",'".$comment."',CURRENT_TIMESTAMP)";
            query($sql);
        }

        jsonSuccess($res);
    break;
    case 'zakaz_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError('Ошибка данных');
        $id = intval($_POST['id']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
            jsonError('Ошибка данных');
        $work_about = htmlspecialchars(trim($_POST['work_about']), ENT_QUOTES);
        $work_adres = htmlspecialchars(trim($_POST['work_adres']), ENT_QUOTES);
        $responsible = htmlspecialchars(trim($_POST['responsible']), ENT_QUOTES);
        $date_exec = htmlspecialchars(trim($_POST['date_exec']), ENT_QUOTES);
        $images = trim($_POST['images']);
        $sql = "UPDATE `zakaz` SET
                    `work_about`='".$work_about."',
                    `work_adres`='".$work_adres."',
                    `responsible`='".$responsible."',
                    `date_exec`='".$date_exec."',
                    `status`='".intval($_POST['status'])."',
                    `images`='".$images."'
                WHERE `id`=".$id;
        query($sql);
        jsonSuccess();
    break;
    case 'accrual_insert':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zakaz_id']))
            jsonError('Ошибка данных');
        $zakaz_id = intval($_POST['zakaz_id']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            jsonError('Ошибка данных');
        $client_id = intval($_POST['client_id']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError('Ошибка данных');
        $sum = intval($_POST['sum']);
        $about = htmlspecialchars(trim($_POST['about']), ENT_QUOTES);
        $sql = "INSERT INTO `accrual`
                    (`zakaz_id`,`client_id`,`sum`,`about`,`dtime_add`)
                VALUES
                    (".$zakaz_id.",".$client_id.",".$sum.",'".$about."',CURRENT_TIMESTAMP)";
        query($sql);
        clientBalansUpdate($client_id);
        $res['spisok'] = show_money_accrual($zakaz_id);
        jsonSuccess($res);
        break;
    case 'money_insert':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zakaz_id']))
            jsonError('Ошибка данных');
        $zakaz_id = intval($_POST['zakaz_id']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            jsonError('Ошибка данных');
        $client_id = intval($_POST['client_id']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError('Ошибка данных');
        $sum = intval($_POST['sum']);
        $about = htmlspecialchars(trim($_POST['about']), ENT_QUOTES);
        $sql = "INSERT INTO `money`
                    (`zakaz_id`,`client_id`,`sum`,`about`,`dtime_add`)
                VALUES
                    (".$zakaz_id.",".$client_id.",".$sum.",'".$about."',CURRENT_TIMESTAMP)";
        query($sql);
        clientBalansUpdate($client_id);
        $res['spisok'] = show_money_accrual($zakaz_id);
        jsonSuccess($res);
        break;
    case 'zakaz_comment_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zakaz_id']))
            jsonError('Ошибка данных');
        $zakaz_id = intval($_POST['zakaz_id']);
        $txt = htmlspecialchars(trim($_POST['txt']), ENT_QUOTES);
        $res['html'] = '';
        if(!empty($txt)) {
            $sql = "INSERT INTO `zakaz_comment`
                        (`zakaz_id`,`txt`,`dtime_add`)
                    VALUES
                        (".$zakaz_id.",'".$txt."',CURRENT_TIMESTAMP)";
            query($sql);
            $res['html'] = commentUnit(array(
                'dtime_add' => strftime('%Y-%m-%d %H:%M:%S', time()),
                'txt' => $txt
            ));
        }
        jsonSuccess($res);
    break;
}

jsonError();