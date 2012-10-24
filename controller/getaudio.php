<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/media/{trackid}', function ($trackid) use ($app) {
    Log::write("Файл в кеше не найден trackid={$trackid}", __LINE__);
    //todo проверять на выдачу нескольких экземпляров
    //todo Сделать объект для tracka
    $trackid = substr($trackid, 0, -4);
    $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
    $data = json_decode($resp, true);
    $data = $data['response'];
    $filename = $data[0]['artist'] . "-" . $data[0]['title'] . ".mp3";
    $url = $data[0]['url'];
    $size = Core::remotefilesize($url);
    Log::write("trackid={$trackid} url={$url} filename={$filename} size={$size}", __LINE__);
    if (Core::getfilevk($trackid, $url, $size)) {
        return $app->redirect("/media/{$trackid}.mp3");
    }


});

$app->get('/getprevtrack/', function () use ($app) {
    $state = State::load();
    $app['monolog']->addDebug(__FUNCTION__ . ' Загрузили состояние ', array('state' => $state));
    $serial = $state['serial'] - 1;

    $app['monolog']->addDebug(__FUNCTION__ . ' Выбор предыдущего трека, смотрим порядковый номер текущего трека ', array('serial' => $serial));

    if ($serial > 0) {
        $filename = State::GetSongbySerial($serial);
        $trackid = substr($filename, 0, -4);
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
        $data = json_decode($resp, true);
        $data = $data['response'];
        State::save($state['user_id'], $state['side'], $state['playlistid'], $state['search'], $serial, $state['currentpage'], $state['sort']);
        return json_encode(array('filename' => $filename, 'artist' => $data[0]['artist'], 'trak' => $data[0]['title']));
    } else {
        return false;
    }


});

$app->get('/getnexttrack/', function () use ($app) {
    $state = State::load();
    $app['monolog']->addDebug(__FUNCTION__ . ' Загрузили состояние ', array('state' => $state));
    $serial = $state['serial'] + 1;

    $app['monolog']->addDebug(__FUNCTION__ . ' Выбор следующего трека, смотрим порядковый номер трека ', array('serial' => $serial));

    if ($serial > 0) {

        $filename = State::GetSongbySerial($serial);

        if (!empty($filename)) {
            $trackid = substr($filename, 0, -4);
            $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
            $data = json_decode($resp, true);
            $data = $data['response'];
            State::save($state['user_id'], $state['side'], $state['playlistid'], $state['search'], $serial, $state['currentpage'], $state['sort']);
            return json_encode(array('filename' => $filename, 'artist' => $data[0]['artist'], 'trak' => $data[0]['title']));
        } else {
            return false;
        }
    } else {
        return false;
    }


});


$app->get('/getsong/{trackid}', function (Request $request, $trackid) use ($app) {
    session_write_close();
    //$trackid = substr($trackid, 0, -4);

    if (!file_exists($GLOBALS['conf']['upload_dir'] . $trackid . ".mp3")) {
        //todo проверять на выдачу нескольких экземпляров
        //todo Сделать объект для tracka
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
        $data = json_decode($resp, true);
        $data = $data['response'];
        $fname = $data[0]['artist'] . "-" . $data[0]['title'] . ".mp3";
        $url = $data[0]['url'];
        $size = Core::remotefilesize($url);
        if (Core::getfilevk($trackid, $url, $size)) {
            $fs = filesize($GLOBALS['conf']['upload_dir'] . $trackid . ".mp3");
        }
    } else {
        if (Core::GetFileName($trackid)) {
            if ($fname = Core::GetFileName($trackid)){
                $fs = filesize($GLOBALS['conf']['upload_dir'] . $trackid . ".mp3");
            }
        } else {
            Log::write(__FILE__ . ": File name не найден в базе, надо идти в vk.com", __LINE__);
            $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
            $data = json_decode($resp, true);
            $data = $data['response'];
            $fname = $data[0]['artist'] . "-" . $data[0]['title'] . ".mp3";
            $fs = filesize($GLOBALS['conf']['upload_dir'] . $trackid . ".mp3");
        }
    }

    $fileurl = $GLOBALS['conf']['upload_dir'] . $trackid . ".mp3";
    header("Content-Length: {$fs}");
    header('Last-Modified:');
    header('ETag:');
    header('Content-Type: audio/mpeg');
    header('Accept-Ranges: bytes');

    header("Content-Disposition: attachment; filename=\"{$fname}\"");
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');

    return $app->stream(function () use ($fileurl) {
        readfile($fileurl);
    }, 200, array('Content-Type' => 'application/force-download'));
});

/*
$app->get('/getsong/{trackid}', function ( $trackid ) use ($app){
	$path = APPLICATION_PATH.DS.$GLOBALS['conf']['upload_dir'].DS;
	session_write_close();
	if (file_exists($path.$trackid)){
	$size = filesize ($path.$trackid);
	
	return $app->stream(function () use ($path, $trackid) {readfile($path.$trackid);}, 200, array('Content-Type' => 'audio/mpeg','Transfer-Encoding' => 'binary'));
} else { return new Response('We are sorry, but something went terribly wrong.', 404); }


});
*/