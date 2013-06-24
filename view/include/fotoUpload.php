<?php
// создание имени файла
function fileNameCreate() {
  $arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0');
  $name = '';
  for($i = 0; $i < 10; $i++) { $name .= $arr[rand(0,35)]; }
  return $name.'-';
}


// изменение размера изображения
function imResize(
    $im,       // картинка
    $x_cur,    // исходный X
    $y_cur,    // исходный Y
    $x_new,    // новый X
    $y_new,    // новый Y
    $name) {   // имя файла для сохранения

  // если ширина больше или равна высоте
  if ($x_cur >= $y_cur) {
    $x = $x_new;
    if ($x > $x_cur) { $x = $x_cur; } // если новая ширина больше, чем исходная, то X остаётся исходным
    $y = round($y_cur / $x_cur * $x);
    if ($y > $y_new) { // если новая высота в итоге осталась меньше исходной, то подравнивание по Y
      $y = $y_new;
      $x = round($x_cur / $y_cur * $y);
    }
  }

  // если выстоа больше ширины
  if ($y_cur > $x_cur) {
    $y = $y_new;
    if ($y > $y_cur) { $y = $y_cur; } // если новая высота больше, чем исходная, то Y остаётся исходным
    $x = round($x_cur / $y_cur * $y);
    if ($x > $x_new) { // если новая ширина в итоге осталась меньше исходной, то подравнивание по X
      $x = $x_new;
      $y = round($y_cur / $x_cur * $x);
    }
  }

  $im_new = imagecreatetruecolor($x, $y);
  imagecopyresampled($im_new, $im, 0, 0, 0, 0, $x, $y, $x_cur, $y_cur);
  imagejpeg($im_new, $name, 80);
  imagedestroy($im_new);

  $send['x'] = $x;
  $send['y'] = $y;
  return $send;
}


ini_set('memory_limit','120M');
require_once('../../config.php');
$cookie = "uploaded_";

//echo print_r($_FILES);


$file_name = fileNameCreate();
$path = DOCUMENT_ROOT."/files/images/";
$im = null;

$post_name = $_FILES["file_name"]["tmp_name"];
switch ($_FILES["file_name"]["type"]) {
    case 'image/jpeg': $im = @imagecreatefromjpeg($post_name); break;
    case 'image/png': $im = @imagecreatefrompng($post_name); break;
    case 'image/gif': $im = @imagecreatefromgif($post_name); break;
}

if (!$im) {
    $cookie = "error_1"; // если файл - не картинка
} else {
    $x = imagesx($im);
    $y = imagesy($im);
    if ($x < 200 or $y < 100) {
        $cookie = "error_2"; // если картинка имеет неправильные размеры
    } else {
        $send = imResize($im, $x, $y, 1300, 900, $path.$file_name."b.jpg");
        $json = $send['x'].','.$send['y'].','.$file_name.'b.jpg';
        setcookie("fotoJson", $json, time() + 3600, "/");
        echo $json;
    }
}


setcookie("fotoUpload", $cookie, time() + 3600, "/");

