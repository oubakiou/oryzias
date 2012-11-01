<?php
namespace Oryzias;

class Util
{
    //再帰的文字コード変換
    public static function rMbConvertEncoding($val, $toEncoding, $fromEncoding)
    {
        if (is_array($val)) {
            foreach ($val as $k=>$v) {
                $val[$k] = self::rMbConvertEncoding($v, $toEncoding, $fromEncoding);
            }
        } else {
            return mb_convert_encoding($val, $toEncoding, $fromEncoding);
        }
        return $val;
    }
    
    //再帰的HTMLエスケープ
    public static function h($val, $flags=ENT_QUOTES, $encoding='UTF-8', $double_encode=true)
    {
        if (is_array($val)) {
            foreach ($val as $k=>$v) {
                $val[$k] = self::h($v, $flags, $encoding, $double_encode);
            }
        } else {
            if (is_scalar($val)) {
                return htmlspecialchars($val, $flags, $encoding, $double_encode);
            } else {
                return $val;
            }
        }
        return $val;
    }
    
    //リダイレクト
    public static function r($url, $replace = true, $http_response_code=302)
    {
        header('Location: ' . $url, $replace, $http_response_code);
        exit;
    }
}
