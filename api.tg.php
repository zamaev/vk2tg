<?php

// https://api.telegram.org/botTOKEN
// https://api.telegram.org/botTOKEN/setWebhook?url=https://url.site/tg.php

define('TEST', 1);
define('TEST_TG_ID', 123456789);
function test($text) {
    if (is_array($text)) {
        $text = print_r($text, true);
    }
    if (TEST == 1) {
        sendTgMessage(TEST_TG_ID, $text, 'HTML');
    }
}

define('TG_TOKEN', 'TOKEN');

function tgApi($method, $params)
{
    $url = 'https://api.telegram.org/bot' . TG_TOKEN . '/' . $method . '?' . http_build_query($params);
    return file_get_contents($url);
}

function sendTgMessage($chat, $message, $parse_mode = '')
{
    return tgApi('sendMessage', array(
        'chat_id' => $chat,
        'text' => $message,
        'parse_mode' => $parse_mode
    ));
}

function sendPhoto($chat, $photo, $caption = '')
{
    return tgApi('sendPhoto', array(
        'chat_id' => $chat,
        'photo' => $photo,
        'caption' => $caption
    ));
}

function sendMediaGroup($chat, $media)
{
    return tgApi('sendMediaGroup', array(
        'chat_id' => $chat,
        'media' => json_encode($media)
    ));
}

function sendAudio($chat, $audio, $artist = '', $title = '', $caption = '')
{
    return tgApi('sendAudio', array(
        'chat_id' => $chat,
        'audio' => $audio,
        'performer' => $artist,
        'title' => $title,
        'caption' => $caption,
    ));
}

function sendVideo($chat, $video)
{
    $url = 'https://api.telegram.org/bot' . TG_TOKEN . '/sendVideo?chat_id=' . $chat . '&video=' . $video;
    return file_get_contents($url);
}

function sendVoice($chat, $voice)
{
    return tgApi('sendVoice', array(
        'chat_id' => $chat,
        'voice' => $voice,
    ));
}

function sendDocument($chat, $document)
{
    return tgApi('sendDocument', array(
        'chat_id' => $chat,
        'document' => $document
    ));
}

function sendLocation($chat, $latitude, $longitude)
{
    return tgApi('sendLocation', array(
        'chat_id' => $chat,
        'latitude' => $latitude,
        'longitude' => $longitude
    ));
}
