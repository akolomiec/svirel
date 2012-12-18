<?php
// Конфиг приложения
// обращение из любой точки скрипта $temp = $GLOBALS['conf']['...']
//todo сделать нормальный класс для конфига и при construct получать значения из ini файла , сохранять при завершении

if (defined("AKPLAYER")) {


// Production debug off
    $GLOBALS['conf'] = array(
        'web_root'      => 'http://akmain.org/',
        'upload_dir'    => APPLICATION_PATH . DS . 'media' . DS,
        'main_title'    => 'AK-player самый удобный mp3-плеер в браузере.',
        'db_host'       => 'localhost',
        'db_user'       => 'root',
        'db_password'   => 'KRUS56_ak+',
        'db_port'       => '3306',
        'db_name'       => 'akplayer',
        'db_charset'    => 'utf8',
        'email'         => 'akolomiec@mail.ru',
        'password'      => 'KRUS56_ak+',
        'AppId'         => '2940672',
        'AppSecret'     => 'A92zngusWGASXGqfGvmF',
        'OAuthCallback' => 'vkauth',
        'VKScope'       => 'audio',
        'trackperpage'  => 20,
        'maxtrack'      => 1000,
        'uagent'        => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1",
        'cookies'       => APPLICATION_PATH . DS . 'log' . DS . "cookie",
        'cookie_domain' => 'akmain.org',
        'cache_dir'     => APPLICATION_PATH . DS . 'cache' . DS,
        'lastfm_api'    => "http://ws.audioscrobbler.com/2.0/",
        'lastfm_apikey' => '1fa4d30d602b85ec1579f0de593b381a',
        'lastfm_secret' => '4e2b61191fed912fc64a8e713c552120'
    );
}

