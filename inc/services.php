<?php

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => APPLICATION_PATH.'/views',
    ));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => APPLICATION_PATH.'/log/development.log',
    ));


