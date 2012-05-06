<?php
namespace Oryzias;
class Request{

    function __construct(){
        $this->g = $this->conv($_GET);
        $this->p = $this->conv($_POST);
        $this->r = $this->conv($_REQUEST);
        $this->f = $this->conv($_FILES);
        $this->c = $this->conv($_COOKIE);
    }

    protected function conv($arr = array()){
        return $arr;
    }

    static function r_mb_convert_encoding($arr, $to_encoding, $from_encoding){
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $arr[$k] = $this->r_mb_convert_encoding($v, $to_encoding, $from_encoding);
            }
        }else{
            return mb_convert_encoding($str, $to_encoding, $from_encoding);
        }
        return $arr;
    }
}