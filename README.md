Oryzias
=======

Oryziasはだいたい概ね2000行ぐらいの軽量で高速なPHPマイクロフレームワークです。

Oryziasは不真面目なオブジェクト指向に則っています。

端的に言えば、
DAOが返すのはデータオブジェクトではなく配列です。
Imageクラスは画像を表現したものではないのでwidthとかheightみたいなプロパティを持っていません。
クラススコープの変数が使いたいがために作られた、単なる画像に関するメソッドの集まりです。

その代償として少ないコード量と[低フットプリント](http://presentation.bz/img/member/pc/1/26.png)を実現しています。
綺麗なOOPやモデリングを学びたい人はRubyとかなんか別のをお勧めします。
また二人以上の開発者が関わるプロジェクトや、長期的な保守が必要なプロジェクトにも、なんか別のをお勧めします。

##ドキュメント

https://github.com/oubakiou/oryzias/wiki

##FAQ

+ ORMは無いの？
 + ありません。自分で作るか、Doctrineを持ってくるか、Railsを使うと良いと思います。SQLの息遣いを肌で感じてほしい。クエリビルダは追加した。

+ ユニットテストのサポート
 + ありません。

+ CLIから叩けるの？
 + curlで叩け。

+ 速いの？
 + xhprofでの凄くおおざっぱな計測だと、たぶんFuelPHPの10倍ぐらい。ただし機能は1/10未満。

+ どんなプロジェクトで使われているの？
 + [oryzias-blog](https://github.com/oubakiou/oryzias-blog) ([demo](http://blog.presentation.bz))
 + [phpMyPresentation](https://github.com/oubakiou/phpMyPresentation)（[presentation.bz](http://presentation.bz/)）

+ Nginxでの設定例
    
        server{
            
            listen       80;
            server_name  example.com.dev;
            access_log   /var/log/nginx/example.com.dev;
            error_log    /var/log/nginx/example.comz.dev.error;
            
            location / {
                root /home/dev/example.com.dev/public/;
                index index.php index.html;
            }
            
            if (!-e $request_filename) {
                set $is_php "true";
            }
            
            if ($uri ~ "^/(css|img|js)/") {
                set $is_php "false";
            }
            
            if ($uri ~ "^/(robots.txt|favicon.ico)") {
                set $is_php "false";
            }
            
            if ($is_php = "true"){
                rewrite ^/(.+)$ /index.php?path=$1 last;
                break;
            }
            
            location ~ \.php$ {
                root /home/dev/example.com.dev/public/;
                fastcgi_pass   127.0.0.1:9000;
                fastcgi_index  index.php;
                fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
                include        fastcgi_params;
            }
        }
    
