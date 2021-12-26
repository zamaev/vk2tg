<?php
require_once 'api.tg.php';
require_once 'api.vk.php';
require_once 'db.php';

$db = new DB();

$postData = file_get_contents('php://input');
if (!$postData) {
    echo 'ok';
    exit;
}

$data = json_decode($postData, true);
$tg = $data['message']['chat']['id'];
$vk = $db->getVk($tg);

if (!$vk || !$vk['vk']) {
    $key = $db->getKey($tg);
    sendTgMessage($tg, "Скопируй вот этот ключ:");
    sendTgMessage($tg, '<code>'.$key.'</code>', 'HTML');
    sendTgMessage($tg, "Перейди по ссылке https://vk.com/vk2tgconnect и там передай скопированный ключ в сообщении боту канала.");
} else {
    sendTgMessage($tg, 'Нет возможности переноса из Tg в Vk. Если нужен такой функционал, пиши в комментах в Vk.');
    sendTgMessage($tg, 'Отправляй боту Vk https://vk.com/Vk2TgConnect то, что хочешь перенести сюда.');

    $key = $db->getKey($tg);
    sendTgMessage($tg, "Вот твой ключ:");
    sendTgMessage($tg, '<code>'.$key.'</code>', 'HTML');
}