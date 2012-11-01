<?php
namespace Oryzias;

class Config
{
    //カンマ区切りでパスを指定して設定取得
    public static function get($path)
    {
        static $mergedConfig;
        if (!$mergedConfig) {
            $parentConfig = require('Config/common.php');
            $childConfig = require('Config/' . $_SERVER['SERVER_NAME'] . '.php');
            $mergedConfig = self::merge($parentConfig, $childConfig);
        }
        return self::findByPath($mergedConfig, $path);
    }
    
    //多次元配列からカンマ区切りのパスと対応した要素を返す
    protected static function findByPath($arr, $path)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($arr[$key])) {
                return false;
            }
            $arr = $arr[$key];
        }
        return $arr;
    }
    
    //キーを基準に子の設定ファイルで親を上書き
    protected static function merge($parentConfig, $childConfig)
    {
        if (is_array($childConfig)) {
            foreach ($childConfig as $k=>$v) {
                if (!isset($parentConfig[$k])) {
                    $parentConfig[$k] = [];
                }
                $parentConfig[$k] = self::merge($parentConfig[$k], $childConfig[$k]);
            }
        } else {
            return $childConfig;
        }
        return $parentConfig;
    }
}
