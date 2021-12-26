<?php

// Получение токена https://vk.com/dev/authcode_flow_user
// https://oauth.vk.com/authorize?client_id=client_id&display=page&redirect_uri=https://url.site/vk.php&scope=24&response_type=code&v=5.126
// https://oauth.vk.com/access_token?client_id=client_id&client_secret=client_secret&redirect_uri=https://url.site/vk.php&code=code

define('VK_BOT_TOKEN', 'VK_BOT_TOKEN');
define('VK_USER_TOKEN', 'VK_USER_TOKEN');

function vkApi($method, $params) {
    $params['access_token'] = VK_BOT_TOKEN;
    $params['v'] = '5.126';
    $url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($params);
    return file_get_contents($url);
}

function sendVkMessage($user, $message) {
    return vkApi('messages.send', array(
        'peer_id' => $user,
        'random_id' => rand(),
        'message' => $message
    ));
}

function messagesMarkAsRead($user, $message_id) {
    return vkApi('messages.markAsRead', array(
        'peer_id' => $user,
        'message_id' => $message_id
    ));
}