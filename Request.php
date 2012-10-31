<?php
namespace Oryzias;

class Request
{
    public $converters;
    
    public function __construct($outputCharset, $internalCharset)
    {
        $this->g = $this->convert($_GET);
        $this->p = $this->convert($_POST);
        $this->r = $this->convert($_REQUEST);
        $this->f = $_FILES;
        $this->c = $_COOKIE;
        
        //出力HTMLの文字セットと内部文字セットが食い違えばコンバータ追加
        if ($outputCharset != $internalCharset) {
            $this->converters[] = function ($val){
                return Util::rMbConvertEncoding($val, $outputCharset, $internalCharset);
            };
        }
        
    }
    
    protected function convert($arr = [])
    {
        if ($this->converters) {
            foreach ($this->converters as $converter) {
                return $converter($arr);
            }
        } else {
            return $arr;
        }
    }
}
