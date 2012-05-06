<?php
namespace Oryzias;
class Router{

    protected $path;

    function __construct($path){
        $this->setPath($path);
    }

    function setPath($path){

        if(!$path || ($path == 'index.php')){
            $path = 'Index';
        }

        $this->path = $path;
    }

    function getControllerName(){

        if(isset($GLOBALS['ROUTING_RULES'])){
            foreach($GLOBALS['ROUTING_RULES'] as $rule){
                if(preg_match_all($rule['pathPattern'], $this->path, $params, PREG_PATTERN_ORDER)){
                    $this->path = $rule['controllerName'];
                    foreach($rule['paramsName'] as $i=>$paramName){
                        $_REQUEST[$paramName] = $params[($i+1)][0];
                        $_GET[$paramName] = $params[($i+1)][0];
                    }
                }
            }
        }

        $controllerName = 'Controller_' . str_replace('/', '_', $this->path);

        //先頭大文字化
        $controllerName = explode('_', $controllerName);
        foreach($controllerName as $k=>$v){
            $controllerName[$k] = ucfirst($v);
        }
        if(!$controllerName[$k]){
            $controllerName[$k] = 'Index';
        }
        $controllerName = implode('_', $controllerName);

        if(!class_exists($controllerName)){
            $controllerName = 'Controller_404';
        }

        return $controllerName;
    }

}