<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app->before(function (Request $request) use ($app){
    $app['session']->start();
    if (!CUser::isLogedin()) {
        CUser::first_enter();
    }
    $token = Core::taketoken();
});

$app->get('/', function () use ($app) {
    $app['monolog']->addInfo('Контроллер /');
    $app['session']->set('req', '');
    $title = $GLOBALS['conf']['main_title'];
    $state = State::load();
    return $app['twig']->render('index.twig', array(
        'title' => $title,
        'cookie_domain' => $GLOBALS['conf']['cookie_domain'],
        'repeat' => State::repeat(),
        'shuffle' => State::shuffle()



    ));
})->bind('homepage');

//todo Сделать нормальную проверку входных данных
$app->post('/getleft/', function (Request $request) use ($app) {
    $side = 'left';
    $cont = $request->get('cont');
    $req = $request->get('req') ? $request->get('req') : null;
    switch($cont) {
    case "s":
        if (!empty($req)) {
            $req = $app->escape($req);
            $uid = CUser::GetUserId();
            $header = "".$req;
            $page = 1;
            if ($list = Core::Search($req, $GLOBALS['conf']['trackperpage'],0)) {
                $reslt = $list['response'];
                $list = null;
				$i=0;
                foreach ($reslt as $key => $value) {
                    if (is_int($value)) {
                        $totalpages = ceil($value / $GLOBALS['conf']['trackperpage']);
                        if ($totalpages>50) {
                            $totalpages = 1000/ $GLOBALS['conf']['trackperpage'];
                        }
                    } else {
						$i++;
                        $time = $value["duration"];
                        $value["duration"] = Core::sec2time($time);
						$value["serial"] = $i;
                        $list[] = $value;
                    }
                }
                $reslt = null;
                $app['session']->set('req', $req);
                LastSearch::SetLastSearch($uid,$req);
                $category = 'search';
                $serial = State::Getserial($side, $category, $req);
                return $app['twig']->render('left.twig', array(
                    'header' => $header,
                    'page' => $page,
                    'list' => $list,
                    'web_root' => $GLOBALS['conf']['web_root'],
                    'url' => "search",
                    'totalpages' => $totalpages,
                    'trackselect' => $serial,
                    'category' => $category
                ));
            }
        } else { $app['session']->set('req', ''); }
        break;
    default:
        $header = "ТОП 100";
        $page = 1;
        $totalpages = 5;
        if ($list = Plist::GetPlaylist(1, 0)){
            $reslt = $list;
            $list = null;
            $i=0;
            foreach ($reslt as $key => $value) {
                if (is_int($value)) {
                    $totalpages = ceil($value / $GLOBALS['conf']['trackperpage']);
                    if ($totalpages>50) {
                        $totalpages = 1000/ $GLOBALS['conf']['trackperpage'];
                    }

                }
                else {
                    $i++;
                    $value["serial"] = $i;
                    $list[] = $value;
                }
            }
            $category = 'top100';
            $serial = State::Getserial($side, $category);
            return $app['twig']->render('left.twig', array(
                'header' => $header,
                'page' => $page,
                'list' => $list,
                'url' => "top100",
                'web_root' => $GLOBALS['conf']['web_root'],
                'totalpages' => $totalpages,
                'trackselect' => $serial,
                'category' => $category
            ));
        } else {
            die ("Не могу загрузить ни один плейлист");
        }
        break;
    }
})->bind('getleft');


//todo сделать нормальную проверку входных данных
$app->get('/getcenter/{playlistid}/{page}', function (Request $request, $playlistid, $page) use ($app) {
    $side = "right";
    $app['monolog']->addInfo('Контроллер /getcenter/ ', array('request' => $request->getRequestUri(), 'cookie' => $request->cookies, 'playlistid' => $playlistid, 'page' => $page));
    $userid = CUser::GetUserId();

    if ($playlistid == "default"){
        if ($page <> 0 ) {
            $list = Plist::GetmyPlaylist($userid, ($page-1)*$GLOBALS['conf']['trackperpage']);
            /*$app['monolog']->addInfo('Генерим плейлист по умолчанию.', array(
                'list' => $list,
                'page' => $page,
            ));*/
            $playlistid = Plist::GetmyPlaylistId($userid);
            $serial = State::Getserial($side, $playlistid);
            return $app['twig']->render('plist.twig', array(
                'list' => $list,
                'trackselect' => $serial
            ));
        } else {
            $page = 1;
            $list = Plist::GetmyPlaylist($userid, 0);
            $playlistid = Plist::GetmyPlaylistId($userid);
            $tracks = Plist::trackCount($playlistid);
            $serial = State::Getserial($side, $playlistid);
            $totalpages = ceil($tracks['COUNT(*)'] / $GLOBALS['conf']['trackperpage']);
            /*$app['monolog']->addInfo('Генерим плейлист по умолчанию.', array(
                'playlistheader' => 'Мой плейлист',
                'playlistid' => $playlistid,
                'list' => $list,
                'totalpages' => $totalpages,
                'page' => $page,
                'web_root' => $GLOBALS['conf']['web_root'],
                'url' => 'getcenter'
            ));*/
            return $app['twig']->render('playlist.twig', array(
                'playlistheader' => 'Мой плейлист',
                'playlistid' => $playlistid,
                'list' => $list,
                'totalpages' => $totalpages,
                'page' => $page,
                'web_root' => $GLOBALS['conf']['web_root'],
                'url' => 'getcenter/'.$playlistid,
                'trackselect' => $serial
            ));
        }
    } else {
        if ($page <> 0 ) {
            $list = Plist::GetPlaylist($playlistid, ($page-1)*$GLOBALS['conf']['trackperpage']);
            $serial = State::Getserial($side, $playlistid);
            /*$app['monolog']->addInfo('Генерим плейлист по умолчанию.', array(
                'list' => $list,
                'page' => $page,
            ));*/
            return $app['twig']->render('plist.twig', array(
                'list' => $list,
                'trackselect' => $serial
            ));
        } else {
            $page = 1;
            $list = Plist::GetPlaylist($playlistid);
            $tracks = Plist::trackCount($playlistid);
            $plname = Plist::GetPlName($playlistid);
            $totalpages = ceil($tracks['COUNT(*)'] / $GLOBALS['conf']['trackperpage']);
            $serial = State::Getserial($side, $playlistid);
            /*$app['monolog']->addInfo('Генерим плейлист по умолчанию.', array(
                'playlistheader' => $plname,
                'playlistid' => $playlistid,
                'list' => $list,
                'totalpages' => $totalpages,
                'page' => $page,
                'web_root' => $GLOBALS['conf']['web_root'],
                'url' => 'getcenter/'.$playlistid
            ));*/
            return $app['twig']->render('playlist.twig', array(
                'playlistheader' => $plname,
                'playlistid' => $playlistid,
                'list' => $list,
                'totalpages' => $totalpages,
                'page' => $page,
                'web_root' => $GLOBALS['conf']['web_root'],
                'url' => 'getcenter/'.$playlistid,
                'trackselect' => $serial
            ));
        }


    }


})
    ->bind('getcenter');

$app->GET('/showlastsearch/', function () use ($app) {
        $ls = LastSearch::GetLastSearch(CUser::GetUserId());
        return $app['twig']->render('lastsearch.twig', array(
            'list' => $ls
        ));
});