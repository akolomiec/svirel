<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 15.05.12
 * Time: 10:09
 * Получаем access_token для вконтакте.
 *
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/vkauth', function (Request $request) use ($app) {
    $code = $request->get('code');
    Log::write ("\$code = ".$code,__LINE__);
    if($code) {
	$req = 'https://api.vk.com/oauth/access_token?client_id='.$GLOBALS['conf']['AppId'].'&code='.$code.'&client_secret='.$GLOBALS['conf']['AppSecret'];
	    Log::write ("\$req = ".$req,__LINE__);
        // получаем access_token
        $resp = file_get_contents($req);
        $data = json_decode($resp, true);
	Log::write ("access_token = {$data['access_token']}",__LINE__);
        if($data['access_token']){
            // запишем данные в сессию
//            $_SESSION['access_token'] = $data['access_token'];
		//print_r($data);
		Core::savetoken($data['access_token']);
//            $_SESSION['user_id'] = $data['user_id'];
		Core::saveuid($data['user_id']);
            // переадресуем пользователя на нужную страницу
            //todo убрать нах все лишнее
            return $app->redirect('/');
        }
    }
})
    ->bind('vkauth');



