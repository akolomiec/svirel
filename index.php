<?php
// Вывод ошибок
error_reporting (E_ALL ^ E_NOTICE);
// Запомнили время начала этого действа
$start = microtime(true);

define( "AKPLAYER" , TRUE );

// Начальная директория запуска приложения.
define("APPLICATION_PATH", __DIR__);

require (APPLICATION_PATH."/inc/defines.php");



require_once APPLICATION_PATH."/vendor/autoload.php";

$app = new Silex\Application();

$app['debug'] = true;


require (APPLICATION_PATH."/inc/services.php");
require (APPLICATION_PATH."/inc/controllers.php");

// Запуск

$app->run();


// сохранили время выполнения скрипта.
Log::write("Время выполнения скрипта  ".(microtime(true) - $start),__LINE__);
?>

