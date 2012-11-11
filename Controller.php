<?php
namespace Oryzias;

Abstract class Controller
{
    //trueでhttp接続のみ可
    protected $httpOnly;
    
    //trueでhttps接続のみ可
    protected $httpsOnly;
    
    protected $template;
    protected $req;
    
    protected $xFrameOptions = 'DENY';
    
    public function __construct()
    {
        $this->init();
        $this->exec();
        $this->view();
    }
    
    public function __destruct()
    {
        $_SESSION = $this->s;
    }
    
    public function __get($name)
    {
        $token = explode('_', $name);
        
        if ($token[0] == 'Db') {
            if (count($token) == 2) {
                //コントローラ内で$this->Db_TableNameでModel_Db_TableNameを取得
                $dbConnectionKey = 'default';
                $tableName = $token[1];
                $className = 'Model_Db_' . $tableName;
            } elseif (count($token) == 3) {
                //コントローラ内で$this->Db_ConnectionName_TableNameでModel_Db_ConnectionName_TableNameを取得
                $dbConnectionKey = $token[1];
                $tableName = $token[2];
                $className = 'Model_Db_' . $dbConnectionKey . '_' . $tableName;
            }
            
            if (!isset($this->$name)) {
                $this->$name = new $className($dbConnectionKey, Config::get('db.' . $dbConnectionKey));
            }
            return $this->$name;
        }
        
        if ($token[0] == 'Vld') {
            //コントローラ内で$this->Vld_ValidatorNameでModel_Validator_ValidatorNameを取得
            $className = 'Model_Validator_' . substr($name, 4);
        } elseif ($token[0] == 'Mdl') {
            //コントローラ内で$this->Mdl_ModelNameでModel_ModelNameを取得
            $className = 'Model_' . substr($name, 4);
        }
        
        if (isset($className)) {
            if (!isset($this->$name)) {
                $this->$name = new $className;
            }
            return $this->$name;
        }
    }
    
    public function __call($name, $arguments)
    {
        if ($name == 'config') {
            return Config::get($arguments[0]);
        }
    }
    
    protected function init()
    {
        $this->setTimezone();
        $this->checkScheme();
        $this->session();
        
        $templateConfig = Config::get('template');
        if (Ua::isSmartPhone($_SERVER['HTTP_USER_AGENT']) && ($templateSp = Config::get('templateSp')) ) {
            //スマホが別テンプレートかつUAがスマホなら設定上書き
            $templateConfig = $templateSp;
        }
        elseif (Ua::isFeaturePhone($_SERVER['HTTP_USER_AGENT']) && ($templateFp = Config::get('templateFp')) ) {
            //ガラケーが別テンプレートかつUAがガラケーなら設定上書き
            $templateConfig = $templateFp;
        }
        $this->template = new Template($templateConfig);
        $this->req = new Request($templateConfig['outputCharset'], Config::get('internalCharset'));
        
        $this->g = $this->req->g;
        $this->p = $this->req->p;
        $this->r = $this->req->r;
        $this->f = $this->req->f;
        $this->c = $this->req->c;
        $this->ua = $_SERVER['HTTP_USER_AGENT'];
        $this->s = $_SESSION;
        
        if (!isset($this->s['csrfToken'])) {
            $this->s['csrfToken'] = sha1(mt_rand());
        }
    }
    
    protected function setTimezone()
    {
        //タイムゾーン設定
        if (!$timeZone = Config::get('timeZone')) {
            $timeZone = 'Asia/Tokyo';
        }
        date_default_timezone_set($timeZone);
    }
    
    //スキーマ指定があれば不一致時にリダイレクト
    protected function checkScheme()
    {
        if ($this->httpOnly) {
            if ($_SERVER['HTTPS']) {
                Util::r('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        } elseif($this->httpsOnly) {
            if (!$_SERVER['HTTPS']) {
                Util::r('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }
    }
    
    protected function session()
    {
        session_start();
    }
    
    protected function exec()
    {
    }
    
    protected function view()
    {
        //テンプレートの指定が無ければコントローラ名と対応したテンプレートを設定
        if (!$this->template->getTemplate()) {
            $templateName = str_replace('_', '/', substr(get_class($this), 11));
            $this->template->setTemplate($templateName);
        }
        
        //$_REQUEST、$_COOKIE、$_SESSIONをassign
        $this->template->assign('controllerName', get_class($this));
        $this->template->assign('g', $this->g);
        $this->template->assign('p', $this->p);
        $this->template->assign('r', $this->r);
        $this->template->assign('f', $this->f);
        $this->template->assign('c', $this->c);
        $this->template->assign('s', $this->s);
        
        if ($this->xFrameOptions) {
            header('X-FRAME-OPTIONS: ' . $this->xFrameOptions);
        }
        $this->template->view();
    }
    
    protected function assign()
    {
        $this->template->assign($paramName, $paramValue);
    }
    
    //リダイレクト
    public static function r($url, $replace = true, $httpResponseCode=302)
    {
        header('Location: ' . $url, $replace, $httpResponseCode);
        exit;
    }
}
