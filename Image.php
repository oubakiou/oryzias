<?php
namespace Oryzias;

class Image extends File
{
    public $allowMimeType = [
        'image/jpg',
        'image/jpeg',
        'image/gif',
        'image/png',
    ];
    
    public $imageInfo;
    
    public function __construct()
    {
        if ($uploadFileMaxSize = Config::get('image.uploadFileMaxSize')) {
            $this->allowFileSize = $uploadFileMaxSize;
        } else {
            $this->allowFileSize = 1048576;
        }
    }
    
    public function checkFile($file)
    {
        if (!$this->isUploadedFile($file['tmp_name'])) {
            return false;
        }
        
        if (!$this->isAllowedSize($file['size'])) {
            return false;
        }
        
        if (!$this->isNormalStatusCode($file['error'])) {
            return false;
        }
        
        if (!$this->setImageInfo($file['tmp_name'])) {
            return false;
        }
        
        if (!$this->isAllowedMimeType($this->imageInfo['mime'])) {
            return false;
        }
        
        $this->fileInfo = $file;
        return true;
    }
    
    public function setImageInfo($tmpFileName)
    {
        if (!$info = getimagesize($tmpFileName)) {
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
    
    function getExt($includeDot=false)
    {
        return image_type_to_extension($this->imageInfo['imageTypeCode'], $includeDot);
    }
    
    //縦横比を維持して上限を満たすようリサイズして保存
    public function saveResizeImage($dstFilePath, $maxWidth, $maxHeight)
    {
        if (!$dstFilePath) {
            return false;
        }
        
        $oldWidth = $this->imageInfo['width'];
        $oldHeight = $this->imageInfo['height'];
        
        if ($this->imageInfo['imageTypeCode'] == IMAGETYPE_JPEG) {
            $image = ImageCreateFromJPEG($this->fileInfo['tmp_name']);
        } elseif ($this->imageInfo['imageTypeCode'] == IMAGETYPE_GIF){
            $image = ImageCreateFromGIF($this->fileInfo['tmp_name']);
        } elseif ($this->imageInfo['imageTypeCode'] == IMAGETYPE_PNG){
            $image = ImageCreateFromPNG($this->fileInfo['tmp_name']);
        } else {
            return false;
        }
        
        $newWidth = $oldWidth;
        $newHeight = $oldHeight;
        
        if ($oldWidth > $oldHeight) {
            //横長
            if ($oldWidth > $maxWidth) {
                //上限を超えた横幅の時
                $newWidth = $maxWidth;
                $newHeight = ceil($oldHeight * ($newWidth/$oldWidth));
            }
        } else {
            //縦長
            if ($oldHeight > $maxHeight) {
                //上限を超えた縦幅の時
                $newHeight = $maxHeight;
                $newWidth = ceil($oldWidth * ($newHeight/$oldHeight));
            }
        }
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        ImageCopyResized($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
        
        if ($this->imageInfo['imageTypeCode'] == IMAGETYPE_JPEG) {
            ImageJPEG($newImage, $dstFilePath, 100);
        } elseif ($this->imageInfo['imageTypeCode'] == IMAGETYPE_GIF) {
            ImageGIF($newImage, $dstFilePath);
        } elseif ($this->imageInfo['imageTypeCode'] == IMAGETYPE_PNG) {
            ImagePNG($newImage, $dstFilePath);
        }
        
        imagedestroy($image);
        imagedestroy($newImage);
        
        return true;
    }
}
