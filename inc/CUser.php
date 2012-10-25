<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 18.06.12
 * Time: 17:11
 * To change this template use File | Settings | File Templates.
 */
class CUser
{

    public static function first_enter () {
        $login = $_COOKIE['PHPSESSID'];
        $id = CUser::createUser($login, '1', 1, 1, null);
        setcookie('svireluser', $id, time() + 3600, '/',$GLOBALS['conf']['cookie_domain']);
    }
    public static function isLogedin() {
        $userid = CUser::GetUserId();
        if (isset($userid) AND CUser::UserExist($userid)) {
            return true;
        } else {
            return false;
        }
    }

    public static function GetUserId() {
        global $app;
        if (key_exists('svireluser',$_COOKIE)) {
            return $app->escape($_COOKIE['svireluser']);
        } else {
            return false;
        }
    }

    public static function isGranted ( $rule) {
        global $app;
        $rule = $app->escape($rule);
        $userid = CUser::GetUserId();
        if (CUser::isLogedin()) {
            $role = CUser::GetRole($userid);
            $db = new DB;
            $sql = "SELECT * FROM `roles` WHERE `role_id` = '{$role}';";
            $res = $db->dbquery($sql);
            if (is_array($res) AND ($res[0][$rule] == 1)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public static function UserExist ($id) {
        $db = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        $res = $db->dbquery($sql);
        if (is_array($res)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isUser($login, $pass) {
        $base = new DB;
        $pass = md5($pass);
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}' AND `pass` = '{$pass}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            return true;
        } else {
            return false;
        }
    }

    public static function GetRole ($user_id) {
        $base = new DB;
        $sql = "SELECT `role_id` FROM `user` WHERE `id` = '{$user_id}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            return $res[0]['role_id'];
        } else {
            return false;
        }
    }
    public static function checkLogin($login) {
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            return true;
        } else {
            return false;
        }
    }
    /*
     * Проверка логина на валидность разрешены только латинские буквы, цифры, -, _
     * не меньше 1 символа, не больше 100 символов
     * На выходе получаем если логин удовлетворяет условиям, то true
     * Если не прошел по регулярке, то false
     *
     */
    public static function isValidLogin($login) {
        if (!preg_match("/^[0-9a-zA-Z-_]{1,100}$/", $login)) {
            return false;
        } else {
            return true;
        }
    }
    /*
     * Проверка пароля на валидность разрешены только латинские буквы, цифры, -, _
     * не меньше 1 символа, не больше 100 символов
     * На выходе получаем если логин удовлетворяет условиям, то true
     * Если не прошел по регулярке, то false
     *
     */
    public static function isValidPass($pass) {
        if (!preg_match("/^[0-9a-zA-Z-_]{1,100}$/", $pass)) {
            return false;
        } else {
            return true;
        }
    }
    public static function getId($login) {
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            return $res[0]['id'];
        } else {
            return false;
        }
    }
    public static function DeleteUser ($uid)  {
        global $app;
        $base = new DB;
        $sql = "DELETE FROM `user_playlist` WHERE `user_id` = {$uid};";
        $base->dbquery($sql);
        $sql = "DELETE FROM `user` WHERE `id` = {$uid};";
        $base->dbquery($sql);
    }

    public static function createUser($login, $pass, $active = 1, $role = 1, $defpl_id = null) {
        if (self::checkLogin($login)) {
            $userid = self::getId($login);
            return $userid;
        } else {
            if (self::isValidLogin($login) && self::isValidPass($pass)) {
                $base = new DB;
                $pass = md5($pass);
                $sql = "INSERT INTO `user` (`role_id`, `login`, `pass`, `active`, `defpl`) VALUES ('{$role}', '{$login}', '{$pass}', '{$active}', '{$defpl_id}');";
                $base->dbquery($sql);
                $rows = mysql_affected_rows($base->db);
                $id = mysql_insert_id($base->db);
                if ($rows) {
                    if (empty($defpl_id)) {
                        Plist::CreatePlaylist($id, true);
                    }
                    State::createstate($id);
                    return $id;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
    public static function login ($login, $pass) {
        $base = new DB;
        $pass = md5($pass);
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}' AND `pass` = '{$pass}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            $id = $res [0]['id'];

            return $id;
        } else {
            return false;
        }
    }
    public static function GetUserbyId ($id) {
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            return $res[0]['login'];
        } else {
            return false;
        }
    }
    public static function isGuest($id) {
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            if ($res[0]['role_id'] == 1){
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
    }
}
