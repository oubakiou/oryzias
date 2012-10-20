<?php
namespace Oryzias;
class Validator{

    protected $data;
    protected $inputCharset;
    protected $rules;
    protected $error;
    protected $allowKeys;

    public function __construct($rules=null, $inputCharset=null){
        if(!$inputCharset){
            $inputCharset = Config::get('template.outputCharset');
        }
        $this->inputCharset = $inputCharset;
        $this->setRule($rules);
    }

    public function setData($data){
        if($data){
            $this->data = $data;
        }
    }

    public function getData(){

        if(!$this->allowKeys){
            return $this->data;
        }

        $result = array();
        foreach($this->data as $k=>$v){
            if(in_array($k, $this->allowKeys)){
                $result[$k] = $v;
            }
        }
        return $result;
    }

    public function setRule($rules=null){
        if($rules){
            $this->rules = $rules;
        }
    }

    public function isValid(){
        foreach($this->rules as $elementName=>$rule){
            foreach($rule as $validateName=>$validateParams){

                if(!isset($validateParams['args'])){
                    $validateParams['args'] = array();
                }
                if(!isset($this->data[$elementName])){
                    $this->data[$elementName] = array();
                }

                if(!$this->$validateName($this->data[$elementName], $validateParams['args'])){
                    $this->error[$elementName][] = $validateParams['msg'];
                    $this->error['errorList'][] = $validateParams['msg'];
                }
            }
        }

        if($this->error){
            return false;
        }else{
            return true;
        }
    }

    public function getError(){
        return $this->error;
    }

    public function unsetError(){
        unset($this->error);
    }

    //必須
    public function required($input){
        if($input){
            return true;
        }else{
            return false;
        }
    }

    //数字のみ
    public function numeric($input){
        if(ctype_digit($input)){
            return true;
        }else{
            return false;
        }
    }

    //英数字のみ
    public function alphaNumeric($input){
        if(ctype_alnum($input)){
            return true;
        }else{
            return false;
        }
    }

    //メールアドレス
    public function email($input){
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return true;
        }else{
            return false;
        }
    }

    //URL
    public function url($input){
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return true;
        }else{
            return false;
        }
    }

    //最大文字列長
    public function maxStrLen($input, $args){
        return $this->rangeStrLen($input, $args);
    }

    //最小文字列長
    public function minStrLen($input, $args){
        return $this->rangeStrLen($input, $args);
    }

    //文字列長範囲
    public function rangeStrLen($input, $args){
        $result = true;
        $strlen = mb_strlen($input, $this->inputCharset);

        if(isset($args['min'])){
            if($strlen < $args['min']){
                $result = false;
            }
        }

        if(isset($args['max'])){
            if($strlen > $args['max']){
                $result = false;
            }
        }

        return $result;
    }

    //最大
    public function max($input, $args){
        return range($input, $args);
    }

    //最小
    public function min($input, $args){
        return range($input, $args);
    }

    //範囲
    public function range($input, $args){
        $result = true;

        if(isset($args['min'])){
            if($input < $args['min']){
                $result = false;
            }
        }

        if(isset($args['max'])){
            if($input > $args['max']){
                $result = false;
            }
        }

        return $result;
    }

    //CSRFチェック
    public function checkCsrf($input, $args){
        if($input == $_SESSION['csrfToken']){
            return true;
        }else{
            return false;
        }
    }
}