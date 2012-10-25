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
            $sql = "INSERT INTO `state` (`user_id`, `side`, `serial`, `search`, `page`, `playlistid`) VALUES ('{$id}', 'left', '1', '', '1', '');";
            $base->dbquery($sql);
        }

        static function save($username, $side = 'left', $playlistid = '', $search = '', $serial = 1, $currentpage = 1, $sort = '') {
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
        //todo Просто дописать процедуру.
        static function GetSongbySerial($serial) {
            global $app;
            if (isset($serial)) {
                $uid = CUser::GetUserId();
                $state = self::load();
                if ($state['side'] == 'left'){
                    if ($state ['playlistid'] == 'search'){
                        $list = Core::Search($state['search'], 1, $serial-1);
                        $list = $list['response'];
                        $list = $list[1];
                        $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        return $filename;
                    } elseif ($state ['playlistid'] == 'top100') {
                        $list = Core::GetTop100(1, $serial-1);
                        $list = $list['response'];
                        $list = $list[0];
                        $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                        return $filename;
                    } else {
                        return false;
                    }
                } elseif ($state ['side'] == 'right') {
                    $list = Plist::GetPlaylist($state['playlistid'], $serial-1);
                    $list = $list[0];
                    $filename = "{$list['owner_id']}_{$list['aid']}.mp3";
                    return $filename;
                }
            }
        }
    }
}