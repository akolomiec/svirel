<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 19.06.12
 * Time: 9:16
 * To change this template use File | Settings | File Templates.
 *
 * Использование
 * var $base_conn = new DB;
 * В это время происходит подключение к базе.
 *
 * $sql = "SELECT * FROM user WHERE login = {$login} AND pass = {$pass};";
 * $base->dbquery($sql);
 *
 *
 */
if (defined("AKPLAYER")) {
    class DB
    {
        var $dbhost;
        var $dbport;
        var $dbusername;
        var $dbpass;
        var $dbname;
        var $charset;
        var $result;
        var $db;

        public function __construct() {
            global $app;
            $this->dbhost = $GLOBALS['conf']['db_host'];
            $this->dbusername = $GLOBALS['conf']['db_user'];
            $this->dbpass = $GLOBALS['conf']['db_password'];
            $this->dbport = $GLOBALS['conf']['db_port'];
            $this->dbname = $GLOBALS['conf']['db_name'];
            $this->dbcharset = $GLOBALS['conf']['db_charset'];
            if (!$this->db_connect()) {
                $app['monolog']->addCritical(__FUNCTION__.' Не удалось подключиться к базе. Самофраг!',
                    array('dbhost' => $this->dbhost,
                        'dbusername' =>$this->dbusername,
                        'dbpass' => $this->dbpass,
                        'dbport' => $this->dbport,
                        'dbname' => $this->dbname,
                        'dbcharset' => $this->dbcharset
                    ));
                die ("Что-то серьезное случилось, Админ уже в курсе.");
            }
        }
        public function db_connect () {
            global $app;
            $this->db = mysql_connect("{$this->dbhost}:{$this->dbport}", "{$this->dbusername}", "{$this->dbpass}");
            if ($this->db) {
                //$app['monolog']->addInfo(__FUNCTION__.' Подключение к базе прошло нормально');
                mysql_select_db($this->dbname);
                mysql_query("SET NAMES {$this->dbcharset}");
                mysql_set_charset("utf8");
                return true;
            } else {
                $app['monolog']->addDebug(__FUNCTION__.' Подключение к базе не произведено',
                    array('Err_no' => mysql_errno(),
                        'Err_msg' => mysql_error()));
                return false;
            }
        }
        public function dbquery ($sql) {
            global $app;
            //$app['monolog']->addInfo(__FUNCTION__.' Выполнение запроса', array('sql' => $sql));
            $this->result = mysql_query($sql);
            if(mysql_errno()) {
                //$app['monolog']->addError(__FUNCTION__.' Ошибка выполнения запроса', array('sql_errorno' => mysql_errno(), 'sql_msg' => mysql_error()));
                return false;
            }
            if (!is_bool($this->result)){
                $rows = mysql_num_rows($this->result);
                //$app['monolog']->addDebug(__FUNCTION__.' Количество записей', array('rows' => $rows));
                if ($rows) {

                    while ($row = mysql_fetch_assoc($this->result)) {
                        $data[] = $row;
                    }
                    //var_dump($data);
                    //$app['monolog']->addDebug(__FUNCTION__.' Возвращаем массив данных', array('data' => $data));
                    return $data;
                } else {
                    //$app['monolog']->addinfo(__FUNCTION__.' Записей в базе не найдено возвращаем false');
                    return false;
                }
            } else {
                $rows = mysql_affected_rows($this->db);
                //$app['monolog']->addDebug(__FUNCTION__.' Затронуто записей', array('rows' => $rows));
                return true;
            }

        }
    }
}