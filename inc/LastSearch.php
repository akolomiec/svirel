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
        //Log::write (__FUNCTION__." Запрос последнего поиска для пользователя id={$uid}",__LINE__);
        $db = new DB;
        $sql = "SELECT DISTINCT `lastsearch` FROM `lsearch` WHERE `user_id` = '{$uid}' ORDER BY `date` DESC LIMIT 3;";
        //Log::write(__FUNCTION__.": Запрос sql={$sql}",__LINE__);
        $res = $db->dbquery($sql);

        if (is_array($res)) {

            foreach ($res as $key => $value) {
            $list[] = $value;

            }
            //Log::write(__FUNCTION__.": Результат: ".var_export($list,true),__LINE__);
            return $list;
        } else {
            //Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }
    }
    public static function SetLastSearch ($uid, $string) {
        //Log::write (__FUNCTION__." Устанавливаем последний запос поиска для id={$uid}",__LINE__);
        $db = new DB;
        $sql = "INSERT INTO `lsearch` (`user_id`, `lastsearch`) VALUES ('{$uid}', '{$string}');";
        //Log::write(__FUNCTION__.": Запрос sql={$sql}",__LINE__);
        $res = $db->dbquery($sql);
        //Log::write(__FUNCTION__.": Результат запроса: ".var_export($res,true),__LINE__);
        $rows = mysql_affected_rows($db->db);
        $id = mysql_insert_id($db->db);
        //Log::write(__FUNCTION__.": Было затронуто строк: {$rows} id={$id}",__LINE__);
    }
}
