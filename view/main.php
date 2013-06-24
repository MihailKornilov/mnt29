<?php
function _header($title)
{
    global $html;
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        '<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
        '<HEAD>'.
            '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.
            (DEBUG == 1 ? '<SCRIPT type="text/javascript" src="http://nyandoma.ru/js/errors.js?'.VERSION.'"></SCRIPT>':'').
            (DEBUG == 1 ? '<SCRIPT type="text/javascript">var T = (new Date()).getTime();</SCRIPT>':'').
            '<SCRIPT type="text/javascript" src="/js/jquery-1.9.1.min.js"></SCRIPT>'.
            '<SCRIPT type="text/javascript">'.
                'var DOMAIN = "'.DOMAIN.'";'.
                'var URL = "'.URL.'";'.
                'var VERSION = '.VERSION.';'.
            '</SCRIPT>'.
            '<SCRIPT type="text/javascript" src="/js/global.js?'.VERSION.'"></SCRIPT>'.
            '<LINK href="/css/global.css?'.VERSION.'" rel="stylesheet" type="text/css">'.
            '<TITLE>'.$title.'</TITLE>'.
        '</HEAD>'.
        '<BODY class="mnt29">'.

        '<DIV id="mainDiv">';
}//end of _header()

function _footer() {
    global $html, $sqlQuery;
    $html .= '</div></BODY></HTML>';
}//end of _footer()

function _center($content)
{
    global $html;
    $html .= $content;
}//end of _center()

function _menu($p='client')
{
    if(!AUTH)
        return;
    global $html;
    $menu = array(
        'client' => 'Заказчики',
        'zakaz' => 'Заказы',
        'exit' => 'Выход'
    );
    $send = '';
    foreach($menu as $link => $m) {
        $send .= '<a href="'.URL.'/'.$link.'" class="punkt'.($p == $link ? ' active' : '').'">'.$m.'</a>';
    }
    $html .= '<div id="main_menu">'.$send.'</div>';
}//end of _menu()

function show_clients($d='')
{
    switch($d) {
        case 'add': return show_client_add();
        case 'info': return show_client_info(@$_GET['id']);
        case 'edit': return show_client_edit(@$_GET['id']);
        default:;
    }
    global $title;
    $title = 'Заказчики';
    return '<div id="client">'.
        '<input type="text" id="client_search" placeholder="Быстрый поиск..." />'.
        '<a href="'.URL.'/client/add" class="add">Внести нового заказчика</a>'.
        '<div id="client_spisok">'.show_clients_spisok().'</div>'.
    '</div>';
}//end of show_clients()

function show_clients_spisok($val=false, $page=1)
{
    $limit = 20;
    $where = "WHERE id";
    if($val)
        $where .= " AND (`fio` LIKE '%".$val."%'
                    OR `telefon` LIKE '%".$val."%'
                    OR `adres` LIKE '%".$val."%')";
    $sql = "SELECT COUNT(`id`) AS `count` FROM `client` ".$where." LIMIT 1";
    if(!($r = mysql_fetch_assoc(query($sql))))
        return 'Заказчиков не найдено.';
    $count = $r['count'];
    $start = ($page - 1) * $limit;

    $sql = "SELECT *
            FROM `client`
            ".$where."
            ORDER BY `id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $clients = '';
    if($page == 1)
        $clients .= '<h1>Всего: '.$count.'</h1>';
    while($r = mysql_fetch_assoc($q)) {
        $clients .= '<div class="client_unit">'.
            ($r['balans'] != 0 ? '<div class="balans">Баланс: '.balansColor($r['balans']).'</div>' : '').
            '<table cellspacing="3">'.
                '<tr><td class="label">ФИО:<td><td><a href="'.URL.'/client/'.$r['id'].'">'.$r['fio'].'</a><td></tr>'.
                ($r['telefon'] ? '<tr><td class="label">Телефон:<td><td>'.$r['telefon'].'<td></tr>' : '').
                ($r['adres'] ? '<tr><td class="label">Адрес:<td><td>'.$r['adres'].'<td></tr>' : '').
                ($r['zakaz_count'] > 0 ? '<tr><td class="label">Заказы:<td><td><b>'.$r['zakaz_count'].'</b><td></tr>' : '').
            '</table>'.
            '</div>';
    }
    if($start + $limit < $count)
        $clients .= '<div id="client_next" class="spisokNext" val="'.($page + 1).'">Показать ещё заказчиков</div>';
    return $clients;
}//end of show_clients()

function show_client_add()
{
    global $title;
    $title = 'Внесение нового заказчика';
    return '<div id="client_add">'.
            '<h1>Внесение нового заказчика:</h1>'.
            '<table cellspacing="5">'.
                '<tr><td class="label">ФИО:</td><td><input type="text" id="fio" /></td></tr>'.
                '<tr><td class="label">Контакные телефоны:</td><td><input type="text" id="telefon" /></td></tr>'.
                '<tr><td class="label">Адрес:</td><td><input type="text" id="adres" /></td></tr>'.
                '<tr><td></td><td><button id="client_add_button">Внести</button> <span class="error"></span></td></tr>'.
            '</table>'.
        '</div>';
}//end of show_client_add()

function show_client_info($id=0)
{
    if(!preg_match(REGEXP_NUMERIC, $id))
        return 'Клиент не найден.';
    $sql = "SELECT * FROM `client` WHERE `id`=".intval($id)." LIMIT 1";
    if(!($r = mysql_fetch_assoc(query($sql))))
        return 'Клиент не найден.';
    return '<div id="client_info">'.
        '<a href="'.URL.'/client/'.$r['id'].'/edit">Изменить данные заказчика</a> | '.
        '<a href="'.URL.'/zakaz/add/client='.$r['id'].'"><b>Внести новый заказ</b></a>'.
        '<table cellspacing="6" class="info_tab">'.
            '<tr><td class="label">ФИО:</td><td>'.$r['fio'].'</td></tr>'.
            '<tr><td class="label">Телефоны:</td><td>'.$r['telefon'].'</td></tr>'.
            '<tr><td class="label">Адрес:</td><td>'.$r['adres'].'</td></tr>'.
            '<tr><td class="label">Баланс:</td><td>'.balansColor($r['balans']).'</td></tr>'.
        '</table>'.
        '<div class="headName">Заказы</div>'.
        '<input type="hidden" id="zakaz_client_id" value="'.$id.'">'.
        '<div id="zakaz_spisok">'.show_zakaz_spisok(array('client_id' => $id)).'</div>'.
    '</div>';
}//end of show_client_info()

function show_client_edit($id=0)
{
    global $title;
    $title = 'Изменения данных заказчика';
    if(!preg_match(REGEXP_NUMERIC, $id))
        return 'Клиент не найден.';
    $sql = "SELECT * FROM `client` WHERE `id`=".intval($id)." LIMIT 1";
    if(!($r = mysql_fetch_assoc(query($sql))))
        return 'Клиент не найден.';
    return '<div id="client_edit">'.
        '<h1>Изменения данных заказчика:</h1>'.
        '<table cellspacing="5">'.
            '<tr><td class="label">ФИО:</td><td><input type="text" id="fio" value="'.$r['fio'].'" /></td></tr>'.
            '<tr><td class="label">Контакные телефоны:</td><td><input type="text" id="telefon" value="'.$r['telefon'].'" /></td></tr>'.
            '<tr><td class="label">Адрес:</td><td><input type="text" id="adres" value="'.$r['adres'].'" /></td></tr>'.
            '<tr><td align="right"><a href="'.URL.'/client/'.$r['id'].'">Отмена</a></td>'.
                '<td><button id="client_edit_button">Сохранить</button> <span class="error"></span></td></tr>'.
        '</table>'.
        '<input type="hidden" id="client_id" value="'.$r['id'].'">'.
    '</div>';
}//end of show_client_add()

function clientBalansUpdate($client_id)
{
    $sql = "SELECT COUNT(`id`) AS `count` FROM `zakaz` WHERE `client_id`=".$client_id." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $zakaz_count = $r['count'];

    $sql = "SELECT IFNULL(SUM(`sum`),0) AS `sum` FROM `accrual` WHERE `client_id`=".$client_id." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $accrual = $r['sum'];

    $sql = "SELECT IFNULL(SUM(`sum`),0) AS `sum` FROM `money` WHERE `client_id`=".$client_id." LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $money = $r['sum'];

    $balans = $money - $accrual;
    $sql = "UPDATE `client`
            SET `balans`=".$balans.",
                `zakaz_count`=".$zakaz_count."
            WHERE `id`=".$client_id." LIMIT 1";
    query($sql);
}//end of clientBalansUpdate()

function balansColor($sum)
{
    $color = $sum < 0 ? 'a33' : '080';
    return '<b style="color:#'.$color.'">'.round($sum, 2).'</b>';
}//end of balansColor()

function zakazStatus($id=false)
{
    $status = array(
        1 => array(
            'title' => 'Оформлен',
            'class' => 'oform'
        ),
        2 => array(
            'title' => 'В работе',
            'class' => 'inwork'
        ),
        3 => array(
            'title' => 'Выполнено',
            'class' => 'ended'
        ),
        4 => array(
            'title' => 'Отказ',
            'class' => 'fail'
        )
    );
    return $id ? $status[$id] : $status;
}//end of zakazStatus()

function getZakazStatusSelect($status_id)
{
    $send = '<select id="zakaz_status">';
    foreach(zakazStatus() as $id => $st)
        $send .= '<option value="'.$id.'"'.($status_id == $id ? ' selected="selected"' : '').'>'.$st['title'].'</option>';
    $send .= '</select>';
    return $send;
}//end of getZakazStatusSelect()

function show_zakaz($d='')
{
    switch($d) {
        case 'add': return show_zakaz_add(@$_GET['id']);
        case 'info': return show_zakaz_info(@$_GET['id']);
        case 'edit': return show_zakaz_edit(@$_GET['id']);
        default:;
    }
    global $title;
    $title = 'Заказы';
    $statusCond = '';
    foreach(zakazStatus() as $id => $st)
        $statusCond .= '<label><input type="radio" name="status" value="'.$id.'"> '.$st['title'].'</label>';
    return '<div id="zakaz">'.
        '<div id="cond">'.
            '<label><input type="radio" name="status" value="0" checked="checked"> Все</label>'.
            $statusCond.
        '</div>'.
        '<div id="zakaz_spisok">'.show_zakaz_spisok().'</div>'.
    '</div>';
}//end of show_zakaz()

function show_zakaz_spisok($cond=array(), $page=1)
{
    $limit = 20;
    $where = "WHERE `z`.`id`";
    if(isset($cond['client_id']) && $cond['client_id'] > 0)
        $where .= " AND `z`.`client_id`=".$cond['client_id'];
    if(isset($cond['status']) && $cond['status'] > 0)
        $where .= " AND `z`.`status`=".$cond['status'];
    $sql = "SELECT COUNT(`z`.`id`) AS `count`
            FROM `zakaz` AS `z`
               LEFT JOIN `client` AS `c`
               ON `z`.`client_id`=`c`.`id`
            ".$where."
            LIMIT 1";
    $r = mysql_fetch_assoc(query($sql));
    $count = intval($r['count']);
    if($count == 0)
        return 'Заказов не найдено.';
    $start = ($page - 1) * $limit;
    $sql = "SELECT
                `z`.`id`,
                `z`.`client_id`,
                `z`.`work_about`,
                `z`.`work_adres`,
                `c`.`fio`,
                `z`.`status`
            FROM `zakaz` AS `z`
               LEFT JOIN `client` AS `c`
               ON `z`.`client_id`=`c`.`id`
            ".$where."
            ORDER BY `z`.`id` DESC
            LIMIT ".$start.",".$limit;
    $q = query($sql);
    $spisok = $page == 1 ? '<div class="all">Всего: '.$count.'</div>' : '';
    while($r = mysql_fetch_assoc($q)) {
        $status = zakazStatus($r['status']);
        $spisok .= '<div class="zakaz_unit '.$status['class'].'" val="'.$r['id'].'">'.
            '<h1>Заказ №'.$r['id'].'</h1>'.
            '<table cellspacing="5">'.
                '<tr><td class="label">Заказчик:</td><td><a href="'.URL.'/client/'.$r['client_id'].'">'.$r['fio'].'</a></td></tr>'.
                '<tr><td class="label top">Описание:</td><td>'.$r['work_about'].'</td></tr>'.
                '<tr><td class="label">Адрес:</td><td>'.$r['work_adres'].'</td></tr>'.
            '</table>'.
            '</div>';
    }
    if($start + $limit < $count)
        $spisok .= '<div id="zakaz_next" class="spisokNext" val="'.($page + 1).'">Показать ещё заказы</div>';
    return $spisok;
}//end of show_zakaz_spisok()

function show_zakaz_add($client_id=0)
{
    if(!preg_match(REGEXP_NUMERIC, $client_id))
        return 'Клиент, для которого должен производиться заказ, не найден.';
    $sql = "SELECT * FROM `client` WHERE `id`=".intval($client_id);
    if(!($r = mysql_fetch_assoc(query($sql))))
        return 'Клиент, для которого должен производиться заказ, не найден.';
    return '<div id="zakaz_add">'.
        '<h1>Внесение нового заказа:</h1>'.
        '<table cellspacing="8">'.
            '<tr><td class="label">Заказчик:</td><td><a href="'.URL.'/client/'.$r['id'].'">'.$r['fio'].'</a></td></tr>'.
            '<tr><td class="label top">Описание работ:</td><td><textarea id="work_about"></textarea></td></tr>'.
            '<tr><td class="label">Адрес проведения работ:</td><td><input type="text" id="work_adres" /></td></tr>'.
            '<tr><td class="label">Ответственный:</td><td><input type="text" id="responsible" maxlength="150" /></td></tr>'.
            '<tr><td class="label">Дата исполнения:</td><td><input type="text" id="date_exec" maxlength="50" /></td></tr>'.
            '<tr><td class="label top">Изображения:</td><td>'.zakaz_images().'</td></tr>'.
            '<tr><td class="label">Стоимость осмотра:</td>'.
                '<td><input type="text" id="cost_osmotr" /> руб.'.
                    '<span id="oplata_osmotr_span">Оплачено?'.
                        '<label><input type="radio" name="oplata_osmotr" value="1">Да</label>'.
                        '<label><input type="radio" name="oplata_osmotr" value="2">Нет</label>'.
                    '</span>'.
                '</td></tr>'.
            '<tr><td class="label">Примечание:</td><td><input type="text" id="comment" /></td></tr>'.
            '<tr><td align="right"><a href="'.URL.'/client/'.$client_id.'">Отмена</a></td>'.
                '<td><button id="zakaz_add_button">Внести</button> <span id="zakaz_error" class="error"></span></td></tr>'.
        '</table>'.
        '<input type="hidden" id="client_id" value="'.$client_id.'">'.
    '</div>';
}//end of show_zakaz_add()

function show_zakaz_info($id=0)
{
    if(!preg_match(REGEXP_NUMERIC, $id))
        return 'Заказ не найден.';
    $sql = "SELECT * FROM `zakaz` WHERE `id`=".intval($id)." LIMIT 1";
    if(!($zakaz = mysql_fetch_assoc(query($sql))))
        return 'Заказ не найден.';

    $sql = "SELECT * FROM `client` WHERE `id`=".$zakaz['client_id']." LIMIT 1";
    $client = mysql_fetch_assoc(query($sql));

    $imgs = json_decode('['.$zakaz['images'].']');
    $images = '';
    if(count($imgs) > 0) {
        $images = '<div class="headName">Изображения</div>';
        foreach($imgs as $im)
            $images .= '<a href="'.$im->link.'" target="_blank" class="img"><img src="'.$im->link.'" height="100"></a>';
    }

    $sql = "SELECT * FROM `zakaz_comment` WHERE `zakaz_id`=".$zakaz['id']." ORDER BY `dtime_add` ASC";
    $q = query($sql);
    $comments = '';
    while($r = mysql_fetch_assoc($q))
        $comments .= commentUnit($r);

    $money = '<div class="headName">'.
        'Денежные операции'.
        '<div class="insert">'.
            'Внести: '.
            '<a id="accrualAdd_show">начисление</a> :: '.
            '<a id="moneyAdd_show">платёж</a>'.
        '</div>'.
    '</div>'.
    '<div id="zakaz_accrual_insert">'.
        '<h2>Внесение начисления:</h2>'.
        '<table cellspacing="8">'.
            '<tr><td class="label">Сумма:</td><td><input type="text" class="summa" id="accrual_sum"> руб.</td></td>'.
            '<tr><td class="label">Описание:</td><td><input type="text" id="accrual_about"></td></td>'.
            '<tr><td></td><td><button id="accrual_insert_button">Внести</button> <span id="accrual_error" class="error"></span></td></td>'.
        '</table>'.
    '</div>'.
    '<div id="zakaz_money_insert">'.
        '<h2>Внесение платежа:</h2>'.
        '<table cellspacing="8">'.
            '<tr><td class="label">Сумма:</td><td><input type="text" class="summa" id="money_sum"> руб.</td></td>'.
            '<tr><td class="label">Описание:</td><td><input type="text" id="money_about"></td></td>'.
            '<tr><td></td><td><button id="money_insert_button">Внести</button> <span id="money_error" class="error"></span></td></td>'.
        '</table>'.
    '</div>';

    $status = zakazStatus($zakaz['status']);
    return '<div id="zakaz_info">'.
        '<input type="hidden" id="zakaz_id" value="'.$zakaz['id'].'">'.
        '<input type="hidden" id="client_id" value="'.$zakaz['client_id'].'">'.
        '<h1>Информация о заказе №'.$zakaz['id'].' <a href="'.URL.'/zakaz/'.$zakaz['id'].'/edit">Изменить заказ</a></h1>'.
        '<table cellspacing="8" class="info_tab">'.
            '<tr><td class="label">Заказчик:</td><td><a href="'.URL.'/client/'.$client['id'].'">'.$client['fio'].'</a></td></tr>'.
            '<tr><td class="label top">Описание работ:</td><td><div class="work_about">'.$zakaz['work_about'].'</div></td></tr>'.
            '<tr><td class="label">Адрес проведения работ:</td><td>'.$zakaz['work_adres'].'</td></tr>'.
            '<tr><td class="label">Ответственный:</td><td>'.($zakaz['responsible'] ? $zakaz['responsible'] : '<b style="color:#a22">не назначен</b>').'</td></tr>'.
            '<tr><td class="label">Дата исполнения:</td><td>'.($zakaz['date_exec'] ? $zakaz['date_exec'] : '<b style="color:#a22">не установлена</b>').'</td></tr>'.
            '<tr><td class="label">Статус:</td><td><div class="status '.$status['class'].'">'.$status['title'].'<div></td></tr>'.
            '<tr><td class="label">Дата внесения:</td><td>'.FullDataTime($zakaz['dtime_add']).'</td></tr>'.
        '</table>'.
        $images.
        $money.
        '<div id="zakaz_moneyAccrual">'.show_money_accrual($zakaz['id']).'</div>'.
        '<div class="headName">Комментарии</div>'.
        '<div id="zakaz_comments">'.$comments.'</div>'.
        '<input type="text" id="zakaz_comment_add" placeholder="Новый комментарий: введите текст и нажмите Enter">'.
    '</div>';
}//end of show_zakaz_info()

function commentUnit($r) {
    return '<div class="unit">'.
        '<h3>'.FullDataTime($r['dtime_add']).'</h3>'.
        $r['txt'].
    '</div>';
}//end of commentUnit()

function show_money_accrual($zakaz_id) {
    $sql = "SELECT * FROM `accrual` WHERE `zakaz_id`=".$zakaz_id." ORDER BY `id`";
    $q_acc = query($sql);
    $sql = "SELECT * FROM `money` WHERE `zakaz_id`=".$zakaz_id." ORDER BY `id`";
    $q_mon = query($sql);
    $send = 'Денежных операций нет';
    if(mysql_num_rows($q_acc) || mysql_num_rows($q_mon)) {
        $send = '<table class="tabSpisok">';
        while($acc = mysql_fetch_assoc($q_acc)) {
            $send .= '<tr>'.
                '<td class="td">Начисление</td>'.
                '<td class="td sum">'.round($acc['sum'], 2).'</td>'.
                '<td class="td">'.$acc['about'].'</td>'.
                '<td class="td dtime">'.FullDataTime($acc['dtime_add']).'</td>'.
                '</tr>';
        }
        while($acc = mysql_fetch_assoc($q_mon)) {
            $send .= '<tr>'.
                '<td class="td">Платёж</td>'.
                '<td class="td sum">'.round($acc['sum'], 2).'</td>'.
                '<td class="td">'.$acc['about'].'</td>'.
                '<td class="td dtime">'.FullDataTime($acc['dtime_add']).'</td>'.
                '</tr>';
        }
        $send .= '<table>';
    }
    return $send;
}//end of show_money_accrual()

function show_zakaz_edit($id)
{
    if(!preg_match(REGEXP_NUMERIC, $id))
        return 'Заказ не найден.';
    $sql = "SELECT * FROM `zakaz` WHERE `id`=".intval($id)." LIMIT 1";
    if(!($r = mysql_fetch_assoc(query($sql))))
        return 'Заказ не найден.';
    $sql = "SELECT * FROM `client` WHERE `id`=".$r['client_id']." LIMIT 1";
    $client = mysql_fetch_assoc(query($sql));
    return '<div id="zakaz_edit">'.
        '<h1>Изменение данных заказа №'.$r['id'].':</h1>'.
        '<table cellspacing="8">'.
            '<tr><td class="label">Заказчик:</td><td><a href="'.URL.'/client/'.$client['id'].'">'.$client['fio'].'</a></td></tr>'.
            '<tr><td class="label top">Описание работ:</td><td><textarea id="work_about">'.$r['work_about'].'</textarea></td></tr>'.
            '<tr><td class="label">Адрес проведения работ:</td><td><input type="text" id="work_adres" value="'.$r['work_adres'].'" /></td></tr>'.
            '<tr><td class="label">Ответственный:</td><td><input type="text" id="responsible" maxlength="150" value="'.$r['responsible'].'" /></td></tr>'.
            '<tr><td class="label">Дата исполнения:</td><td><input type="text" id="date_exec" maxlength="50" value="'.$r['date_exec'].'" /></td></tr>'.
            '<tr><td class="label top">Изображения:</td><td>'.zakaz_images($r['images']).'</td></tr>'.
            '<tr><td class="label">Статус:</td><td>'.getZakazStatusSelect($r['status']).'</td></tr>'.
            '<tr><td align="right"><a href="'.URL.'/zakaz/'.$r['id'].'">Отмена</a></td>'.
                '<td><button id="zakaz_edit_button">Изменить</button> <span class="error"></span></td></tr>'.
        '</table>'.
        '<input type="hidden" id="zakaz_id" value="'.$id.'">'.
    '</div>';
}//end of show_zakaz_edit()

function zakaz_images($images='')
{
    return '<div id="zakaz_images"></div>'.
        '<SCRIPT type="text/javascript">var IMAGES = ['.$images.'];</SCRIPT>'.
        '<form method="post" '.
              'action="'.URL.'/view/include/fotoUpload.php" '.
              'enctype="multipart/form-data" '.
              'target="upload_frame" '.
              'id="upload_form">'.
            '<div id="upload_input_file"><input type="file" name="file_name" id="zakaz_img_upload"></div>'.
            '<div id="image_error" class="error"></div>'.
            '<IFRAME name="upload_frame"></IFRAME>'.
        '</form>';
}//end of zakaz_images()
