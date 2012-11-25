<?php
namespace Oryzias;

class Log
{
    public static function write($arr=null)
    {
        $arr['backtrace'] = debug_backtrace();
        $arr['createdAt'] = date('Y-m-d H:i:s');
        error_log(json_encode($arr) . "\n", 3, Config::get('logDir') . '/' . date('Y-m-d') . '.json');
    }
}
