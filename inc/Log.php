<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 06.04.12
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */
if (defined("AKPLAYER")) {
    class Log    {
        static $dir, $filename;

        static function write($str, $line)
        {
            self::$dir = APPLICATION_PATH."/log";
            self::$filename = "player.log";
            $fh = fopen(self::$dir . "/" . self::$filename, "a+") or die("Создать файл лога не удалось");
            $id = time() . " line:{$line} ";
            $result = !fwrite($fh, $id . $str . " \n") ? "Panic log not writable" : "";
            if ($result <> "") {
                echo $result;
            }
            fclose($fh);
        }
    }
}