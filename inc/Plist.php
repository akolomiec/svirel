<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 28.06.12
 * Time: 11:14
 * To change this template use File | Settings | File Templates.
 */
class Plist
{
    public static function GetmyPlaylist($userid, $offset) {
        Log::write(__FUNCTION__.": Ищем плейлист по умолчанию для пользователя {$userid}",__LINE__);
        $playlistid = Plist::GetmyPlaylistId($userid);
        return Plist::GetPlaylist($playlistid, $offset);
    }

    public static function GetPlName($plid) {

        global $app;
        $db = new DB;
        $sql = "SELECT * FROM `user_playlist` WHERE `id` = '{$plid}';";
        //$app['monolog']->addInfo(' SQL запрос', array('sql' => $sql));
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
                $list[] = array('id'=>$value['id'],'name'=>$value['plname']);
            }
            //$app['monolog']->addInfo(__FUNCTION__.' Получили список', $list);
            return $list[0]['name'];
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Видимо список пуст ', array('return' => false));
            return false;
        }


    }
    public static function GetArrayPlaylist($user_id) {
        global $app;
        $list = array();
        //$app['monolog']->addInfo(' Пробуем получить список плейлистов пользователя', array('user_id' => $user_id));
        $db = new DB;
        $sql = "SELECT * FROM `user_playlist` WHERE `user_id` = '{$user_id}';";
        //$app['monolog']->addInfo(' SQL запрос ', array('sql' => $sql));
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
                $list[] = array('id'=>$value['id'],'name'=>$value['plname']);
            }
            //$app['monolog']->addInfo(__FUNCTION__.' Получили список', $list);
            return $list;
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Видимо список пуст ', array('return' => false));
            return false;
        }

    }
    public static function trackCount ($playlistid) {
        global $app;
        //$app['monolog']->addInfo('Попытка выполнить trackCount ', array('playlistid' => $playlistid));
        $db = new DB;
        $sql = "SELECT COUNT(*) FROM `playlist` WHERE `playlist_id` = '{$playlistid}';";
        //$app['monolog']->addInfo(' SQL запрос ', array('sql' => $sql));
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            //$app['monolog']->addInfo('В результате получен массив ', array('res' => $res));
            return $res[0];
        } else {
            //$app['monolog']->addInfo('Видимо ничего нет в базе');
            return false;
        }
    }

    public static function GetmyPlaylistId($userid) {
        Log::write(__FUNCTION__.": Ищем ID плейлиста по умолчанию для пользователя {$userid}",__LINE__);
        $db = new DB;
        $sql = "SELECT `defpl` FROM `user` WHERE `id` = '{$userid}';";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $res = $db->dbquery($sql);
        Log::write(__FUNCTION__.": Результат: ".var_export($res,true),__LINE__);
        if (is_array($res)) {
            Log::write(__FUNCTION__.": Результат: ".var_export($res,true),__LINE__);
            return $res[0]['defpl'];
        } else {
            Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }
    }
    //todo пробовать оптимизировать запросы в vk.com
    public static function Addtrack($playlistid, $trackid) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.'Добавляем песню в плейлист', array());
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios='.$trackid.'&access_token='.Core::taketoken());
        //$app['monolog']->addInfo(__FUNCTION__.' В vk.com', array('resp' => $resp));
        $data = json_decode($resp, true);
        if ( empty( $data['response'] ) ) {
            //$app['monolog']->addInfo(__FUNCTION__.' Пришел пустой ответ', array('empty_resp' => empty( $data['response'] )));
            return false;
        }
        if (key_exists('error',$data)) {
            //$app['monolog']->addDebug(__FUNCTION__.' Получена ответ от vk.com Error_code ', array('error_code' => $data["error"]["error_code"], 'error_msg' => $data["error"]["error_msg"]));
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            $list = $data['response'];
            //$app['monolog']->addDebug(__FUNCTION__.' Обработанные', array('data' => $data));
            $db = new DB;
            $serial = Plist::GetLastSerial($playlistid)+1;
            $sql = "INSERT INTO `playlist` (`playlist_id`, `track_id`, `serial`, `track_artist`, `track_title`, `track_duration`) VALUES ( '{$playlistid}', '{$trackid}', '{$serial}', '{$list[0]['artist']}', '{$list[0]['title']}', '{$list[0]['duration']}' );";
            //$app['monolog']->addDebug(__FUNCTION__.' SQL запрос', array('sql' => $sql));
            $db->dbquery($sql);
            $rows = mysql_affected_rows($db->db);
            $playlist_id = mysql_insert_id($db->db);
            //$app['monolog']->addDebug(__FUNCTION__.' Затронуто', array('rows' => $rows, 'playlistid' => $playlistid));
            return true;
        }
    }

    public static function GetLastSerial($playlistid) {
        global $app;

        $db = new DB;
        $sql = "SELECT serial FROM `playlist` WHERE playlist_id = '{$playlistid}' ORDER BY serial DESC";
        //$app['monolog']->addDebug(__FUNCTION__.' SQL запрос', array('sql' => $sql));
        $result = $db->dbquery($sql);
        //$app['monolog']->addDebug(__FUNCTION__.' Результат', array('result' => $result, 'res' => $result[0]['serial']));

        return $result[0]['serial'];
    }

    public static function Deltrack($playlistid, $serial) {
        Log::write (__FUNCTION__." Удаляем трек",__LINE__);
        $db = new DB;
        $sql = "DELETE FROM `playlist` WHERE playlist_id = '{$playlistid}' AND serial = '{$serial}';";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $db->dbquery($sql);
        $rows = mysql_affected_rows($db->db);
        $playlist_id = mysql_insert_id($db->db);
        Log::write(__FUNCTION__.": Было затронуто строк: {$rows} playlist_id={$playlist_id}",__LINE__);


        if ($rows) {
            $sql = "UPDATE `playlist` SET `serial`=`serial`-1 WHERE `serial` > {$serial};";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $db->dbquery($sql);
        $result = mysql_affected_rows($db->db);
        Log::write(__FUNCTION__.": Было затронуто строк: {$result}",__LINE__);
            return true;
        } else {
            return false;
        }
    }
        //print_r($data); // расскоментировать эту строчку, чтобы увидеть ответ сервера

    public static function DelPlaylist($playlistid) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Удаляем плейлист', array('id' => $playlistid));
        //todo проверить чтобы плейлист не был по умолчанию у пользователя...
        $db = new DB;
        $sql = "SELECT * FROM `user` WHERE `defpl` = '{$playlistid}';";
        $res = $db->dbquery($sql);
        if (!is_array($res)) {
            //$app['monolog']->addInfo(__FUNCTION__.' Проверили чтоб не был по умолчанию', array('id' => $playlistid));
            $sql = "DELETE FROM `playlist` WHERE `playlist_id` = '{$playlistid}';";
            $res = $db->dbquery($sql);
            if ($res > 0) {
                //$app['monolog']->addInfo(__FUNCTION__.' Успешно удалили все песни', array('id' => $playlistid));

            }
            $sql = "DELETE FROM `user_playlist` WHERE `id` = '{$playlistid}';";
            $res = $db->dbquery($sql);
            if ($res > 0) {
                //$app['monolog']->addInfo(__FUNCTION__.' Успешно удалили плейлист', array('id' => $playlistid));
                return true;
            } else {
                return false;
            }


        } else {
            return false;
        }
    }



//todo сформировать ответ как у контакта, с aid ownerid и т.д.
    public static function GetPlaylist($playlistid, $offset = 0){
        //Log::write(__FUNCTION__.": Ищем плейлист по id={$playlistid}",__LINE__);
        $trackperpage = $GLOBALS['conf']['trackperpage'];
        $db = new DB;
        $sql = "SELECT * FROM `playlist` WHERE `playlist_id` = '{$playlistid}' ORDER BY `serial` LIMIT {$offset}, {$trackperpage};";
        //Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $res = $db->dbquery($sql);
        if (is_array($res)) {

            foreach ($res as $key => $value) {
                $track = explode("_", $value['track_id']);
                // track id = $track[0]_$track[1]
                $list[] = array('aid'=>$track[1],'owner_id'=>$track[0], 'serial' => $value['serial'], 'artist' => $value['track_artist'], 'title'=> $value['track_title'], 'duration' => Core::sec2time($value['track_duration']));
            }
            //Log::write(__FUNCTION__.": Результат: ".var_export($list,true),__LINE__);
            return $list;
        } else {
            //Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }

    }

    public static function CreatePlaylist ($id, $defpl = false, $name = "Мой плейлист" ) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Создаем плейлист', array('id' => $id, 'defpl' => $defpl, 'name'=> $name));
        $db = new DB;
        $sql = "INSERT INTO `user_playlist` (`user_id`, `plname`) VALUES ( {$id}, \"{$name}\" );";
        //$app['monolog']->addInfo(__FUNCTION__.' SQL запрос', array('sql' => $sql));
        $db->dbquery($sql);
        $rows = mysql_affected_rows($db->db);
        $playlist_id = mysql_insert_id($db->db);
        //$app['monolog']->addInfo(__FUNCTION__.' Запрос выполнен', array('rows' => $rows, 'playlist_id' => $playlist_id));
        if ($rows) {
            if ($defpl) {
                //$app['monolog']->addInfo(__FUNCTION__.' Будем указывать плейлист по умолчанию', array('defpl' => $playlist_id));
                $sql = "UPDATE `user` SET defpl = {$playlist_id} WHERE id = {$id};";
                //$app['monolog']->addInfo(__FUNCTION__.' SQL запрос', array('sql' => $sql));
                $db->dbquery($sql);
                $rows = mysql_affected_rows($db->db);
                //$app['monolog']->addInfo(__FUNCTION__.' Запрос выполнен', array('rows' => $rows));
                if ($rows) {
                    //$app['monolog']->addInfo(__FUNCTION__.' Плейлист по умолчанию внесен в профиль');
                } else {
                    //$app['monolog']->addError(__FUNCTION__.' Плейлист по умолчанию не внесен в профиль');
                }

            }
            return $playlist_id;
        } else {
            //$app['monolog']->addError(__FUNCTION__.' Не удалось создать плейлист');
            return false;
        }



    }
}
