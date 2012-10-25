<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 19.06.12
 * Time: 11:06
 * To change this template use File | Settings | File Templates.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post('/getregistration/', function (Request $request) use ($app) {
    $id = CUser::GetUserId();
    $app['monolog']->addDebug(__FUNCTION__.' Выбор блока регистрации ' , array('id' => $id));
    if (CUser::isGranted('enter_system')) {

        $login = CUser::GetUserbyId($id);
        return $app['twig']->render('user.twig', array(
            'username' => $login,
            'myplid' => Plist::GetmyPlaylistId($id),
            'list' => Plist::GetArrayPlaylist($id)
        ));
    } else {
        return $app['twig']->render('registration.twig', array());
    }
});


$app->get('/logout/', function (Request $request) use ($app) {
//todo cookie должны быть удалены с теми же параметрами что и сделаны, выставить время на час назад

    $id = CUser::GetUserId();
    //setcookie('svireluser', $id, time() - 3600, '/',$GLOBALS['conf']['cookie_domain']);
    //$_COOKIE['svireluser'] = "";
    CUser::first_enter($app);

    return $app['twig']->render('registration.twig', array());
});

//todo При регистрации не создается плейлист по умолчанию.
$app->post('/reg/', function (Request $request) use ($app) {
    $lastuid = CUser::GetUserId();
    $lastpl = Plist::GetmyPlaylistId($lastuid);
    $login = $app->escape($request->get('login'));
    $pass = $app->escape($request->get('pass'));
    if (CUser::checkLogin($login)) {
	CUser::DeleteUser($lastuid);
        if ($id = CUser::login($login,$pass)) {

            return new Response(json_encode(array('id' => $id)), 200);
        } else {
            return new Response(json_encode(array('code' => 1)), 200);
        }
    } else {
        if ($id = CUser::createUser($login, $pass, 1, 3, $lastpl)){
            CUser::DeleteUser($lastuid);
            if (CUser::login($login,$pass)){

                return new Response(json_encode(array('id' => $id)), 200);
            } else {
                return new Response(json_encode(array('code' => 2)), 200);
            }
        } else {
            return new Response(json_encode(array('code' => 3)), 200);
        }
    }
});

