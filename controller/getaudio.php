<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/media/{trackid}', function ($trackid) use ($app) {
    $trackid = substr($trackid, 0, -4);
    $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
    $data = json_decode($resp, true);
    $data = $data['response'];
    if (strlen($data[0]['artist'] . "-" . $data[0]['title'] . ".mp3") >= 255 ) {
        $data[0]['artist'] = substr($data[0]['artist'], 0 ,128);
        $data[0]['title'] = substr($data[0]['title'], 0 ,120);
    }
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
    $serial = $state['serial'] - 1;
    if ($serial > 0) {
        $filename = State::GetSongbySerial($serial);
        $trackid = substr($filename, 0, -4);
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
        $data = json_decode($resp, true);
        $data = $data['response'];
        State::save($state['user_id'], $state['side'], $state['playlistid'], $state['search'], $serial, $state['currentpage'], $state['repeat'], $state['shuffle'], $state['sort']);
        return json_encode(array('filename' => $filename, 'artist' => $data[0]['artist'], 'trak' => $data[0]['title']));
    } else {
        return false;
    }
});
$app->get('/getnexttrack/', function () use ($app) {
    $state = State::load();

    $serial = $state['serial'] + 1;
    if($serial > State::maxtrack($state)) {
        if (State::repeat()){
            $serial = 1;
            $app['monolog']->addInfo('Выбираем песню', array('serial' => $serial));
        } else return false;


    }
    if ($serial > 0) {
        $filename = State::GetSongbySerial($serial);
        if (!empty($filename)) {
            $trackid = substr($filename, 0, -4);
            $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
            $data = json_decode($resp, true);
            $data = $data['response'];
            State::save($state['user_id'], $state['side'], $state['playlistid'], $state['search'], $serial, $state['currentpage'], $state['repeat'], $state['shuffle'], $state['sort']);
            //todo проверка на пустой ответ
            return json_encode(array('filename' => $filename, 'artist' => $data[0]['artist'], 'trak' => $data[0]['title']));
        } else {
            return false;
        }
    } else {
        return false;
    }
});




$app->get('/getrandomtrack/', function () use ($app) {
    $state = State::load();
    $serial = mt_rand(1,State::maxtrack($state));
    $filename = State::GetSongbySerial($serial);
    if (!empty($filename)) {
        $trackid = substr($filename, 0, -4);
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
        $data = json_decode($resp, true);
        $data = $data['response'];
        State::save($state['user_id'], $state['side'], $state['playlistid'], $state['search'], $serial, $state['currentpage'], $state['repeat'], $state['shuffle'], $state['sort']);
        //todo проверка на пустой ответ
        return json_encode(array('filename' => $filename, 'artist' => $data[0]['artist'], 'trak' => $data[0]['title']));
    } else {
        return false;
    }
});































$app->get('/getsong/{trackid}', function (Request $request, $trackid) use ($app) {
    session_write_close();
    if (!file_exists($GLOBALS['conf']['upload_dir'] . $trackid . ".mp3")) {
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
        $data = json_decode($resp, true);
        $data = $data['response'];
        if (strlen($data[0]['artist'] . "-" . $data[0]['title'] . ".mp3") >= 255 ) {
            $data[0]['artist'] = substr($data[0]['artist'], 0 ,128);
            $data[0]['title'] = substr($data[0]['title'], 0 ,120);
        }
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
            $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios=' . $app->escape($trackid) . '&access_token=' . Core::taketoken());
            $data = json_decode($resp, true);
            $data = $data['response'];
            if (strlen($data[0]['artist'] . "-" . $data[0]['title'] . ".mp3") >= 255 ) {
                $data[0]['artist'] = substr($data[0]['artist'], 0 ,128);
                $data[0]['title'] = substr($data[0]['title'], 0 ,120);
            }
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