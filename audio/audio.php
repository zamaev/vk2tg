<?php
$str = '';
foreach ($_GET as $k => $v) {
    $str .= $k.'='.$v.'&';
}

preg_match('/https:\/\/(.+)\?.+/', $str, $match);
$old = $match[1];
$new = preg_replace('/_/', '.', $old);
$url = str_replace($old, $new, $str);

header('Content-Type: audio/mpeg');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Accept-Ranges: bytes');
header("Content-Transfer-Encoding: binary\n");
header('Connection: close');

readfile($url);