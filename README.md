oryzias
=======

oryziasはだいたい概ね1000行(27KB)ぐらいのフルスタックなPHPマイクロフレームワークです。

##FAQ

+ フォームクラスやヘルパーは無いの？
 + ありません。自分で作るか、Symfony\Component\Formを持ってくるか、Railsを使うと良いと思います。さっきフルスタックと言ったな。あれは嘘だ。

+ クエリービルダやORMは無いの？
 + ありません。自分で作るか、Doctrineを持ってくるか、Railsを使うと良いと思います。SQLの息遣いを肌で感じてほしい。

+ ユニットテストのサポート
 + ありません。

+ ロガー
 + ありません。

+ configにymlとか使えないの？
 + 使えません。

+ CLIから叩けるの？
 + curlで叩け。

+ 速いの？
 + oryziasを動かすにはPHP5.3.10の周辺がたぶん必要です。新しいPHPを使えば何だって速くなります。

+ どんなプロジェクトで使われているの？
 + [phpMyPresentation](https://github.com/oubakiou/phpMyPresentation)（[presentation.bz](http://presentation.bz/)）で使われています。というかそのために作られました。
