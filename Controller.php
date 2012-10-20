<?php
namespace Oryzias;
Abstract class Controller{

    //trueでhttp接続のみ可
    protected $httpOnly;

    //trueでhttps接続のみ可
    protected $httpsOnly;

    protected $template;
    protected $req;

    public function __construct(){
        $this->init();
        $this->exec();
        $this->view();
    }

    public function __destruct(){
        $_SESSION = $this->s;
    }

    public function __get($name){

    	$token = explode('_', $name);
    	
        //コントローラ内で$this->db_HogeでModel_Db_Hogeを取得
        if($token[0] == 'Db'){
            if(count($token) == 3){
                $dbConnectionKey = 'default';
                $tableName = $token[2];
                $className = 'Model_Db_' . $tableName;
            }else if(count($token) == 4){
                $dbConnectionKey = $token[2];
                $tableName = $token[3];
                $className = 'Model_Db_' . $dbConnectionKey . $tableName;
            }
            
            if(!isset($this->$name)){
                $this->$name = new $className($dbConnectionKey, Config::get('db.' . $dbConnectionKey));
            }
            return $this->$name;
        }
        
        //コントローラ内で$this->vld_HogeでModel_Validator_Hogeを取得
        if($token[0] == 'Vld'){
            $className = 'Model_Validator_' . substr($name, 4);
        }
        //コントローラ内で$this->mdl_HogeでModel_Hogeを取得
        elseif($token[0] == 'Mdl'){
            $className = 'Model_' . substr($name, 4);
        }

        if(isset($className)){
            if(!isset($this->$name)){
                $this->$name = new $className;
            }
            return $this->$name;
        }
    }

    protected function init(){
        
        $this->setTimezone();
        $this->checkScheme();
        $this->session();
        
        if(!isset($this->template)){
            $templateConfig = Config::get('template');
            //スマホが別テンプレートかつUAがスマホなら設定上書き
            if($this->isSmartPhone() && ($templateSp = Config::get('templateSp')) ){
                $templateConfig = $templateSp;
            }
            //ガラケーが別テンプレートかつUAがガラケーなら設定上書き
            elseif($this->isFeaturePhone() && ($templateFp = Config::get('templateFp')) ){
                $templateConfig = $templateFp;
            }
            $this->template = new Template($templateConfig);
        }
        
        if(!isset($this->req)){
            $this->req = new Request();
        }
        
        $this->g = $this->req->g;
        $this->p = $this->req->p;
        $this->r = $this->req->r;
        $this->f = $this->req->f;
        $this->c = $this->req->c;
        $this->ua = $_SERVER['HTTP_USER_AGENT'];
        $this->s = $_SESSION;

        if(!isset($this->s['csrfToken'])){
            $this->s['csrfToken'] = sha1(mt_rand());
        }
    }
    
    protected function setTimezone(){
        //タイムゾーン設定
        if(!$timeZone = Config::get('timeZone')){
            $timeZone = 'Asia/Tokyo';
        }
        date_default_timezone_set($timeZone);
    }

    //スキーマ指定があれば不一致時にリダイレクト
    protected function checkScheme(){
        if($this->httpOnly){
            if($_SERVER['HTTPS']){
                $this->r('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }elseif($this->httpsOnly){
            if(!$_SERVER['HTTPS']){
                $this->r('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }
    }

    protected function session(){
        session_start();
    }

    protected function exec(){
    }

    protected function view(){

        //テンプレートの指定が無ければコントローラ名と対応したテンプレートを設定
        if(!$this->template->getTemplate()){
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
        $this->template->view();
    }

    static function r($url, $replace = true, $http_response_code=302){
        header('Location: ' . $url, $replace, $http_response_code);
        exit;
    }

    protected function isAndroid(){
        if(stripos($this->ua, 'Android') === false){
            return false;
        }else{
            return true;
        }
    }

    protected function isIphone(){
        if(stripos($this->ua, 'iPhone') === false){
            return false;
        }else{
            return true;
        }
    }

    protected function isWindowsPhone(){
        if(stripos($this->ua, 'Windows Phone') === false){
            return false;
        }else{
            return true;
        }
    }

    protected  function isSmartPhone(){
        if($this->isAndroid() || $this->isIphone() || $this->isWindowsPhone()){
            return true;
        }else{
            return false;
        }
    }
    
    protected function isDocomoFeaturePhone(){
        if(stripos($this->ua, 'DoCoMo') === false){
            return false;
        }else{
            return true;
        }
    }
    
    protected function isAuFeaturePhone(){
        if(stripos($this->ua, 'KDDI') === false){
            return false;
        }else{
            return true;
        }
    }
    
    protected function isSoftBankFeaturePhone(){
        if(stripos($this->ua, 'SoftBank') === false){
            return false;
        }else{
            return true;
        }
    }
    
    protected  function isFeaturePhone(){
        if($this->isDocomoFeaturePhone() || $this->isAuFeaturePhone() || $this->isSoftBankFeaturePhone()){
            return true;
        }else{
            return false;
        }
    }
    
}