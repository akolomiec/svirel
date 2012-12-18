<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.kolomiec
 * Date: 18.12.12
 * Time: 9:43
 * To change this template use File | Settings | File Templates.
 */
class Lastfm
{
    public static function request ($method ,$params) {
        $apiKey = $GLOBALS['conf']['lastfm_apikey'];
        $qparams = http_build_query($params);
        $req = $GLOBALS['conf']['lastfm_api'] . "?method={$method}&format=json&api_key={$apiKey}&{$qparams}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $req);
        $responce = curl_exec($ch);
        curl_close($ch);

        return json_decode($responce);
    }
}
