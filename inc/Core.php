<?php

class Core
{
	public $token,$uid;


    public static function GetTop100 ($count = 100 , $offset = 0) {
        global $app;
        $resp = file_get_contents('https://api.vk.com/method/audio.get?&count='.$count.'&offset='.$offset.'&access_token='.Core::taketoken());
        $data = json_decode($resp, true);
        if ( empty( $data['response'] ) ) {
            return false;
        }
        if (key_exists('error', $data)) {
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            return $data;
        }
    }
    public static function Search ($req = '' , $count = 100 , $offset = 0) {
        global $app;
        $resp = file_get_contents('https://api.vk.com/method/audio.search?q='.urlencode($req).'&auto_complete=1&count='.$count.'&offset='.$offset.'&access_token='.self::taketoken());
        $data = json_decode($resp, true);
        if (key_exists('error', $data)) {
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            return $data;
        }
    }

    public static function taketoken() {
        global $app;
        $uagent = $GLOBALS['conf']['uagent'];
        $cookies = $GLOBALS['conf']['cookies'];
        $email = $GLOBALS['conf']['email'];
        $pass = urlencode($GLOBALS['conf']['password']);
        $appId = $GLOBALS['conf']['AppId'];
        $dir = APPLICATION_PATH."/log";
        $token = Core::Gettoken();
        if ( $token ) {
            return $token;
        } else {
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
            $body = Core::curl_redirect_exec($ch);
            curl_close($ch);
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
            $responce = Core::curl_redirect_exec($ch2);
            curl_close($ch2);
            if ( preg_match_all("/access_token=(.*)&expires_in=86400/i", $responce, $res) ) {
                $token = $res[1][0];
                Core::savetoken($token);
            } elseif ( preg_match_all("/approve\(\).*?\n.*?location.href = \"(.*?)\";/", $responce, $res) ) {
                // accept application permissions
                $href = $res[1][0];
                $ch3 = curl_init();
                curl_setopt($ch3, CURLOPT_HEADER, 1);
                curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch3, CURLOPT_NOBODY, 0);
                //                curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch3, CURLOPT_COOKIEFILE, $cookies);
                curl_setopt($ch3, CURLOPT_COOKIEJAR, $cookies);
                curl_setopt($ch3, CURLOPT_URL, $href);
                $responce = Core::curl_redirect_exec($ch3);
                curl_close($ch3);
                $token = Core::taketoken();
            }
        }
    }
    public static function savetoken ($token) {
        global $app;
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
        if (file_exists($filename) && ((time() - filectime($filename)) < 7200)) {
            $fh = fopen( $filename, "r") or die("Чтение токена не выполнено");
            $result = fread ($fh, filesize($filename));
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
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ( in_array($httpCode, array(301, 302)) ) {
            list($header) = explode("\r\n\r\n", $data, 2);
            $matches = array();
            preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
            $url = trim( str_replace($matches[1], "", $matches[0]) );
            if ( preg_match_all('/access_token=(.*)&expires_in=86400/i', $url, $matches) ) {
                Core::savetoken($matches[1][0]);
                return true;
            } elseif ( preg_match_all('/id(.*)/i', $url, $matches) ) {
            }
            $urlParsed = parse_url($url);
            if ( isset($urlParsed) ) {
                curl_setopt($ch, CURLOPT_URL, $url);
                $redirects++;
                return Core::curl_redirect_exec($ch, $redirects);
            }
        }
        if ( $curloptHeader ) {
            return $data;
        } else {
            $ttt = explode("\r\n\r\n", $data, 2);
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
            $fm = fopen ($filename, "wb+");
            if (!fwrite($fm, $result)) {
                return false;
            } else {
                return true;
            }
        } else {
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
        return $value;

   }
//todo выдача нормального имени файла
    public static function GetFileName($trackid) {
        $db = new DB;
        $sql = "SELECT `track_artist`, `track_title` FROM `playlist` WHERE `track_id` = '{$trackid}';";
        $res = $db->dbquery($sql);
        if ($res) {
            if (strlen($res[0]['track_artist'] . "-" . $res[0]['track_title'] . ".mp3") >= 255 ) {
                $res[0]['track_artist'] = substr($res[0]['track_artist'], 0 ,128);
                $res[0]['track_title'] = substr($res[0]['track_title'], 0 ,120);
            }
            return $res[0]['track_artist']."-".$res[0]['track_title'].".mp3";
        } else {
            return false;
        }

    }

}
