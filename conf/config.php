<?php
// Конфиг приложения
// обращение из любой точки скрипта $temp = $GLOBALS['conf']['...']
//todo сделать нормальный класс для конфига и при construct получать значения из ini файла , сохранять при завершении

//if (defined("AKPLAYER")) {


// Production debug off
    $GLOBALS['conf'] = array(
        'web_root' => 'http://svirel.akmain.org/',
        'upload_dir' => APPLICATION_PATH.DS.'media'.DS,
        'main_title' => 'AK-Player самый обыкновенный MP3 в браузере',
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_password' => 'KRUS56_ak+',
        'db_port' => '3306',
        'db_name' => 'akplayer_test',
        'db_charset' => 'utf8',
        'email' => 'akolomiec@mail.ru',
        'password' => 'KRUS56_ak+',
        'AppId' => '2940672',
        'AppSecret' => 'A92zngusWGASXGqfGvmF',
        'OAuthCallback' => 'vkauth',
        'VKScope' => 'audio',
        'trackperpage' => 20,
        'uagent' =>  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1",
        'cookies' => APPLICATION_PATH.DS.'log'.DS."cookie",
        'cookie_domain' => 'svirel.akmain.org'
    );


/* Debug localhost
    $GLOBALS['conf'] = array(
        'web_root' => 'http://akmain.org/',
        'upload_dir' => APPLICATION_PATH.DS.'media'.DS,
        'main_title' => 'AK-Player самый обыкновенный MP3 в браузере',
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_password' => 'KRUS56_ak+',
        'db_port' => '3306',
        'db_name' => 'akplayer',
        'db_charset' => 'utf8',
        'email' => 'akolomiec@mail.ru',
        'password' => 'KRUS56_ak+',
        'AppId' => '2940672',
        'AppSecret' => 'A92zngusWGASXGqfGvmF',
        'OAuthCallback' => 'vkauth',
        'VKScope' => 'audio',
        'trackperpage' => 20,
        'uagent' =>  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1",
        'cookies' => APPLICATION_PATH.DS.'log'.DS."cookie",
        'cookie_domain' => '.akmain.org'
    );
    */

    // music fab-sveta ru
/*
    $GLOBALS['conf'] = array(
        'web_root' => 'http://music.fab-sveta.ru/',
        'upload_dir' => APPLICATION_PATH.DS.'media'.DS,
        'main_title' => 'AK-Player самый обыкновенный MP3 в браузере',
        'db_host' => 'mysql.fabsveta.mass.hc.ru',
        'db_user' => 'fabsveta_akplaye',
        'db_password' => 'de7Hruck',
        'db_port' => '3306',
        'db_name' => 'wwwfabsvetaru_akplayer',
        'db_charset' => 'utf8',
        'email' => 'akolomiec@mail.ru',
        'password' => 'KRUS56_ak+',
        'AppId' => '2940672',
        'AppSecret' => 'A92zngusWGASXGqfGvmF',
        'OAuthCallback' => 'vkauth',
        'VKScope' => 'audio',
        'trackperpage' => 20,
        'uagent' =>  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1",
        'cookies' => APPLICATION_PATH.DS.'log'.DS."cookie",
        'cookie_domain' => 'music.fab-sveta.ru'
    );

  */
//}

