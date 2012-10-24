<?php 
use Symfony\Component\HttpFoundation\Response;

$app->error(function (\Exception $e, $code) {
    $message = "error";
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        //default:
        //    $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});