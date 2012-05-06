<?php
namespace Oryzias;
class Boot{

    function __construct($path){

        set_include_path(
            get_include_path() . PATH_SEPARATOR .
            realpath(dirname(__FILE__).'/../../'). PATH_SEPARATOR .
            realpath(dirname(__FILE__).'/../../Library/')
        );

        //サーバ名に対応した設定ファイル読込
        include('Config/' . $_SERVER['SERVER_NAME'] . '.php');

        //タイムゾーン設定
        if(defined(TIME_ZONE)){
            $timeZone = TIME_ZONE;
        }else{
            $timeZone = 'Asia/Tokyo';
        }
        date_default_timezone_set($timeZone);

        //オートローダの追加
        spl_autoload_register(__NAMESPACE__ .'\Boot::classAutoLoad');

        $router = new Router($path);
        $controllerName =  $router->getControllerName();
        new $controllerName;
    }

    //PSR-0
    static function classAutoLoad($className){

        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if(stream_resolve_include_path($fileName)){
            require $fileName;
        }
    }

}
