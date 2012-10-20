<?php
namespace Oryzias;
class HttpClient{

    private $uaVersion = '0.1';
    public $timeoutSec = 5;
    
    // $urlList = [["url"=>$url, "lastCheckDatetime"=>$datetime],...]
    public function request($urlList){
        
        $result = [];
        
        //マルチハンドラの作成
        $mch = curl_multi_init();
        
        foreach($urlList as $url){
            //curlハンドラの作成
            $ch = curl_init();
            //正しい形式のURLであれば
            if($parsedUrl = parse_url($url["url"])){

                //リクエストヘッダの作成
                $header[] = 'GET ' . $parsedUrl["path"] . '?' . $parsedUrl['query'] . ' HTTP/1.1';
                $header[] = 'Host: ' . $parsedUrl["host"];
                $header[] = 'User-Agent: Oryzias HttpClient - Version ' . $this->uaVersion;

                //指定があればIf-Modified-Sinceヘッダを付ける
                if($lastCheckDatetime = strtotime($url["lastCheckDatetime"])){
                    $header[] = 'If-Modified-Since: '.date("r", $lastCheckDatetime);
                }
                
                $header[] = 'Connection: close';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                unset($header);
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FILETIME, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSec);//5秒でタイムアウト
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
                
                //マルチハンドラに登録
                curl_multi_add_handle($mch, $ch);
                $chList[] = $ch;
            }
        }
        
        if($chList){
        
            //マルチハンドルで並列リクエスト
            $active=null;
            do {curl_multi_exec($mch, $active);} while ($active);
            
            //結果の取得
            foreach($chList as $k=>$ch) {
                $temp['url'] = $status["url"];
                $temp['status'] = curl_getinfo($ch);
                $temp['content'] = curl_multi_getcontent($ch);
                $reuslt[$k] = $temp;
            }
        }

        //マルチハンドルからcurlハンドル削除
        foreach($chList as $ch){
            curl_multi_remove_handle($mch, $ch);
            curl_close($ch);
        }
        
        //マルチハンドル閉じ
        curl_multi_close($mch);
        
        return $result;
    }
}