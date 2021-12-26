<?php
require_once 'api.vk.php';
require_once 'api.tg.php';
require_once 'db.php';
require_once 'video.php';

define('SECRET', 'SECRET');

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

if ($_SERVER['HTTP_X_RETRY_COUNTER']) {
    $str = "Почему-то это сообщнеие не отправлось в прошлый раз: \n";
    $str .= print_r($data, true) . "\n\n";
    $str .= "Заголовки: \n";
    $str .= print_r($_SERVER, true);
    $file = time() . '.html';
    file_put_contents('temp/'.$file, "<pre>". $str . "</pre>");
    $url = 'https://url.site/temp/' . $file;
    echo 'ok';
    exit;
}
if ($data['secret'] != SECRET) {
    echo 'ahah lol';
    exit;
}

switch ($data['type']) {
    case 'confirmation':
        echo 'confirmation';
        break;
    case 'message_new':
        messagesMarkAsRead($data['object']['message']['peer_id'], $data['object']['message']['id']);
        message_handler($data);
        echo 'ok';
        break;
    default:
        echo 'Unsupported event';
        break;
}

function message_handler($data)
{
    $db = new DB();
    $vk = $data['object']['message']['peer_id'];
    $tg = $db->getTg($vk);
    if (!$tg) {
        $message = $data['object']['message']['text'];
        if ($message && $tg = $db->setVkByKey($vk, $message)) {
            sendVkMessage($vk, 'Есть контакт! Присылай мне все, что хочешь перенсти в Telegram.');
            sendTgMessage($tg, 'Воу воу! Я в деле. Теперь передавай боту Vk то, что хочешь перенести сюда.');
        } else {
            sendVkMessage($vk, 'Пришли мне ключ от бота Telegram: https://t.me/Vk2TgConnectBot');
        }
    } else {
        parse_message($vk, $tg['tg'], $data['object']['message']);
    }
}

function parse_message($vk, $tg, $data)
{
    if (!$data['text'] && !$data['attachments'] && !$data['fwd_messages'] && !$data['copy_history'] && !$data['geo']) {
        sendVkMessage($vk, 'Я получил от Vk пустое сообщение.');
        sendVkMessage($vk, 'Если вы присылали музыку, то добавьте её сначала в свой профиль, а затем пришлите напрямую через чат.');
        sendVkMessage($vk, 'Если это был другой запрос, сообщите об этом админу группы. Посмотрим что можно сделать.');
    }

    $text = $data['text'];

    if ($data['attachments']) {
        $files = parse_attachments($data['attachments']);

        if ($text && count($files['photo']) == 1) {
            if (strlen($text) <= 1024) {
                sendPhoto($tg, $files['photo'][0]['media'], $text);
            } else {
                $text .= ' <a href="' . $files['photo'][0]['media'] . '"> </a>' . ' ';
                sendTgMessage($tg, $text, 'HTML');
            }

        } else {
            if ($text) {
                sendTgMessage($tg, $text);
            }
            if ($files['photo']) {
                sendMediaGroup($tg, $files['photo']);
            }
        }
        if ($files['music'][0]['url'] == 'https://vk.com/mp3/audio_api_unavailable.mp3') {
            sendVkMessage($vk, 'Vk не позвляет боту напрамую достать аудио из поста. Попробуй добавить аудио сначала к себе, а потом отправь мне.');
        } else {
            foreach ($files['music'] as $music) {
                $title = str_replace(' ', ' ', $music['title'].' - '.$music['artist']);
                sendAudio($tg, 'https://url.site/audio/'.urlencode($title).'.mp3?' . $music['url']);
            }
        }
        foreach ($files['audio'] as $audio_url) {
            sendVoice($tg, $audio_url);
        }
        foreach ($files['video'] as $video) {
            $owner_id = $video['owner_id'] < 0 ? $video['owner_id'] * (-1) : $video['owner_id'];
            $sign = $video['owner_id'] < 0 ? 1 : 0;
            $id = $video['id'];
            $title = $video['title'];
            $access_key = $video['access_key'];
            sendVkMessage($vk, 'Это займет некоторое время, в зависимости от размера файла.');
            $video_url = getVideoUrl($owner_id, $sign, $id, $access_key, $tg, $title);

            $text = 'Скачать видео: <a href="' . $video_url . '">' . $title . '</a>';
            sendTgMessage($tg, $text, 'HTML');

        }
        foreach ($files['document'] as $document_url) {
            sendDocument($tg, $document_url);
        }
        foreach ($files['wall'] as $wall) {
            parse_message($vk, $tg, $wall);
        }
        foreach ($files['link'] as $link) {
            test($link);
            $text = '<b>'.$link['title'].'</b><br>'.$link['url'];
            sendTgMessage($vk, $text, "HTML");
        }
        foreach ($files['warn'] as $type) {
            sendVkMessage($vk, "Тип $type не поддерживается. По всем вопросам к админу.");
        }

    } else if ($text) {
        sendTgMessage($tg, $text);
    }
    foreach ($data['fwd_messages'] as $mess) {
        parse_message($vk, $tg, $mess);
    }
    foreach ($data['copy_history'] as $mess) {
        parse_message($vk, $tg, $mess);
    }
    if ($data['geo']['type'] == 'point') {
        sendLocation($tg, $data['geo']['coordinates']['latitude'], $data['geo']['coordinates']['longitude']);
    }
}

function parse_attachments($att)
{
    $files = array(
        'photo' => array(),
        'music' => array(),
        'audio' => array(),
        'document' => array(),
        'wall' => array(),
        'link' => array(),
        'warn' => array()
    );
    foreach ($att as $item) {
        if ($item['type'] == 'photo') {
            $files['photo'][] = array('type' => 'photo', 'media' => array_pop($item['photo']['sizes'])['url']);
        } else if ($item['type'] == 'audio') {
            $files['music'][] = array('url' => $item['audio']['url'], 'artist' => $item['audio']['artist'], 'title' => $item['audio']['title']);
        } else if ($item['type'] == 'audio_message') {
            $files['audio'][] = $item['audio_message']['link_ogg'];
        } else if ($item['type'] == 'doc') {
            $files['document'][] = $item['doc']['url'];
        } else if ($item['type'] == 'wall') {
            $files['wall'][] = $item['wall'];
        } else if ($item['type'] == 'video') {
            $files['video'][] = array('owner_id' => $item['video']['owner_id'], 'id' => $item['video']['id'], 'title' => $item['video']['title'], 'access_key' => $item['video']['access_key']);
        } else if ($item['type'] == 'link') {
            $files['link'][] = array('title' => $item['link']['title'], 'url' => $item['link']['url']);
        } else {
            $files['warn'][] = $item['type'];
        }
    }
    return $files;
}