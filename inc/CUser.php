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
        global $app;
        $login = $_COOKIE['PHPSESSID'];
        //$app['monolog']->addInfo(__FUNCTION__.' Процесс первого входа на сайт' , array('PHPSESSID' => $login));
        $uid = CUser::createUser($login, '1', 1, 1, null);
        //$app['monolog']->addDebug(__FUNCTION__.' Получилось создать нового пользователя?' , array('UID' => $uid));
        if ($uid){
            if (CUser::login($login,'1')){
                //$app['monolog']->addInfo(__FUNCTION__.' Залогинили пользователя ' , array('login' => $login));
            } else {
                //$app['monolog']->addInfo(' Не удалось залогинить пользователя', array('login' => $login) );
            }
        } else {
            //$app['monolog']->addWarning('Не удалось создать пользователя', array('login' => $login) );
        }
    }
    public static function isLogedin() {
        global $app;
        $userid = CUser::GetUserId();
        if (isset($userid)) {

            if (CUser::UserExist($userid)) {
                //$app['monolog']->addInfo(__FUNCTION__.__LINE__.' user loged in ', array('userid' => $userid));
                return true;
            } else {
                //todo Правильно удалить плюшку cookie
                //setcookie('svireluser', $userid, time() - 3600, '/',$GLOBALS['conf']['cookie_domain']);
                //$app['monolog']->addInfo(__FUNCTION__.__LINE__.' User not exist user forced loged out ', array('userid' => $userid));
                return false;
            }
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
        /*$app['monolog']->addInfo(__FUNCTION__.' Попытка проверки разрешения на rule= ', array(
            'rule' => $rule
        ));*/
        $rule = $app->escape($rule);
        $userid = CUser::GetUserId();
        if (CUser::isLogedin()) {
            $role = CUser::GetRole($userid);
            $db = new DB;
            $sql = "SELECT * FROM `roles` WHERE `role_id` = '{$role}';";
            $res = $db->dbquery($sql);
            if (is_array($res)) {
                /*$app['monolog']->addInfo(__FUNCTION__.' Получили список разрешений для роли ', array(
                    'role' => $role,
                    'sql' => $sql,
                    'res' => $res
                ));*/
                if ($res[0][$rule] == 1) {
                    return true;
                } else {
                    return false;
                }

            } else {
                /*$app['monolog']->addDebug('No such ROLE in DB', array(
                    'userid' => $app->escape($app['session']->get('login')),
                    'res' => $res,
                    'userip' => $_SERVER['REMOTE_ADDR'],
                    'X_FORWARDED' => $_SERVER['HTTP_X_FORWARDED_FOR']
                ));*/
                return false;
            }
        } else {
            return false;
        }

    }

    public static function UserExist ($id) {
        global $app;
        $db = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        $res = $db->dbquery($sql);
        //$app['monolog']->addDebug(__FUNCTION__.' sql ', array('sql' => $sql, 'res' => $res));
        if (is_array($res)) {
            //$app['monolog']->addInfo(__FUNCTION__.' user exist ', array('userid' => $id));
            return true;
        } else {
            /*$app['monolog']->addDebug(__FUNCTION__.__LINE__.'No such user in DB', array(
                'userid' => $id,
                'sql' => $sql,
                'res' => $res,
                'userip' => $_SERVER['REMOTE_ADDR']
            ));*/
            return false;
        }

    }

    public static function isUser($login, $pass) {
        Log::write(__FUNCTION__.": Авторизуем пользователя {$login}",__LINE__);
        $base = new DB;
        $pass = md5($pass);
        Log::write(__FUNCTION__.": Делаем hash {$pass}",__LINE__);
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}' AND `pass` = '{$pass}';";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $res = $base->dbquery($sql);
        if (is_array($res)) {

            Log::write(__FUNCTION__.": Результат: ".var_export($res, true),__LINE__);
            return true;
        } else {
            Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }
    }

    public static function GetRole ($user_id) {
        Log::write(__FUNCTION__.": Проверяем пользователя на существование {$user_id}",__LINE__);
        $base = new DB;
        $sql = "SELECT `role_id` FROM `user` WHERE `id` = '{$user_id}';";
        Log::write(__FUNCTION__.": Запрос {$sql}",__LINE__);
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            Log::write(__FUNCTION__.": Результат: ".var_export($res, true),__LINE__);
            return $res[0]['role_id'];
        } else {
            Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }
    }
    public static function checkLogin($login) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Проверяем логин на существование в базе' , array('login' => $login));
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}';";
        //$app['monolog']->addInfo(__FUNCTION__.' Сформировали запрос' , array('sql' => $sql));
        $res = $base->dbquery($sql);
        //$app['monolog']->addDebug(__FUNCTION__.' Результат запроса' , array('res' => $res));
        if (is_array($res)) {
            //$app['monolog']->addDebug(__FUNCTION__.' Результат запроса оказался массивом.' , array('isarray' => is_array($res)));
            return true;
        } else {
            //$app['monolog']->addDebug(__FUNCTION__.' Результат не массив, значит в базе ничего не найдено' , array('isarray' => is_array($res)));
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
        Log::write(__FUNCTION__.": Проверяем логин {$login}",__LINE__);
        if (!preg_match("/^[0-9a-zA-Z-_]{1,100}$/", $login)) {
            Log::write(__FUNCTION__.": Не прошел {$login}",__LINE__);
            return false;
        } else {
            Log::write(__FUNCTION__.": Все нормально {$login}",__LINE__);
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
        Log::write(__FUNCTION__.": Проверяем пароль {$pass}",__LINE__);
        if (!preg_match("/^[0-9a-zA-Z-_]{1,100}$/", $pass)) {
            Log::write(__FUNCTION__.": Не прошел {$pass}",__LINE__);
            return false;
        } else {
            Log::write(__FUNCTION__.": Все нормально с {$pass}",__LINE__);
            return true;
        }
    }
    public static function getId($login) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Ищем идентификатор пользователя' , array('login' => $login));
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}';";
        //$app['monolog']->addInfo(__FUNCTION__.' SQL запрос' , array('sql' => $sql));
        $res = $base->dbquery($sql);
        //$app['monolog']->addDebug(__FUNCTION__.' Результат запроса' , array('res' => $res));
        if (is_array($res)) {
            //$app['monolog']->addDebug(__FUNCTION__.' Возвращаем значения' , array('res[0][id]' => $res[0]['id']));
            return $res[0]['id'];
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Возвращаем значения false' );
            return false;
        }
    }
    public static function DeleteUser ($uid)  {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Удаляем пользователя' , array('uid' => $uid));
        //$plid = Plist::GetmyPlaylistId($uid);
        $base = new DB;
        /*$sql = "DELETE FROM `playlist` WHERE `playlist_id` = {$plid};";
        $base->dbquery($sql);
        $rows = mysql_affected_rows($base->db);
        $id = mysql_insert_id($base->db);
        $app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('sql' => $sql, 'rows' => $rows, 'id' => $id));
*/
        $sql = "DELETE FROM `user_playlist` WHERE `user_id` = {$uid};";
        $base->dbquery($sql);
        $rows = mysql_affected_rows($base->db);
        $id = mysql_insert_id($base->db);
        //$app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('sql' => $sql, 'rows' => $rows, 'id' => $id));

        $sql = "DELETE FROM `user` WHERE `id` = {$uid};";
        $base->dbquery($sql);
        $rows = mysql_affected_rows($base->db);
        $id = mysql_insert_id($base->db);
        //$app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('sql' => $sql, 'rows' => $rows, 'id' => $id));
    }






    public static function createUser($login, $pass, $active = 1, $role = 1, $defpl_id = null) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Создаем нового пользователя' , array('login' => $login, 'pass' => md5($pass), 'active' => $active, 'role' => $role, '$defpl_id' => $defpl_id));
        if (self::checkLogin($login)) {
            $userid = self::getId($login);
            //$app['monolog']->addInfo(__FUNCTION__.' Пользователь есть в базе' , array('login' => $login, 'userid' => $userid));
            return $userid;
        } else {
            //$app['monolog']->addInfo(__FUNCTION__.' Пользователя нет в базе' , array('login' => $login));
            //$app['monolog']->addDebug(__FUNCTION__.' Проверим логин и пароль на валидность' , array('login' => self::isValidLogin($login), 'pass' => self::isValidPass($pass), 'result' => self::isValidLogin($login) && self::isValidPass($pass)));
            if (self::isValidLogin($login) && self::isValidPass($pass)) {
                //$app['monolog']->addInfo(__FUNCTION__.' Логин и пароль удовлетворяют условиям');
                $base = new DB;
                $pass = md5($pass);

                $sql = "INSERT INTO `user` (`role_id`, `login`, `pass`, `active`, `defpl`) VALUES ('{$role}', '{$login}', '{$pass}', '{$active}', '{$defpl_id}');";

                //$app['monolog']->addInfo(__FUNCTION__.' SQL запрос сформировали', array('sql' => $sql));
                $base->dbquery($sql);
                $rows = mysql_affected_rows($base->db);
                $id = mysql_insert_id($base->db);
                //$app['monolog']->addDebug(__FUNCTION__.' Запрос выполнен ' , array('rows' => $rows, 'id' => $id));
                if ($rows) {
                    if (!$defpl_id) {
                        //$app['monolog']->addInfo(__FUNCTION__.' Не установлен плейлист по умолчанию для данного пользователя' , array('defpl_id' => !isset($defpl_id)));
                        //$app['monolog']->addInfo(__FUNCTION__.' Попытка создать новый плейлист');
                        Plist::CreatePlaylist($id, true);

                        //$app['monolog']->addInfo(__FUNCTION__.' Создаем плейлист по умолчанию для пользователя ' , array('id' => $id));
                    }
                    State::createstate($id);
                    return $id;
                } else {
                    return false;
                }
            } else {
                //$app['monolog']->addInfo(__FUNCTION__.' Логин и пароль не удовлетворяют условиям ' , array('login' => $login, 'pass' => $pass));
                return false;
            }
        }
    }
    public static function login ($login, $pass) {
        global $app;
        //$app['monolog']->addInfo(__FUNCTION__.' Процесс логина пользователя', array('login' => $login, 'pass' => md5($pass)) );
        $base = new DB;
        $pass = md5($pass);
        $sql = "SELECT * FROM `user` WHERE `login` = '{$login}' AND `pass` = '{$pass}';";
        //$app['monolog']->addDebug(__FUNCTION__.' SQL запрос ', array('sql' => $sql) );
        $res = $base->dbquery($sql);
        //$app['monolog']->addDebug(__FUNCTION__.' SQL запрос выполнен', array('res' => $res) );
        if (is_array($res)) {
            $id = $res [0]['id'];
            setcookie('svireluser', $id, time() + 3600, '/',$GLOBALS['conf']['cookie_domain']);
            //$app['monolog']->addDebug(__FUNCTION__.' Поставили cookie', array('svireluser' => $id) );
            return $id;
        } else {
            //$app['monolog']->addDebug(__FUNCTION__.' Вернули false');
            return false;
        }
    }
    public static function GetUserbyId ($id) {
        Log::write(__FUNCTION__.": Ищем пользователя с id ={$id}",__LINE__);
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        $res = $base->dbquery($sql);
        if (is_array($res)) {

            return $res[0]['login'];
        } else {
            Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return false;
        }
    }
    public static function isGuest($id) {
        Log::write(__FUNCTION__.": Ищем пользователя с id ={$id}",__LINE__);
        $base = new DB;
        $sql = "SELECT * FROM `user` WHERE `id` = '{$id}';";
        Log::write(__FUNCTION__.": SQL ={$sql}",__LINE__);
        $res = $base->dbquery($sql);
        if (is_array($res)) {
            Log::write(__FUNCTION__.": Результат: ".var_export($res,true),__LINE__);
            if ($res[0]['role_id'] == 1){
                return true;
            } else {
                return false;
            }
        } else {
            Log::write(__FUNCTION__.": Видать ничего нет в базе",__LINE__);
            return null;
        }
    }
}
