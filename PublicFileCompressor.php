<?php
namespace Oryzias;

class PublicFileCompressor
{
    public $config;
    
    public function __construct($f, $config = null, $publicDir = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = \Oryzias\Config::get('compressor');
        }
        
        if ($publicDir) {
            $this->publicDir = $publicDir;
        } else {
            $this->publicDir = \Oryzias\Config::get('publicDir');
        }
        
        $ext = $this->getExt($f);
        if ($this->printHeader($ext)) {
            return false;
        }
        
        if ($this->config['cacheDisable']) {
            $output = $this->merge($f);
        } else {
            if ($cache = $this->getCache($ext)) {
                $output = $cache;
            } else {
                $output = $this->merge($f);
                $this->setCache($output, $ext);
            }
        }
    
        if ($this->config['enabledGzip']) {
            ob_start("ob_gzhandler");
        }
        
        exit($output);
    }
    
    public function getExt($f)
    {
        return substr($f, strrpos($f, '.') + 1);
    }
    
    public function printHeader($ext)
    {
        if ($ext == 'js') {
            header('Content-Type: text/javascript');
        } elseif ($ext == 'css') {
            header('Content-Type: text/css');
        } else {
            return false;
        }
    }
    
    public function merge($f)
    {
        $output = '';
        $publicDirLen = strlen($this->publicDir);
        
        $fileNames = explode(',', $f);
        foreach ($fileNames as $fileName) {
            $path = $this->publicDir . '/' . $fileName;
            $realPath = realpath($path);
            if (substr($realPath, 0, $publicDirLen) == $this->publicDir) {
                $output .= file_get_contents($realPath) . "\n\n";
            }
        }
        return $output;
    }
    
    public function getCache($ext)
    {
        $fileName = $this->getCacheFileName($ext);
        if (!file_exists($fileName)) {
            return false;
        }
        return file_get_contents($fileName);
    }
    
    public function setCache($output, $ext)
    {
        return file_put_contents($this->getCacheFileName($ext), $output);
    }
    
    public function getCacheFileName($ext)
    {
        return $this->config[$ext]['cacheDir'] . '/cache.' . $ext;
    }    
}
