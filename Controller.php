<?php
namespace Oryzias;
Abstract class Controller{

    //trueでhttp接続のみ可
    protected $http_only;

    //trueでhttps接続のみ可
    protected $https_only;

    protected $template;
    protected $req;

    function __construct(){
        $this->init();
        $this->exec();
        $this->view();
    }

    function __destruct(){
        $_SESSION = $this->s;
    }

    function __get($name){

        //コントローラ内で$this->db_HogeでModel_Db_Hogeを取得
        if(substr($name, 0, 3) == 'Db_'){
            $className = 'Model_Db_' . substr($name, 3);
        }
        //コントローラ内で$this->vld_HogeでModel_Validator_Hogeを取得
        elseif(substr($name, 0, 4) == 'Vld_'){
            $className = 'Model_Validator_' . substr($name, 4);
        }
        //コントローラ内で$this->mdl_HogeでModel_Hogeを取得
        elseif(substr($name, 0, 4) == 'Mdl_'){
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
        $this->checkScheme();
        $this->session();
        if(!isset($this->template)){
            $this->template = new Template();
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

    //スキーマ指定があれば不一致時にリダイレクト
    protected function checkScheme(){
        if($this->http_only){
            if($_SERVER['HTTPS']){
                $this->r('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }elseif($this->https_only){
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

}