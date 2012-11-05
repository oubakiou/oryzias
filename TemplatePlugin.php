<?php
namespace Oryzias;

class TemplatePlugin
{
    public $outputCharset;
    public $echo;
    
    public function __construct($outputCharset = 'UTF-8', $echo = true)
    {
        $this->outputCharset = $outputCharset;
        $this->echo = $echo;
    }
    
    public function output($result)
    {
        if ($this->echo) {
            echo $result;
        } else {
            return $result;
        }
    }
    
    //HTMLエスケープ
    public function h($str)
    {
        $this->output(Util::h($str, ENT_QUOTES, $this->outputCharset));
    }
    
    //指定文字数で切り詰め
    public function truncate($str, $limit, $suffix='..')
    {
        $len = mb_strlen($str, $this->outputCharset);
        if ($len <= $limit) {
            return $this->output($str);
        }
        
        return $this->output(mb_substr($str, 0, $limit, $this->outputCharset) . $suffix);
    }
    
    //日付書式変換
    public function dateFormat($str, $format='Y-m-d')
    {
        return $this->output(date($format, strtotime($str)));
    }
    
    //オートリンク
    public function autoLink($str, $target='')
    {
        return preg_replace('/(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/', '<a href="$1" target="' . $target . '">$1</a>', $str);
    }
}
