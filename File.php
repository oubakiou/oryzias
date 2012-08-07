<?php
namespace Oryzias;
class File{

    public $fileInfo;
    public $allowFileSize;
    public $allowMimeType = array();
    public $err;

    function __construct(){

        if(defined(UPLOAD_FILE_MAX_SIZE)){
            $this->allowFileSize = UPLOAD_FILE_MAX_SIZE;
        }else{
            $this->allowFileSize = 1048576;
        }
    }

    function getError(){
        return $this->err;
    }

    function checkFile($file){

        if(!$this->isUploadedFile($file['tmp_name'])){
            return false;
        }

        if(!$this->isAllowedSize($file['size'])){
            return false;
        }

        if(!$this->isNormalStatusCode($file['error'])){
            return false;
        }

        if(!$this->isAllowedMimeType($file['type'])){
            return false;
        }

        $this->fileInfo = $file;
        return true;
    }

    protected function isAllowedMimeType($mimeType){

        if(!count($this->allowMimeType)){
            return true;
        }

        if(in_array($mimeType, $this->allowMimeType)){
            return true;
        }else{
            $this->err[] = '許可されたファイルタイプではありません';
            return false;
        }
    }

    protected function isUploadedFile($tmpFileName){
        if(is_uploaded_file($tmpFileName)){
            return true;
        }else{
            $this->err[] = '不正なファイルです';
            return false;
        }
    }

    protected function isAllowedSize($size){
        if($this->allowFileSize >= $size){
            return true;
        }else{
            $this->err[] = '上限サイズ(' . ceil($this->allowFileSize/1024) . 'KB)を越えています';
            return false;
        }
    }

    protected function isNormalStatusCode($status){
        if($status == UPLOAD_ERR_OK){
            return true;
        }else{
            $this->err[] = 'アップロードに失敗しました';
            return false;
        }
    }
}