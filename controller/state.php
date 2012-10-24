<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 17.10.12
 * Time: 11:53
 *  Сохраняем состояние плеера
 *
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$app->post('/state/save/', function (Request $request) use ($app) {
    $username = $request->get('username');
    $side = $request->get('side');
    $playlistid = $request->get('playlistid');
    $search = $request->get('search');
    $serial = $request->get('serial');
    $currentpage = $request->get('currentpage');
    $sort = $request->get('sort');
    //$app->escape($name)
    //todo Записать в базу всю эту ахинею...
    State::save($app->escape($username), $app->escape($side), $app->escape($playlistid), $app->escape($search), $app->escape($serial), $app->escape($currentpage), $app->escape($sort));



})->bind('state_save');



