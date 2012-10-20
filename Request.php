<?php
namespace Oryzias;
class Request{

    public function __construct(){
        $this->g = $this->conv($_GET);
        $this->p = $this->conv($_POST);
        $this->r = $this->conv($_REQUEST);
        $this->f = $this->conv($_FILES);
        $this->c = $this->conv($_COOKIE);
    }

    protected function conv($arr = array()){
        return $arr;
    }

    static function r_mb_convert_encoding($arr, $toEncoding, $fromEncoding){
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $arr[$k] = $this->r_mb_convert_encoding($v, $toEncoding, $fromEncoding);
            }
        }else{
            return mb_convert_encoding($str, $toEncoding, $fromEncoding);
        }
        return $arr;
    }
}