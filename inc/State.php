<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 06.04.12
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */
if (defined("AKPLAYER")) {
    class State  {
        static function createstate($id) {
            global $app;
            $app['monolog']->addDebug(__FUNCTION__.' Создаем состояние по умолчанию: ' , array('sql' => $sql, 'rows' => $rows));
            $base = new DB;
            $sql = "INSERT INTO `state` (`user_id`, `side`, `serial`, `search`, `page`, `playlistid`) VALUES ('{$id}', 'left', '1', '', '1', '');";
            $base->dbquery($sql);
            $rows = mysql_affected_rows($base->db);
            $app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('sql' => $sql, 'rows' => $rows));
        }
        static function save($username, $side = 'left', $playlistid = '', $search = '', $serial = 1, $currentpage = 1, $sort = '') {
            global $app;
            $app['monolog']->addDebug(__FUNCTION__.' Сохраняем состояние: ' , array());
            $base = new DB;
            $sql = "UPDATE `state` SET";
            if (!empty($side)) { $sql .= " `side`='{$side}',";}
            if (!empty($serial)) {$sql .= " `serial`='{$serial}',";}
            if (!empty($search)) {$sql .= " `search`='{$search}',";}
            if (!empty($currentpage)) {$sql .= " `page`='{$currentpage}',";}
            if (!empty($playlistid)) {$sql .= " `playlistid`='{$playlistid}',";}
            $sql = substr($sql, 0, -1);
            $sql = $sql. " WHERE `user_id` ='{$username}';";
            $base->dbquery($sql);
            $rows = mysql_affected_rows($base->db);
            $app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('sql' => $sql, 'rows' => $rows));
        }
        static function Getserial($side = 'left', $playlistid = '', $req = '') {
            global $app;
            $app['monolog']->addDebug(__FUNCTION__.' Посмотрим какой файл поет: ', array('side' => $side, 'playlistid' => $playlistid));
            $uid = CUser::GetUserId();
            $base = new DB;
            if (!empty($req) AND $playlistid = 'search'){
                $sql = "SELECT `serial` FROM `state` WHERE `user_id` = '{$uid}' AND `playlistid`='search' AND `search` = '{$req}';";
            } else {
                $sql = "SELECT `serial` FROM `state` WHERE `user_id` = '{$uid}' AND `playlistid`='{$playlistid}';";
            }
            $res = $base->dbquery($sql);
            $app['monolog']->addDebug(__FUNCTION__.' sql ', array('sql' => $sql, 'res' => $res));
            if (is_array($res)) {
                $serial = $res[0]['serial'];
            } else {
                $serial = 0;
            }
            return $serial;
        }
        public static function load() {
            global $app;
            $uid = CUser::GetUserId();
            if (isset($uid)){
                //$app['monolog']->addDebug(__FUNCTION__.'  ');
                $base = new DB;
                $sql = "SELECT * FROM `state` WHERE `user_id` = '{$uid}';";
                $res = $base->dbquery($sql);
                $app['monolog']->addDebug(__FUNCTION__.' Загружаем состояние: sql ', array('sql' => $sql, 'res' => $res));
                if (is_array($res)) {
                    return $res[0];
                } else {
                    return false;
                }
            }
        }
        //todo Просто дописать процедуру.
        static function GetSongbySerial($serial) {
            global $app;
            if (isset($serial)) {
                $app['monolog']->addDebug(__FUNCTION__.' Получаем имя файла который будем петь: ', array('serial' => $serial));

                $uid = CUser::GetUserId();
                $state = self::load();
                $app['monolog']->addDebug(__FUNCTION__.' uid , state: ', array('uid' => $uid, 'state' => $state));
                if ($state['side'] == 'left'){
                    $app['monolog']->addDebug(__FUNCTION__.' Side=left ', array('side' => $state['side']));
                    if ($state ['playlistid'] == 'search'){
                        $app['monolog']->addDebug(__FUNCTION__.' playlistid=search ', array('playlistid' => $state ['playlistid']));
                        $list = Core::Search($state['search'], 1, $serial-1);
                        $app['monolog']->addDebug(__FUNCTION__.' Получили выдачу поиска: ', array('list' => $list));
                        $list = $list['response'];
                        $list = $list[1];
                        //print_r($list);
                        $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        $app['monolog']->addDebug(__FUNCTION__.' Имя файла которое получили: ', array ('filename' => $filename));
                        return $filename;
                    } elseif ($state ['playlistid'] == 'top100') {
                        $app['monolog']->addDebug(__FUNCTION__.' playlistid=top100 ', array('playlistid' => $state ['playlistid']));
                        $list = Core::GetTop100(1, $serial-1);
                        $list = $list['response'];
                        $list = $list[0];
                        //print_r($list);
                        $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        $app['monolog']->addDebug(__FUNCTION__.' Получили выдачу Top100: ', array('filename' => $filename));
                        return $filename;
                    } else {
                        $app['monolog']->addDebug(__FUNCTION__.' playlistid ни с чем не совпал ', array('playlistid' => $state ['playlistid']));
                        return false;
                    }
                } elseif ($state ['side'] == 'right') {
                    $app['monolog']->addDebug(__FUNCTION__.' side=right ', array('side' => $state ['side']));
                    $list = Plist::GetPlaylist($state['playlistid'], $serial-1);
                    $list = $list[0];
                    //print_r($list);
                    $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                    $app['monolog']->addDebug(__FUNCTION__.' Получили выдачу Плейлиста: ', array ('playlistid' => $state['playlistid'],'list' =>$list));
                    return $filename;
                }
                //return filename;
                //or return false;
            }
        }

    }

}