<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 16.07.12
 * Time: 10:51
 * To change this template use File | Settings | File Templates.
 */
class LastSearch {
    public static function GetLastSearch ($uid) {
        $db = new DB;
        $sql = "SELECT DISTINCT `lastsearch` FROM `lsearch` WHERE `user_id` = '{$uid}' ORDER BY `date` DESC LIMIT 3;";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            foreach ($res as $key => $value) {
            $list[] = $value;
            }
            return $list;
        } else {
            return false;
        }
    }

    public static function SetLastSearch ($uid, $string) {
        global $app;
        $db = new DB;
        $string = $app->escape($string);
        $sql = "INSERT INTO `lsearch` (`user_id`, `lastsearch`) VALUES ('{$uid}', '{$string}');";
        $db->dbquery($sql);
    }
}
