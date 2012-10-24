<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 29.06.12
 * Time: 12:16
 * To change this template use File | Settings | File Templates.
 */

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app->post('/newplaylist/', function ( Request $request ) use ($app){
    $playlistname = $app->escape($request->get('playlistname'));
    $userid = CUser::GetUserId();
    $app['monolog']->addInfo('Добавляем плейлист', array('playlistname' => $playlistname));
    if ($plid = Plist::CreatePlaylist($userid, false, $playlistname)){
        $app['monolog']->addInfo('Плейлист успешно добавлен', array('playlistname' => $playlistname, 'plid' => $plid));
        return array('code' => 0, 'msg'=> "Все прошло нормально, плейлист создан");
    } else {
        $app['monolog']->addError('Плейлист НЕ добавлен', array('playlistname' => $playlistname));
        return array('code' => 1, 'msg'=> "Беда, плейлист НЕ создан");
    }
});

$app->post('/delplaylist/', function ( Request $request ) use ($app){
    $playlistid = $app->escape($request->get('playlistid'));

    $app['monolog']->addInfo('Удаляем плейлист', array('playlistname' => $playlistid));
    if (Plist::DelPlaylist($playlistid)){
        $app['monolog']->addInfo('Плейлист успешно удален', array('playlistid' => $playlistid));
        return json_encode(array('code' => 0, 'msg'=> "Все прошло нормально, плейлист удален"));
    } else {
        $app['monolog']->addError('Плейлист НЕ удален', array('playlistid' => $playlistid));
        return json_encode(array('code' => 1, 'msg'=> "Беда, плейлист невозможно удалить"));
    }
});




$app->post('/addinplaylist/', function ( Request $request ) use ($app){
    $side = 'right';
    $trackid = $request->get('trackid');
    $playlistid = $request->get('playlistid');
    $app['monolog']->addInfo('Добавляем песню в плейлист', array('trackid' => $trackid, 'playlistid' => $playlistid));
    if (Plist::Addtrack($playlistid, $trackid)) {
        $list = Plist::GetPlaylist($playlistid);
        Log::write (__FILE__." Плейлист ". var_export($list,true),__LINE__);
        $tracks = Plist::trackCount($playlistid);
        $totalpages = ceil($tracks['COUNT(*)'] / $GLOBALS['conf']['trackperpage']);
        $serial = State::Getserial($side, $playlistid);
        $playlistheader = Plist::GetPlName($playlistid);
        return $app['twig']->render('playlist.twig', array(
            'playlistheader' => $playlistheader,
            'playlistid' => $playlistid,
            'list' => $list,
            'totalpages' => $totalpages,
            'page' => 1,
            'web_root' => $GLOBALS['conf']['web_root'],
            'url' => 'getcenter/'.$playlistid,
            'trackselect' => $serial
        ));
    };
});


$app->post('/delfromplaylist/', function ( Request $request ) use ($app){
    Log::write (__FILE__." Удаляем песню из плейлиста",__LINE__);
    $side = 'right';
    $serial = $request->get('serial');
    $playlistid = $request->get('playlistid');
    Log::write (__FILE__." Удаляем песню npp={$serial} из плейлиста id={$playlistid}",__LINE__);
    if (Plist::Deltrack($playlistid, $serial)) {
        $list = Plist::GetPlaylist($playlistid);
        Log::write (__FILE__." Список песен:". var_export($list,true),__LINE__);
        $tracks = Plist::trackCount($playlistid);
        $totalpages = ceil($tracks['COUNT(*)'] / $GLOBALS['conf']['trackperpage']);
        $playlistheader = Plist::GetPlName($playlistid);
        $serial_track = State::Getserial($side, $playlistid);
        return $app['twig']->render('playlist.twig', array(
            'playlistheader' => $playlistheader,
            'playlistid' => $playlistid,
            'list' => $list,
            'totalpages' => $totalpages,
            'page' => 1,
            'web_root' => $GLOBALS['conf']['web_root'],
            'url' => 'getcenter/'.$playlistid,
            'trackselect' => $serial_track
        ));
    };
});