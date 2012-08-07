<?php
namespace Oryzias;
class Image extends File{

    public $allowMimeType = array(
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png',
    );

    public $imageInfo;

    function __construct(){

        if(defined(UPLOAD_IMG_MAX_SIZE)){
            $this->allowFileSize = UPLOAD_IMG_MAX_SIZE;
        }else{
            $this->allowFileSize = 1048576;
        }
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

        if(!$this->setImageInfo($file['tmp_name'])){
            return false;
        }

        if(!$this->isAllowedMimeType($this->imageInfo['mime'])){
            return false;
        }

        $this->fileInfo = $file;
        return true;
    }

    function setImageInfo($tmpFileName){

        if(!$info = getimagesize($tmpFileName)){
            $this->err[] = '画像情報の取得に失敗しました';
            return false;
        }

        $info['width'] = $info[0];
        $info['height'] = $info[1];
        $info['imageTypeCode'] = $info[2];
        $info['imageTag'] = $info[3];

        $this->imageInfo = $info;
        return true;
    }

    function getExt($includeDot=false){
        return image_type_to_extension($this->imageInfo['imageTypeCode'], $includeDot);
    }

    //縦横比を維持して上限を満たすようリサイズして保存
    function saveResizeImage($dstFilePath, $maxWidth, $maxHeight){

        if(!$dstFilePath){
            return false;
        }

        $oldWidth = $this->imageInfo['width'];
        $oldHeight = $this->imageInfo['height'];

        if($this->imageInfo['imageTypeCode'] == IMAGETYPE_JPEG){
            $image = ImageCreateFromJPEG($this->fileInfo['tmp_name']);
        }elseif($this->imageInfo['imageTypeCode'] == IMAGETYPE_GIF){
            $image = ImageCreateFromGIF($this->fileInfo['tmp_name']);
        }elseif($this->imageInfo['imageTypeCode'] == IMAGETYPE_PNG){
            $image = ImageCreateFromPNG($this->fileInfo['tmp_name']);
        }else{
            return false;
        }

        $newWidth = $oldWidth;
        $newHeight = $oldHeight;

        //横長
        if($oldWidth > $oldHeight){
            //上限を超えた横幅の時
            if($oldWidth > $maxWidth){
                $newWidth = $maxWidth;
                $newHeight = ceil($oldHeight*($newWidth/$oldWidth));
            }
        }
        //縦長
        else{
            //上限を超えた縦幅の時
            if($oldHeight > $maxHeight){
                $newHeight = $maxHeight;
                $newWidth = ceil($oldWidth*($newHeight/$oldHeight));
            }
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        ImageCopyResized($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

        if($this->imageInfo['imageTypeCode'] == IMAGETYPE_JPEG){
            ImageJPEG($newImage, $dstFilePath, 100);
        }elseif($this->imageInfo['imageTypeCode'] == IMAGETYPE_GIF){
            ImageGIF($newImage, $dstFilePath);
        }elseif($this->imageInfo['imageTypeCode'] == IMAGETYPE_PNG){
            ImagePNG($newImage, $dstFilePath);
        }

        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }

}