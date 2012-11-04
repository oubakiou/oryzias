<?php
namespace Oryzias;

class Request
{
    public $converters;
    
    public function __construct($inputCharset, $internalCharset, $converters=[])
    {
        $this->converters = $converters;
        
        //入力の文字セットと内部文字セットが食い違えばコンバータ追加
        if ($inputCharset != $internalCharset) {
            $this->converters[] = function ($val) use ($inputCharset, $internalCharset) {
                return Util::rMbConvertEncoding($val, $internalCharset, $inputCharset);
            };
        }
        
        $this->g = $this->convert($_GET);
        $this->p = $this->convert($_POST);
        $this->r = $this->convert($_REQUEST);
        $this->f = $_FILES;
        $this->c = $_COOKIE;
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
