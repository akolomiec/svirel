<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 17.12.12
 * Time: 11:40
 * To change this template use File | Settings | File Templates.
 */
class Cache
{
    /* Запись кэш-файла
    * @param string contents - содержание буфера
    * @param string filename - имя файла, используемое при создании кэш-файла
    * @return void
    */
    public static function writeCache($content, $filename) {
        $fp = fopen($GLOBALS['conf']['cache_dir'].$filename, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    /* Проверка кэш-файлов
    * @param string filename - имя проверяемого кэш-файла
    * @param int expiry - максимальный "возраст" файла в секундах
    * @return mixed содержимое кэша или false
    */
    public static function readCache($filename, $expiry) {
        if (file_exists($GLOBALS['conf']['cache_dir']. $filename)) {
            if ((time() - $expiry) > filemtime($GLOBALS['conf']['cache_dir'] . $filename))
                return FALSE;
            $cache = file($GLOBALS['conf']['cache_dir'] . $filename);
            return implode('', $cache);
        }
        return FALSE;
    }
}
