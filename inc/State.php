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
            $base = new DB;
            $sql = "INSERT INTO `state` (`user_id`, `side`, `serial`, `search`, `page`, `playlistid`, `repeat`) VALUES ('{$id}', 'left', '1', '', '1', '', '');";
            $base->dbquery($sql);
        }

        static function save($username, $side = 'left', $playlistid = '', $search = '', $serial = 1, $currentpage = 1, $repeat = '', $shuffle = '', $sort = '') {
            global $app;
            $base = new DB;
            $sql = "UPDATE `state` SET";
            if (!empty($side)) { $sql .= " `side`='{$side}',";}
            if (!empty($serial)) {$sql .= " `serial`='{$serial}',";}
            if (!empty($search)) {$sql .= " `search`='{$search}',";}
            if (!empty($currentpage)) {$sql .= " `page`='{$currentpage}',";}
            if (!empty($playlistid)) {$sql .= " `playlistid`='{$playlistid}',";}
            if (!empty($repeat)) {$sql .= " `repeat`='{$repeat}',";}
            if (!empty($shuffle)) {$sql .= " `shuffle`='{$shuffle}',";}
            $sql = substr($sql, 0, -1);
            $sql = $sql. " WHERE `user_id` ='{$username}';";
            $app['monolog']->AddDebug(__FUNCTION__.' Сохраняем состояние ', array('sql'=>$sql));
            $base->dbquery($sql);


        }

        static function Getserial($side = 'left', $playlistid = '', $req = '') {
            $uid = CUser::GetUserId();
            $base = new DB;
            if (!empty($req) AND $playlistid = 'search'){
                $sql = "SELECT `serial` FROM `state` WHERE `user_id` = '{$uid}' AND `playlistid`='search' AND `search` = '{$req}';";
            } else {
                $sql = "SELECT `serial` FROM `state` WHERE `user_id` = '{$uid}' AND `playlistid`='{$playlistid}';";
            }
            $res = $base->dbquery($sql);
            if (is_array($res)) {
                $serial = $res[0]['serial'];
            } else {
                $serial = 0;
            }
            return $serial;
        }

        public static function load() {
            $uid = CUser::GetUserId();
            if (isset($uid)){
                $base = new DB;
                $sql = "SELECT * FROM `state` WHERE `user_id` = '{$uid}';";
                $res = $base->dbquery($sql);
                if (is_array($res)) {
                    return $res[0];
                } else {
                    return false;
                }
            }
        }

        static function GetSongbySerial($serial, $mp3 = true) {
            global $app;
            if (isset($serial)) {
                $uid = CUser::GetUserId();
                $state = self::load();
                if ($state['side'] == 'left'){
                    if ($state ['playlistid'] == 'search'){
                        $list = Core::Search($state['search'], 1, $serial-1);
                        $list = $list['response'];
                        $list = $list[1];
                        if ($mp3) {
                            $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        } else {
                            $filename = "{$list['owner_id']}_{$list['aid']}";
                        }

                        return $filename;
                    } elseif ($state ['playlistid'] == 'top100') {
                        $list = Core::GetTop100(1, $serial-1);
                        $list = $list['response'];
                        $list = $list[0];
                        if ($mp3) {
                            $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        } else {
                            $filename = "{$list['owner_id']}_{$list['aid']}";
                        }
                        return $filename;
                    } else {
                        return false;
                    }
                } elseif ($state ['side'] == 'right') {
                    $list = Plist::GetPlaylist($state['playlistid'], $serial-1);
                    $list = $list[0];
                    if ($mp3) {
                        $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                    } else {
                        $filename = "{$list['owner_id']}_{$list['aid']}";
                    }
                    return $filename;
                }
            }
        }

        public static function maxtrack ($state) {
            global $app;
            if ($state['side'] == 'left'){
                if ($state['playlistid'] == 'search'){
                    $result = Core::Search();
                    $app['monolog']->AddDebug(__FUNCTION__.' Левая сторона, поиск. результат', array('side'=>$state['side'], 'playlistid'=>$state ['playlistid'], 'res' => $result['response'][0]));
                    if ($result['response'][0] > $GLOBALS['conf']['maxtrack'] ){
                        $count = $GLOBALS['conf']['maxtrack'];
                    }
                    return $count;
                } elseif ($state ['playlistid'] == 'top100') {
                    $app['monolog']->AddDebug(__FUNCTION__.' Левая сторона, TOP100. результат', array('side'=>$state['side'], 'playlistid'=>$state ['playlistid'], 'res' => 100));
                    $count = 94;
                    return $count;
                } else {
                    return false;
                }
            } elseif ($state ['side'] == 'right') {
                $base = new DB;
                $sql = "SELECT COUNT(*) FROM `playlist` WHERE `playlist_id`='{$state['playlistid']}';";
                $res = $base->dbquery($sql);
                $app['monolog']->AddDebug(__FUNCTION__.' Правая сторона. результат', array('side'=>$state['side'], 'playlistid'=>$state ['playlistid'], 'res' => $res[0]['COUNT(*)']));
                if (is_array($res)) {
                    return $res[0]['COUNT(*)'];
                } else {
                    return false;
                }
            }
        }

        public static function repeat() {
            global $app;
            $state = self::load();
            $app['monolog']->AddDebug(__FUNCTION__.' Состояние кнопки репит ', array('state'=>$state));
            if ($state['repeat'] == 1) {
                return 1;
            } else {
                return false;
            }

        }

        public static function shuffle() {
            global $app;
            $state = self::load();
            $app['monolog']->AddDebug(__FUNCTION__.' Состояние кнопки в перемешку ', array('state'=>$state));
            if ($state['shuffle'] == 1) {
                return 1;
            } else {
                return false;
            }

        }


        public static function repeat_save($on) {
            global $app;
            $state = self::load();
            $app['monolog']->AddDebug(__FUNCTION__.' Состояние ', array('state'=>$state));
            $state['repeat'] = $app->escape($on);
            self::save($state['user_id'],$state['side'], $state['playlistid'], $state['search'],$state['serial'],$state['page'],$state['repeat'], $state['shuffle'],$state['sort']);

        }

        public static function shuffle_save($on) {
            global $app;
            $state = self::load();
            $app['monolog']->AddDebug(__FUNCTION__.' Состояние ', array('state'=>$state));
            $state['shuffle'] = $app->escape($on);
            $app['monolog']->AddDebug(__FUNCTION__.' ON =  ', array('on'=>$state['shuffle']));
            self::save($state['user_id'],$state['side'], $state['playlistid'], $state['search'],$state['serial'],$state['page'],$state['repeat'], $state['shuffle'],$state['sort']);

        }
    }
}