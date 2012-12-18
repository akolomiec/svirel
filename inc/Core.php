<?php

class Core
{
	public $token,$uid;

        /*
         *
         $responce = Lastfm::request("chart.getTopTracks", array("page" => $page, "limit" => $limit));
        if (isset ($responce->tracks)) {
            $tracks = $responce->tracks->track;
        } else {
            return false;
        }
        $result = array();
        foreach ( $tracks as $track ) {
            $artist = $track->artist->name;
            $name = $track->name;
            $vtrack = self::Search("{$artist} - {$name}", 1, 0);
            $result['response'][] = $vtrack['response'][1];
        }
        return $result;
    }*/
    /*
     *
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

    */
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
            if (!empty($url)){
                $result = file_get_contents($url);
            } else {
                $result = 0;
            }
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
    public static function lastfm_gettoken() {
        /*
         *
         * svirel
         * API Key: 1fa4d30d602b85ec1579f0de593b381a
         * Secret: is 4e2b61191fed912fc64a8e713c552120
         *
         *
         * 1.
         * Get an API Key
         * If you don’t already have an API account, please apply for one. For each of your accounts you will have a
         * shared secret which you will require in Section 6. You will also need to set up a callback url which our
         * authentication service will redirect to in Section 4.
         * 2. Request authorization from the user
         * Send your user to last.fm/api/auth with your API key as a parameter. Use an HTTP GET request. Your request
         * will look like this:
         * http://www.last.fm/api/auth/?api_key=xxx
         * If the user is not logged in to Last.fm, they will be redirected to the login page before being asked to
         * grant your web application permission to use their account. On this page they will see the name of your
         * application, along with the application description and logo as supplied in Section 1.
         * 2.1 Custom callback url
         * You can optionally specify a callback URL that is different to your API Account callback url. Include this
         * as a query param cb. This allows you to have users forward to a specific part of your site after the
         * authorisation process.
         *
         * http://www.last.fm/api/auth/?api_key=xxx&cb=http://example.com
         * 3. Create an authentication handler
         * Once the user has granted permission to use their account on the Last.fm page, Last.fm will redirect to your
         * callback url, supplying an authentication token as a GET variable.
         *
         * <callback_url>/?token=xxxxxxx
         *
         * If the callback url already contains a query string then token variable will be appended, like;
         *
         * <callback_url>&token=xxxxxxx
         *
         * The script located at your callback url should pick up this authentication token and use it to create a
         * Last.fm web service session as described in Section 4.
         *
         * 3.1 Authentication Tokens
         * Authentication tokens are user and API account specific. They are valid for 60 minutes from the moment they
         * are granted.
         *
         * 4. Fetch a Web Service Session
         * Send your api key along with an api signature and your authentication token as arguments to the
         * auth.getSession API method call. The parameters for this call are defined as such:
         *
         * api_key: Your 32-character API Key.
         * token: The authentication token received at your callback url as a GET variable.
         * api_sig: Your 32-character API method signature, as explained in Section 6
         * Note: You can only use an authentication token once. It will be consumed when creating your web service
         * session.
         * The response format of this call is shown on the auth.getSession method page.
         *
         * 4.1 Session Lifetime
         * Session keys have an infinite lifetime by default. You are recommended to store the key securely. Users are
         * able to revoke privileges for your application on their Last.fm settings screen, rendering session keys
         * invalid.
         * 5. Make authenticated web service calls
         * You can now sign your web service calls with a method signature, provided along with the session key you
         * received in Section 4 and your API key. You will need to include all three as parameters in subsequent calls
         * in order to be able to access services that require authentication. You can visit individual method call
         * pages to find out if they require authentication. Your three authentication parameters are defined as:
         * sk (Required) : The session key returned by auth.getSession service.
         * api_key (Required) : Your 32-character API key.
         * api_sig (Required) : Your API method signature, constructed as explained in Section 6
         * 6. Sign your calls
         * Construct your api method signatures by first ordering all the parameters sent in your call alphabetically
         * by parameter name and concatenating them into one string using a <name><value> scheme. So for a call to
         * auth.getSession you may have:
         *
         * api_keyxxxxxxxxmethodauth.getSessiontokenxxxxxxx
         * Ensure your parameters are utf8 encoded. Now append your secret to this string. Finally, generate an md5
         * hash of the resulting string. For example, for an account with a secret equal to 'mysecret', your api
         * signature will be:
         * api signature = md5("api_keyxxxxxxxxmethodauth.getSessiontokenxxxxxxxmysecret")
         * Where md5() is an md5 hashing operation and its argument is the string to be hashed. The hashing operation
         * should return a 32-character hexadecimal md5 hash.
         * */
        // http://www.last.fm/api/auth/?api_key=xxx
    }


}
