<?php
require_once '../api.tg.php';
require_once '../api.vk.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * /video/owner_id/signed(-1 +0)/id/video.mp4
 * /video/23213239/0/456240038/video.mp4
 */
preg_match('/^\/video\/(\d+)\/(\d+)\/(\d+)\/(\w+)\/(.+)\/(.+)\.mp4$/', $url, $matches);
if ($matches[1] && $matches[3]) {
    $owner_id = $matches[1];
    if ($matches[2]) {
        $owner_id *= -1;
    }
    $id = $matches[3];
    $access_key = $matches[4];
    $tg = $matches[5];
    $title = $matches[6];
} else {
    echo "Данная страница недоступна. Отправьте ролик еще раз.";
    exit;
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

header('Content-Type: video/mp4');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Accept-Ranges: bytes');
header("Content-Transfer-Encoding: binary\n");
header('Connection: close');

readfile(html_entity_decode($url));
