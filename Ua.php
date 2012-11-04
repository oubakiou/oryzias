<?php
namespace Oryzias;

class Ua
{
    public static function isAndroid($ua)
    {
        if (stripos($ua, 'Android') === false) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function isIphone($ua)
    {
        if (stripos($ua, 'iPhone') === false) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function isWindowsPhone($ua)
    {
        if (stripos($ua, 'Windows Phone') === false) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function isSmartPhone($ua)
    {
        if (self::isAndroid($ua) || self::isIphone($ua) || self::isWindowsPhone($ua)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isDocomoFeaturePhone($ua)
    {
        if (!self::isSmartPhone($ua) && (stripos($ua, 'DoCoMo') !== false)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isAuFeaturePhone($ua)
    {
        if (!self::isSmartPhone($ua) && (stripos($ua, 'KDDI') !== false)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isSoftBankFeaturePhone($ua)
    {
        if (!self::isSmartPhone($ua) && (stripos($ua, 'SoftBank') !== false)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isFeaturePhone($ua)
    {
        if (self::isDocomoFeaturePhone($ua) || self::isAuFeaturePhone($ua) || self::isSoftBankFeaturePhone($ua)) {
            return true;
        } else {
            return false;
        }
    }
}
