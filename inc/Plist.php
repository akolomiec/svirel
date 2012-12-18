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
        $playlistid = Plist::GetmyPlaylistId($userid);
        return Plist::GetPlaylist($playlistid, $offset);
    }
    public static function rename($playlistid, $name) {
        $db = new DB;
        if ($playlistid == Plist::GetmyPlaylistId(CUser::GetUserId())) {
            return 'myplaylist';
        } else {
            $sql = "UPDATE `user_playlist` SET `plname`='{$name}' WHERE `id` = '{$playlistid}';";
            $db->dbquery($sql);
            return 'updated';
        }
    }
    public static function GetPlName($plid) {

        $db = new DB;
        $sql = "SELECT * FROM `user_playlist` WHERE `id` = '{$plid}';";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
                $list[] = array('id'=>$value['id'],'name'=>$value['plname']);
            }
            return $list[0]['name'];
        } else {
            return false;
        }


    }
    public static function GetArrayPlaylist($user_id) {
        $list = array();
        $db = new DB;
        $sql = "SELECT * FROM `user_playlist` WHERE `user_id` = '{$user_id}';";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
                $list[] = array('id'=>$value['id'],'name'=>$value['plname']);
            }
            return $list;
        } else {
            return false;
        }

    }
    public static function trackCount ($playlistid) {
        $db = new DB;
        $sql = "SELECT COUNT(*) FROM `playlist` WHERE `playlist_id` = '{$playlistid}';";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            return $res[0];
        } else {
            return false;
        }
    }

    public static function GetmyPlaylistId($userid) {
        $db = new DB;
        $sql = "SELECT `defpl` FROM `user` WHERE `id` = '{$userid}';";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            return $res[0]['defpl'];
        } else {
            return false;
        }
    }
    //todo пробовать оптимизировать запросы в vk.com
    public static function Addtrack($playlistid, $trackid) {
        $resp = file_get_contents('https://api.vk.com/method/audio.getById?audios='.$trackid.'&access_token='.Core::taketoken());
        $data = json_decode($resp, true);
        if ( empty( $data['response'] ) ) {
            return false;
        }
        if (key_exists('error',$data)) {
            if ($data["error"]["error_code"] == 5) {
                Core::deltoken();
            }
            return false;
        } else {
            $list = $data['response'];
            $db = new DB;
            $serial = Plist::GetLastSerial($playlistid)+1;
            $sql = "INSERT INTO `playlist` (`playlist_id`, `track_id`, `serial`, `track_artist`, `track_title`, `track_duration`) VALUES ( '{$playlistid}', '{$trackid}', '{$serial}', '{$list[0]['artist']}', '{$list[0]['title']}', '{$list[0]['duration']}' );";
            $db->dbquery($sql);
            return true;
        }
    }

    public static function GetLastSerial($playlistid) {
        $db = new DB;
        $sql = "SELECT serial FROM `playlist` WHERE playlist_id = '{$playlistid}' ORDER BY serial DESC";
        $result = $db->dbquery($sql);
        var_dump($result);
        return $result[0]['serial'];
    }

    public static function Deltrack($playlistid, $serial) {
        $db = new DB;
        $sql = "DELETE FROM `playlist` WHERE playlist_id = '{$playlistid}' AND serial = '{$serial}';";
        $db->dbquery($sql);
        $rows = mysql_affected_rows($db->db);
        $playlist_id = mysql_insert_id($db->db);
        if ($rows) {
            $sql = "UPDATE `playlist` SET `serial`=`serial`-1 WHERE `serial` > {$serial};";
            $db->dbquery($sql);
            return true;
        } else {
            return false;
        }
    }

    public static function DelPlaylist($playlistid) {
        global $app;
        $db = new DB;
        $sql = "SELECT * FROM `user` WHERE `defpl` = '{$playlistid}';";
        $res = $db->dbquery($sql);
        if (!is_array($res)) {
            $sql = "DELETE FROM `playlist` WHERE `playlist_id` = '{$playlistid}';";
            $res = $db->dbquery($sql);
            $sql = "DELETE FROM `user_playlist` WHERE `id` = '{$playlistid}';";
            $res = $db->dbquery($sql);
            if ($res > 0) {
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
        $trackperpage = $GLOBALS['conf']['trackperpage'];
        $db = new DB;
        $sql = "SELECT * FROM `playlist` WHERE `playlist_id` = '{$playlistid}' ORDER BY `serial` LIMIT {$offset}, {$trackperpage};";
        $res = $db->dbquery($sql);

        if (is_array($res)) {

            foreach ($res as $key => $value) {
                $track = explode("_", $value['track_id']);
                $list[] = array('aid'=>$track[1],'owner_id'=>$track[0], 'serial' => $value['serial'], 'artist' => $value['track_artist'], 'title'=> $value['track_title'], 'duration' => Core::sec2time($value['track_duration']));
            }
            return $list;
        } else {
            return false;
        }

    }

    public static function CreatePlaylist ($id, $defpl = false, $name = "Мой плейлист" ) {
        global $app;
        $app['monolog']->AddDebug(__FUNCTION__.' Cоздавать новый плейлист ', array('id'=>$id, 'defpl'=>$defpl, 'name'=>$name));
        $db = new DB;
        $sql = "INSERT INTO `user_playlist` (`user_id`, `plname`) VALUES ( {$id}, \"{$name}\" );";
        $app['monolog']->AddDebug(__FUNCTION__.' Готовим запрос  ', array('sql'=>$sql));
        $db->dbquery($sql);
        $rows = mysql_affected_rows($db->db);
        $playlist_id = mysql_insert_id($db->db);
        $app['monolog']->AddDebug(__FUNCTION__.' Запрос выполнен ', array('rows'=>$rows, 'playlist_id'=>$playlist_id));
        if ($rows) {
            $app['monolog']->AddDebug(__FUNCTION__.' Rows != 0 ', array('rows'=>$rows));
            if ($defpl) {
                $app['monolog']->AddDebug(__FUNCTION__.' Выбран плейлист по умолчанию ', array('defpl'=>$defpl));
                $sql = "UPDATE `user` SET defpl = {$playlist_id} WHERE id = {$id};";
                $app['monolog']->AddDebug(__FUNCTION__.' Готовим запрос  ', array('sql'=>$sql));
                $db->dbquery($sql);
                $rows = mysql_affected_rows($db->db);
                $app['monolog']->AddDebug(__FUNCTION__.' Запрос выполнен ', array('rows'=>$rows));
            }
            return $playlist_id;
        } else {
            return false;
        }
    }

}
