<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app->get('/top100/{page}', function ($page) use ($app) {
    $side = 'left';
    $response =  Core::GetTop100 ($GLOBALS['conf']['trackperpage'], ($page-1)*$GLOBALS['conf']['trackperpage']);
    $reslt = $response['response'];
    $i = 0+($page-1)*$GLOBALS['conf']['trackperpage'];
    foreach ($reslt as $key => $value) {
        $i++;
        $time = $value["duration"];
        $value["duration"] = Core::sec2time($time);
        $value["serial"] = $i;
        $top100[] = $value;
    }
    $serial = State::Getserial($side, 'top100');
    return $app['twig']->render('list.twig', array(
        'list' => $top100,
        'trackselect' => $serial
    ));
})->bind('gettop');

$app->get('/search/{page}', function ($page) use ($app) {
    $side = 'left';
    $playlistid = 'search';
    $req = $app['session']->get('req');
    if ($req) {
        $req = $app->escape($req);
        if ($list = Core::Search($req, $GLOBALS['conf']['trackperpage'], ($page-1)*$GLOBALS['conf']['trackperpage'])) {
            $reslt = $list['response'];
            $list = null;
    		$i = ($page-1)*$GLOBALS['conf']['trackperpage'];
            foreach ($reslt as $key => $value) {
                if (!is_int($value)){
				    $i++;
                    $time = $value["duration"];
                    $value["duration"] = Core::sec2time($time);
					$value["serial"] = $i;
                    $list[] = $value;
                }
            }
            $app['session']->set('req', $req);
            $serial = State::Getserial($side, $playlistid);
            return $app['twig']->render('list.twig', array(
                'list' => $list,
                'trackselect' => $serial
            ));
        }
    } else {
        $app['session']->set('req', '');
    }
})->bind('search');