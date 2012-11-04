<?php
namespace Oryzias;

class Template
{
    public $config;
    public $params;
    public $unEscapedParams;
    public $templateName;
    public $postFilters;
    public $form;
    
    public function __call($name, $arguments)
    {
        //テンプレートプラグインの呼び出し
        return call_user_func_array([$this->templatePlugin, $name], $arguments);
    }
    
    public function __construct($config)
    {
        $this->config       = $config;
        $this->params       = [];
        $this->postFilters  = [];
        $this->templatePlugin = new TemplatePlugin($config['outputCharset']);
        $this->form = new Form($config['outputCharset']);
        
        //テンプレートの文字セットと出力したい文字セットが食い違えばフィルター追加
        if ($this->config['templateCharset'] != $this->config['outputCharset']) {
            $this->postFilters[] = function ($output){
                return mb_convert_encoding($output, $this->config['outputCharset'], $this->config['templateCharset']);
            };
        }
    }
    
    public function setTemplate($templateName)
    {
        $this->templateName = $templateName;
    }
    
    public function getTemplate()
    {
        return $this->templateName;
    }
    
    public function assign($paramName, $paramValue)
    {
        $this->params[$paramName] = $paramValue;
    }
    
    public function unEscapedAssign($paramName, $paramValue)
    {
        $this->unEscapedParams[$paramName] = $paramValue;
    }
    
    public function fetch()
    {
      if (!$this->templateName) {
          return false;
      }
      $output =  $this->execute($this->getConvertedTemplate($this->templateName));
      if ($this->postFilters) {
          foreach ($this->postFilters as $postFilter) {
              $output = $postFilter($output);
          }
      }
      return $output;
    }
    
    public function view()
    {
        echo $this->fetch();
    }
    
    protected function getConvertedTemplate($templateName)
    {
        if (is_array($templateName)) {
            $templateName = $templateName[1];
        }
        
        $text = '';
        
        if (!$this->config['templateCacheDisable']) {
            $cacheFilePath = $this->config['templateCacheDir'] . '/' . urlencode($templateName) . '.php';
            if (file_exists($cacheFilePath)) {
                $text = file_get_contents($cacheFilePath);
            }
        }
        
        if (!$text) {
            $text = file_get_contents($this->config['templateDir'] . '/' . $templateName . '.html');
            $text = $this->convert($text);
            
            if (!$this->config['templateCacheDisable']) {
                file_put_contents($cacheFilePath, $text);
            }
        }
        
        return $text;
    }
    
    protected function convert($text)
    {
        $text = preg_replace_callback('/\{\[include\((.*?)\)\]\}/', [$this, 'getConvertedTemplate'], $text);
        $text = preg_replace('/\{\[\$(.*?)\]\}/', '<?php if(isset(\$$1)): echo \$$1; endif; ?>', $text);
        
        $text = str_replace('{[', '<?php ', $text);
        $text = str_replace(']}', ' ?>', $text);
        return $text;
    }
    
    protected function execute($convertedTemplate)
    {
        if (isset($this->params)) {
            extract(Util::h($this->params, ENT_QUOTES, Config::get('internalCharset'), true));
        }
        if (isset($this->unEscapedParams)) {
            extract($this->unEscapedParams);
        }
        
        ob_start();
        eval('?>'.$convertedTemplate);
        $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
    }
}
