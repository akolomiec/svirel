<?php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app->get('/top100/{page}', function ($page) use ($app) {
    $side = 'left';
    if(file_exists(APPLICATION_PATH.DS."/log/token")) {



        //	Log::write ("uid = {$uid}",__LINE__);
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
    } else {
        return $app->redirect('http://api.vk.com/oauth/authorize?client_id='.$GLOBALS['conf']['AppId'].'&redirect_uri='.$GLOBALS['conf']['web_root'].$GLOBALS['conf']['OAuthCallback'].'&display=popup&scope='.$GLOBALS['conf']['VKScope'].'&response_type=code');
    }

})
    ->bind('gettop');

$app->get('/search/{page}', function ($page) use ($app) {
    $side = 'left';
    $playlistid = 'search';
    Log::write (__FILE__."Листаем поисковые странички",__LINE__);
    $req = $app['session']->get('req');
    if(file_exists(APPLICATION_PATH.DS."/log/token")) {
        Log::write (__FILE__."Проверили наличие токена",__LINE__);
        if ($req) {
            Log::write (__FILE__."Получили запрос страницы".$req,__LINE__);
            $req = $app->escape($req);
            Log::write (__FILE__."Обработали q=".$req,__LINE__);


            if ($list = Core::Search($req, $GLOBALS['conf']['trackperpage'], ($page-1)*$GLOBALS['conf']['trackperpage'])) {
                Log::write (__FILE__."нашли композиции".$req,__LINE__);

                $reslt = $list['response'];
                Log::write (__FILE__."List=".var_export($reslt, true),__LINE__);
                $list = null;
				$i = ($page-1)*$GLOBALS['conf']['trackperpage'];
                foreach ($reslt as $key => $value) {
                    if (is_int($value)) {


                    }
                    else {
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
    } else {
        return $app->redirect('http://api.vk.com/oauth/authorize?client_id='.$GLOBALS['conf']['AppId'].'&redirect_uri='.$GLOBALS['conf']['web_root'].$GLOBALS['conf']['OAuthCallback'].'&display=popup&scope='.$GLOBALS['conf']['VKScope'].'&response_type=code');
    }
})->bind('search');