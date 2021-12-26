<?php
require_once 'api.tg.php';
require_once 'api.vk.php';

function getVideoUrl($owner_id, $sign, $id, $access_key, $tg, $title) {
    if ($sign) {
        $owner_id *= -1;
    }
    
    $params = array(
        'access_token' => VK_USER_TOKEN,
        'owner_id' => $owner_id,
        'videos' => $owner_id . '_' . $id . '_' . $access_key,
        'count' => 1,
        'offset' => 0,
        'extended' => 1,
        'v' => '5.126',
    );
    $url = 'https://api.vk.com/method/video.get?' . http_build_query($params);

    $video_data = json_decode(file_get_contents($url), true);


    if (!$video_data['response']['count']) {
        echo 'Видео недоступно, возможно оно удалено, изменилась ссылка или просто скрыто.';
        exit;
    }

    $vk_video_url = $video_data['response']['items'][0]['player'];

    $page = file_get_contents($vk_video_url);
    $page = iconv('CP1251', 'UTF-8', $page);
    if (strripos($page, 'Данная видеозапись скрыта настройками приватности и недоступна для просмотра')) {
        $button = '<a id="video_ext_btn" class="flat_button button_big" href="https://t.me/Vk2TgConnectBot" target="_blank" style="font-size:13px; margin: 20px 0; background-color: #0088cc; margin-bottom: 0;">Перейти в Telegram</a>';
        $page = preg_replace('/недоступна для просмотра\.\s+<\/div>/', 'недоступна для просмотра.<br>Можете попросить <b><a style="color: white; text-decoration: underline;" href="//vk.com/id' . $owner_id . '">владельца этой видеозаписи</a></b> скинуть её вам лично.<br>Если оригинал не сохранился, пусть добавит в свой профиль, потом скинет вам.<br>Тогда его можно будет скачать через @Vk2TgConnect</div>'. $button, $page);
        $page = str_replace('href="//vk.com"', 'href="//vk.com/Vk2TgConnect"', $page);
        echo $page; exit;
    }

    $first = explode('video_player', $page);
    $second = explode('progress', $first[1]);
    $src = explode('src="', $second[0]);
    $url = explode('" type', $src[2])[0];

    $time = time();

    file_put_contents('videos/video'.$time.'.mp4', file_get_contents(html_entity_decode($url)));

    return 'https://url.site/videos/video'.$time.'.mp4';
}