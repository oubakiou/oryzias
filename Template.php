<?php
namespace Oryzias;
class Template{

    public $outputCharset;
    public $templateCharset;
    public $templateDir;
    public $templateCacheDir;
    public $templateCacheDisable;
    public $params;
    public $unEscapedParams;
    public $templateName;
    public $postFilters;
    
    public function __construct($templateConfig){
        $this->outputCharset        = $templateConfig['outputCharset'];
        $this->templateCharset      = $templateConfig['templateCharset'];
        $this->templateDir          = $templateConfig['templateDir'];
        $this->templateCacheDir     = $templateConfig['templateCacheDir'];
        $this->templateCacheDisable = $templateConfig['templateCacheDisable'];
        $this->params               = array();
        $this->postFilters          = array();
        
        //テンプレートの文字セットと出力したい文字セットが食い違えばフィルター追加
        if($this->outputCharset != $this->templateCharset){
            $this->postFilters[] = function($output){
                return mb_convert_encoding($output, $this->outputCharset, $this->templateCharset);
            };
        }
    }

    public function setTemplate($templateName){
        $this->templateName = $templateName;
    }

    public function getTemplate(){
        return $this->templateName;
    }

    public function assign($paramName, $paramValue){
        $this->params[$paramName] = $paramValue;
    }

    public function unEscapedAssign($paramName, $paramValue){
        $this->unEscapedParams[$paramName] = $paramValue;
    }

    public function fetch(){
      if(!$this->templateName){
          return false;
      }
      $output =  $this->execute($this->getConvertedTemplate($this->templateName));
      if($this->postFilters){
          foreach($this->postFilters as $postFilter){
              $output = $postFilter($output);
          }
      }
      return $output;
    }

    public function view(){
        echo $this->fetch();
    }

    protected function getConvertedTemplate($templateName){

        if(is_array($templateName)){
            $templateName = $templateName[1];
        }

        $text = '';

        if(!$this->templateCacheDisable){
            $cacheFilePath = $this->templateCacheDir . '/' . urlencode($templateName) . '.php';
            if(file_exists($cacheFilePath)){
                $text = file_get_contents($cacheFilePath);
            }
        }

        if(!$text){
            $text = file_get_contents($this->templateDir . '/' . $templateName . '.html');
            $text = $this->convert($text);

            if(!$this->templateCacheDisable){
                file_put_contents($cacheFilePath, $text);
            }
        }

        return $text;
    }

    protected function convert($text){
        $text = preg_replace_callback('/\{\[include\((.*?)\)\]\}/', array($this, 'getConvertedTemplate'), $text);
        $text = preg_replace('/\{\[\$(.*?)\]\}/', '<?php if(isset(\$$1)): echo \$$1; endif; ?>', $text);

        $text = str_replace('{[', '<?php ', $text);
        $text = str_replace(']}', ' ?>', $text);
        return $text;
    }

    protected function execute($convertedTemplate){
        if(isset($this->params)){
            extract(self::h($this->params, ENT_QUOTES, $this->outputCharset, true));
        }
        if(isset($this->unEscapedParams)){
            extract($this->unEscapedParams);
        }

        ob_start();
        eval('?>'.$convertedTemplate);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    static public function h($arr, $flags=ENT_QUOTES, $encoding='UTF-8', $double_encode=true){
        
        if(is_array($arr)){
            foreach($arr as $k=>$v){
                $arr[$k] = self::h($v, $flags, $encoding, $double_encode);
            }
        }else{
            if(is_scalar($arr)){
                return htmlspecialchars($arr, $flags, $encoding, $double_encode);
            }else{
                return $arr;
            }
        }
        return $arr;
    }

}