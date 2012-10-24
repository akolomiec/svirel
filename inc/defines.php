<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 20.04.12
 * Time: 9:35
 * To change this template use File | Settings | File Templates.
 */
if (defined("AKPLAYER")) {

	// И старт сессии
	



	define('DS', DIRECTORY_SEPARATOR);
	define("CONFIG_PATH", APPLICATION_PATH . DS . "conf" . DS);

	require (CONFIG_PATH . "config.php");


	define("MEDIA_PATH", APPLICATION_PATH . DS . $GLOBALS['conf']['upload_dir'] . DS);
	define("WEB_ROOT", $GLOBALS['conf']['web_root']);


    // Включили логирование
    require (APPLICATION_PATH."/inc/Log.php");
    require (APPLICATION_PATH."/inc/Core.php");
    require (APPLICATION_PATH."/inc/DB.php");
    require (APPLICATION_PATH."/inc/CUser.php");
    require (APPLICATION_PATH."/inc/Plist.php");
    require (APPLICATION_PATH."/inc/LastSearch.php");
    require (APPLICATION_PATH."/inc/State.php");
}