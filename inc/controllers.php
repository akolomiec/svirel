<?php
$controllers = array(
    'index',
    'error',
    'getaudio',
    'gettop',
    'registration',
    'playlist',
    'state'
);

foreach ($controllers as $controller) {
    require_once APPLICATION_PATH . "/controller/{$controller}.php";
}