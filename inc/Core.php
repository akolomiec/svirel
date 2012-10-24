<?php

class Core
{
	public $token,$uid;


    public static function GetTop100 ($count = 100 , $offset = 0) {
        global $app;
        //$app['monolog']->addDebug(__FUNCTION__." Start GetTop100 " , array('count' => $count, 'offset'=>$offset));
        $resp = file_get_contents('https://api.vk.com/method/audio.get?&count='.$count.'&offset='.$offset.'&access_token='.Core::taketoken());
        //Log::write (__FUNCTION__."Запрос audio.get=".var_export($resp,true),__LINE__);
        $data = json_decode($resp, true);
        if ( empty( $data['response'] ) ) {
            return false;
        }
        if (key_exists('error', $data)) {
            //$app['monolog']->addDebug(__FUNCTION__."Получена ответ от vk.com" , array('Error_code' => $data["error"]["error_code"], 'Err_msg' => $data["error"]["error_msg"]));
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            //Log::write (__FUNCTION__."Обработанные \$data=".var_export($data,true),__LINE__);
            $app['monolog']->addDebug(" data " , array('data' => $data));
            return $data;
        }
        $app['monolog']->addDebug(__FUNCTION__." data " , array('data' => $data));
//        print_r($data); // расскоментировать эту строчку, чтобы увидеть ответ сервера
    }
    public static function Search ($req = '' , $count = 100 , $offset = 0) {
        global $app;
        $resp = file_get_contents('https://api.vk.com/method/audio.search?q='.urlencode($req).'&auto_complete=1&count='.$count.'&offset='.$offset.'&access_token='.self::taketoken());
        $data = json_decode($resp, true);
        if (key_exists('error', $data)) {
            //$app['monolog']->addDebug(__FUNCTION__."Получена ответ от vk.com" , array('Error_code' => $data["error"]["error_code"], 'Err_msg' => $data["error"]["error_msg"]));
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            //$app['monolog']->addDebug(__FUNCTION__."Получена ответ от vk.com" , array('data' => $data));
            return $data;
        }
        //print_r($data); // расскоментировать эту строчку, чтобы увидеть ответ сервера
    }

    public static function taketoken() {
        global $app;
        $uagent = $GLOBALS['conf']['uagent'];
        $cookies = $GLOBALS['conf']['cookies'];
        $email = $GLOBALS['conf']['email'];
        $pass = urlencode($GLOBALS['conf']['password']);
        $appId = $GLOBALS['conf']['AppId'];
        $dir = APPLICATION_PATH."/log";
        //$app['monolog']->addDebug(__FUNCTION__.' Попытка получения токена из файла.');
        /*$app['monolog']->addDebug(__FUNCTION__.' Входные данные:',
            array(
                'uagent' => $uagent,
                'cookies' => $cookies,
                'email' => $email,
                'pass' => $pass,
                'appId' => $appId,
                'cookiedir' => $dir
            )
        );
        */
        $token = Core::Gettoken();
        //$app['monolog']->addDebug(__FUNCTION__.' Получили токен из файла', array('token' => $token));
        if ( $token ) {
            //$app['monolog']->addInfo(__FUNCTION__.' Отдали это значение токена', array('token' => $token));
            return $token;
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Не удалось получить токен из файла', array('token' => $token));
            //$app['monolog']->addInfo(__FUNCTION__.' Готовим curl запрос');
            $ch = curl_init();
            $url = 'https://login.vk.com/?act=login&soft=1';
            $postfields = "_origin=http://oauth.vk.com&expire=0&ip_h=cee39e67a788711224&to=aHR0cDovL29hdXRoLnZrLmNvbS9vYXV0aC9hdXRob3JpemU/Y2xpZW50X2lkPTI5NDA2NzImcmVkaXJlY3RfdXJpPWh0dHAlM0ElMkYlMkZvYXV0aC52ay5jb20lMkZibGFuay5odG1sJnJlc3BvbnNlX3R5cGU9dG9rZW4mc2NvcGU9OCZzdGF0ZT0mZGlzcGxheT1wYWdl&email={$email}&pass={$pass}";
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_URL, $url);
            //$app['monolog']->addInfo(__FUNCTION__.' Готовим curl запрос', array('url' => $url, 'postfields' => $postfields));
            $body = Core::curl_redirect_exec($ch);
            //$app['monolog']->addDebug(__FUNCTION__.' Получен результат всех переходов ', array('body' => $body));
            curl_close($ch);
            //$app['monolog']->addInfo(__FUNCTION__.' Следующий запрос ');
            $ch2 = curl_init();
            curl_setopt($ch2, CURLOPT_HEADER, 1);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch2, CURLOPT_NOBODY, 0);
            //            curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookies);
            curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookies);
            $blank = "http://oauth.vk.com/blank.html";
            $url = 'http://oauth.vk.com/authorize?client_id='.$appId.'&scope=audio&redirect_uri='.$blank.'&display=page&response_type=token';
            curl_setopt($ch2, CURLOPT_URL, $url);
            //$app['monolog']->addInfo(__FUNCTION__.' Должны были залогиниться теперь авторизуемся ', array('url' => $url));
            $responce = Core::curl_redirect_exec($ch2);
            //$app['monolog']->addInfo(__FUNCTION__.' Должны были авторизоваться, топаем подтверждать права ', array('responce' => $responce));
            curl_close($ch2);
            if ( preg_match_all("/access_token=(.*)&expires_in=86400/i", $responce, $res) ) {
                //$app['monolog']->addInfo(__FUNCTION__.' Токен найден в ответе!!! ', array('res' => $res));
                // everything is going fine
                $token = $res[1][0];
                Core::savetoken($token);
            } elseif ( preg_match_all("/approve\(\).*?\n.*?location.href = \"(.*?)\";/", $responce, $res) ) {
                //$app['monolog']->addInfo(__FUNCTION__.' Подтвердить надо что я согласен использовать мою музыку и прочее ', array('res' => $res));
                // accept application permissions
                $href = $res[1][0];
                //$app['monolog']->addDebug(__FUNCTION__.' Получили адрес подтверждения ', array('href' => $href));
                //$app['monolog']->addInfo(__FUNCTION__.' Следующий запрос для подтверждения');
                $ch3 = curl_init();
                curl_setopt($ch3, CURLOPT_HEADER, 1);
                curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch3, CURLOPT_NOBODY, 0);
                //                curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch3, CURLOPT_COOKIEFILE, $cookies);
                curl_setopt($ch3, CURLOPT_COOKIEJAR, $cookies);
                curl_setopt($ch3, CURLOPT_URL, $href);
                $responce = Core::curl_redirect_exec($ch3);
                //$app['monolog']->addInfo(__FUNCTION__.' Завершили переход для подтверждения', array('responce' => $responce));
                curl_close($ch3);
                $token = Core::taketoken();
                //$app['monolog']->addInfo(__FUNCTION__.' Токен равен ', array('token' => $token));
            }
        }
    }
    public static function savetoken ($token) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Сохраняем токен в файл ', array('token' => $token));
	    $dir = APPLICATION_PATH."/log";
            $filename = $dir . DS . "token";
            $fh = fopen($filename, 'w') or die("Создать файл токена не удалось");
            $result = !fwrite($fh, $token) ? "Panic token not writable" : "";
            if ($result <> "") {
                echo $result;
            }
            fclose($fh);	


    }
    public static function deltoken () {
        $dir = APPLICATION_PATH."/log";
        $filename = $dir . DS . "token";
        if (file_exists($filename)){
            unlink ($filename) or die("Удалить файл token не удалось");
        }
    }
    public static function saveuid ($uid) {
	    $dir = APPLICATION_PATH."/log";
            $filename = $dir . DS ."userid";
            $fh = fopen($filename, 'w') or die("Создать файл лога не удалось");
            $result = !fwrite($fh, $uid) ? "Panic token not writable" : "";
            if ($result <> "") {
                echo $result;
            }
            fclose($fh);


    }

    public static function Gettoken () {
        global $app;
	    $dir = APPLICATION_PATH."/log";
        $filename = $dir.DS."token";
        /*$app['monolog']->addDebug(__FUNCTION__.' Чтение токена из файла', array('filename' => $filename));
        $app['monolog']->addDebug(__FUNCTION__.' Файл существует и моложе 7200с', array('fileexist' => file_exists($filename), 'create_time' => (time() - filectime($filename))));
        */
        if (file_exists($filename) && ((time() - filectime($filename)) < 7200)) {
            $fh = fopen( $filename, "r") or die("Чтение токена не выполнено");
            $result = fread ($fh, filesize($filename));
            //$app['monolog']->addDebug(__FUNCTION__.' Результат чтения', array('result' => $result));
            if ($result <> "") {

                return $result;
            }
            fclose($fh);
        } else {
            return false;
        }
    }
    public static function Getuid () {
	    $dir = APPLICATION_PATH."/log";
        $filename = $dir . DS . "userid";
        $fh = fopen($filename, "r") or die("Чтение юида не выполнено");
        $result = fread ($fh, filesize($filename));
        if ($result <> "") {
            return $result;
        }
        fclose($fh);
    }

    public static function curl_redirect_exec($ch, &$redirects = 0, $curloptHeader = false) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Выполняем CURL ');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        //$app['monolog']->addInfo(__FUNCTION__.' Выполнили CURL ', array('data' => $data));
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //$app['monolog']->addInfo(__FUNCTION__.' HTTP Code ', array('httpcode' => $httpCode));
        if ( in_array($httpCode, array(301, 302)) ) {
            //$app['monolog']->addInfo(__FUNCTION__.' Получен код переадресации 301 или 302 ');
            list($header) = explode("\r\n\r\n", $data, 2);
            $matches = array();

            //this part has been changes from the original
            preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
            //$app['monolog']->addInfo(__FUNCTION__.' Проверяем заголовки на совпадение с Location: ', array('matches' => $matches));
            $url = trim( str_replace($matches[1], "", $matches[0]) );
            //$app['monolog']->addInfo(__FUNCTION__.' Получили адрес', array('url' => $url));
            if ( preg_match_all('/access_token=(.*)&expires_in=86400/i', $url, $matches) ) {
                $app['monolog']->addInfo(__FUNCTION__.' Получили токен!!! ', array('matches' => $matches));
                Core::savetoken($matches[1][0]);
                return true;
            } elseif ( preg_match_all('/id(.*)/i', $url, $matches) ) {
                //$app['monolog']->addInfo(__FUNCTION__.' Успешно залогинились ', array('matches' => $matches));


            }
            //end changes

            $urlParsed = parse_url($url);
            //$app['monolog']->addInfo(__FUNCTION__.' Парсим url ', array('parsedurl' => $urlParsed));
            if ( isset($urlParsed) ) {
                curl_setopt($ch, CURLOPT_URL, $url);
                $redirects++;
                //$app['monolog']->addInfo(__FUNCTION__.' Пошли на редирект ', array('url' => $url, 'redirects' => $redirects));
                return Core::curl_redirect_exec($ch, $redirects);
            }
        }

        if ( $curloptHeader ) {
            //$app['monolog']->addInfo(__FUNCTION__.' Установлен CURLOPTHEADER ', array('header' => $curloptHeader));
            //$app['monolog']->addInfo(__FUNCTION__.' Возвращаем data ', array('data' => $data));
            return $data;
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Не установлено значение CURLOPTHEADER ', array('header' => $curloptHeader));
            $ttt = explode("\r\n\r\n", $data, 2);

            //$app['monolog']->addInfo(__FUNCTION__.' Вернем ttt[1] или null ', array('ttt' => $ttt));
            return isset($ttt[1]) ? $ttt[1] : null;
        }
    }

    public static function remotefilesize($url) {
            ob_start();
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            $ok = curl_exec($ch);
            curl_close($ch);
            $head = ob_get_contents();
            ob_end_clean();
            $regex = '/Content-Length:\s([0-9].+?)\s/';
            preg_match($regex, $head, $matches);
		    Log::write ('remotesize = '.$matches[1],__LINE__);
            return isset($matches[1]) ? $matches[1] : "unknown";
    }
    public static function getfilevk ($trackid, $url, $size) {
       $filename = $GLOBALS['conf']['upload_dir'].$trackid.".mp3";
	$result = false;
	$i = 0;
	while (!$result){
	    $i++;
	    $result = file_get_contents($url);
	    if ($i > 3) {
	    break;
	    }
        }
       if ($result) {
           Log::write ("Downloaded file {$url}",__LINE__);
           $fm = fopen ($filename, "wb+");
           if (!fwrite($fm, $result)) {
               Log::write ("Файл в кеш записать не удалось {$filename}",__LINE__);
               return false;
           } else {
               return true;
           }
       } else {
           Log::write ("Файл пустой {$filename}",__LINE__);
           return false;
       }
   }
    
    public static function sec2time ($time) {
        $hours = floor($time/3600);
        $min = floor($time/60);
        $sec = $time - $min * 60 - $hours *3600;
        if ($sec < 10) {
            $sec = "0".$sec;
        };
        $value = $hours ? $hours.":".$min.":".$sec : $min.":".$sec;
        Log::write ("Перевод секунд в нормальный вид {$value}",__LINE__);
        return $value;

   }
//todo выдача нормального имени файла
    public static function GetFileName($trackid) {
        Log::write ("Downloaded file {$trackid}",__LINE__);
        $db = new DB;
        $sql = "SELECT `track_artist`, `track_title` FROM `playlist` WHERE `track_id` = '{$trackid}';";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $res = $db->dbquery($sql);
        Log::write(__FUNCTION__.": Результат: ".var_export($res,true),__LINE__);
        if ($res) {
            return $res[0]['track_artist']."-".$res[0]['track_title'].".mp3";
        } else {
            return false;
        }

    }

}
