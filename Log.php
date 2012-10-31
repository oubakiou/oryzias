<?php
namespace Oryzias;

class Log
{
    public static function write($arr)
    {
        error_log(json_encode($arr), 3, Config::get('logDir'));
    }
}
