<?php
// Конфиг приложения
// обращение из любой точки скрипта $temp = $GLOBALS['conf']['...']
//todo сделать нормальный класс для конфига и при construct получать значения из ini файла , сохранять при завершении

if (defined("AKPLAYER")) {


// Production debug off
    $GLOBALS['conf'] = array(
        'web_root' => 'http://akmain.org/',
        'upload_dir' => APPLICATION_PATH.DS.'media'.DS,
        'main_title' => '',
        'db_host' => '',
        'db_user' => '',
        'db_password' => '',
        'db_port' => '3306',
        'db_name' => '',
        'db_charset' => 'utf8',
        'email' => '',
        'password' => '',
        'AppId' => '',
        'AppSecret' => '',
        'OAuthCallback' => 'vkauth',
        'VKScope' => 'audio',
        'trackperpage' => 20,
        'uagent' =>  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1",
        'cookies' => APPLICATION_PATH.DS.'log'.DS."cookie",
        'cookie_domain' => ''
    );
}

