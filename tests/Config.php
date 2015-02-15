<?php

class Config {

    public static function get($key)
    {
        $conf = require __DIR__.'/../src/config/permiso.php';
        $key = str_replace('permiso.', '', $key);
        if (isset($conf[$key])) {
            return $conf[$key];
        }
    }
}