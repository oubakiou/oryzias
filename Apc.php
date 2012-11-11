<?php
namespace Oryzias;

class Apc
{
    public static function store($key, $var, $ttl=null)
    {
        if (!$ttl) {
            $ttl = Config::get('apc.ttl');
        }
        return apc_store(Config::get('apc.keyPrefix') . $key, $var, $ttl);
    }
    
    public static function fetch($key)
    {
        return apc_fetch(Config::get('apc.keyPrefix') . $key);
    }
    
    public static function delete($key)
    {
        return apc_delete(Config::get('apc.keyPrefix') . $key);
    }
    
    public static function exists($keys)
    {
        return apc_exists(Config::get('apc.keyPrefix') . $keys);
    }
    
    //デクリメント
    public static function dec($key, $step=1)
    {
        return apc_dec(Config::get('apc.keyPrefix') . $key, $step);
    }
    
    //インクリメント
    public static function inc($key, $step=1)
    {
        return apc_inc(Config::get('apc.keyPrefix') . $key, $step);
    }
}
