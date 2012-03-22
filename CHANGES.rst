変更点一覧
==================

2.6.0
---------

* Ethna 2.5.0 preview5 に含まれていて、Ethna 2.5.0 に含まれなかった変更点について、CHANGES の整理 (多少重複します)

  * 2.6.0 の変更点一覧が、preview5 からの差分となっていたため、preview5 -> (元)preview6 での fix事項等はCHANGESから削除


features
^^^^^^^^

Ethna本体に関する変更点
  * [Breaking B.C] PHP 5.3 対応のための変更 (B.C. PHP 4 非対応となります)

    * 非推奨シンタックスの除去 (Remove DEPRECATED syntax)
    * 不要な参照渡し、new演算子の参照代入の除去
    * アクセス修飾子、static修飾子の導入(一部)
    * コンストラクタメソッド名の変更(クラス名から__construct()へ)

  * 命名規則の変更

    * class/ 以下のクラスついて、命名規則を変更しました (ファイル名がフルクラス名ではなくなりました)

  * skeleton 関係

    * デフォルトで生成されるレイアウトテンプレートの調整
    * cssの変更
    * UrlHandler と .htaccess (mod_rewrite) を利用するためのひな形を生成

  * セッションハンドラのなど，セッションに関する設定の変更をするための記述を APPID-ini.php にできるようになりました．
  * 本体テスト環境のsimpletest 1.1系への対応

DB に関する変更点
  * Creole 削除: メンテナンスされていないため、Creole は以後サポートしません。
  * これまではADOdbのみで使われていたDSNのパースを、公式スペックとしました(PEAR_DBには直接渡されていたため)。ただし、このパーサが使われるかどうかは各DBドライバに依存します。

UrlHandler に関する変更点
  * path_regexp が定義されている場合、path の定義は必須ではなくなりました (sf#19237)
  * UrlHandler_Simple という軽量 UrlHandler を同梱しました (thx. riaf #17 on GitHub)

Renderer/View に関する変更点
  * Smarty3 追加
  * Rhaco 削除: rhacoテンプレートレンダラは以後サポートしません(いつのrhacoのバージョンで動くのかもわかりませんでした)
  * Ethna_ActionClass から、Ethna_ViewClass#preforward に引数を渡せるようにした

    * return array('forward_name', $params); の形式で渡せば、$params が preforwardの引数として渡される

  * 汎用ビュークラスを実装

    * ビューへの出力時によく使われる処理を雛形として実装したもの
    * Ethna_View_Json.php
    * Ethna_View_403.php
    * Ethna_View_404.php
    * Ethna_View_500.php
    * Ethna_View_Redirect.php

      * アクションクラスで return array('redirect', 'http://example.com'); とすれば http://example.com にリダイレクトされる

  * レイアウトテンプレートを実装

    * HTMLの外側に当たる雛形のテンプレートを描くためのもの。各アクションの出力はこのテンプレートの出力でラップされる
    * デフォルトは template/{locale_name}/layout.tpl に置かれている。
    * この機能はデフォルトで有効になっている。無効にしたければ、[appid]_ViewClass.php の $use_layout を false にする(既存プロジェクトをEthna 2.6に移行する場合、こうすれば動作するはず)

  * PROJECT_DIR/lib/Ethna/extlib/Plugin/Smarty  をデフォルトでSmartyプラグインディレクトリに指定するように，skel に追加
  * renderer の設定を config に書けるようになりました (一部、かつ、実装は renderer 依存)

    * Smarty2 の場合 'smarty', Smarty3 の場合 'smarty3' をキーとした配列に、left/right delimiter の設定を記述できます
    * 'path' として、include するファイルの path を指定できるようになりました
    * Ethna Info は、Smarty2 を利用するため、Smarty3 を使う場合でも Ethna Info を見るみは Smarty2 が必要です

  * Ethna_Renderer の仕様変更 (Breaking B.C.)

    * レンダラとしての Ethna_Renderer の仕様変更

      * Smarty 以外にも実は PHP などで利用できる Ethna_Renderer でしたが、以下のように仕様を変更しました。
      * テンプレートは何度でも render 可能になりました (これまで include_once だったので1度しか render できませんでした)
      * setProp() された変数(assignされた変数) は、$assign名 でアクセスできるようになりました。

    * レンダラエンジンの親クラスとしての Ethna_Renderer (Renderer プラグイン開発者向け情報)

      * 今後エンジンは getName() を実装し、エンジン名を返す必要があります
      * Renderer の $config プロパティには、iniで定義された配列 $config の、$config['renderer'][エンジン名] が入ります

プラグイン機構に関する変更点
  * Ethna_Plugin::import という，プラグインソースをincludeするための，staticメソッドを追加．
  * すべてのPluginの基底となる抽象クラス，Ethna_Plugin_Abstractを追加

    * 既存のプラグインの親クラスを，Ethna_Plugin_Abstract を継承するように変更
    * Plugin に設定を受け渡す方法を変更したため，etcのskelを変更。
    * また、それに伴い，Ethna_Plugin_Cachemanager_Memcacheの設定方法を変更

  * Ethna_Plugin_Cachemanager に config からデフォルト の namespace を指定可能とした
  * pecl::memcached 版に対応した Ethna_Plugin_Cachemanager_Memcached のバンドル

  * [Breaking B.C] プラグインに関する変更
  * [Breaking B.C] プラグインから名前空間を除去することで、複数アプリケーションでの利用を可能に

    * 検索用のアプリケーションIDを削除した
    * ファイル名の命名規則を変更
    * extlibの設置

  * プラグイン関連のethnaコマンドを整理し、インストール、アンインストール関連コマンドは ethna pear-local コマンドに一本化

    * channel-update (削除)
    * info-plugin (削除)
    * install-plugin (削除)
    * uninstall-plugin (削除)
    * upgrade-plugin (削除)
    * list-plugin (削除)

  * プラグインパッケージのスケルトンを生成するコマンドとして ethna create-plugin コマンドを追加

    * 複数のtypeのプラグイン同時作成が可能に
    * Ethnaプロジェクト内でのプラグインの自動生成が可能に
    * ethna make-plugin-package との連動が可能に

  * ethna create-plugin コマンドの出力から ethna make-plugin-package を実行できるようにコマンドを再実装

    * これにより、複数のプラグインを含んだパッケージの作成が可能に

  * Debugtoolbar同梱 (extlibのサンプルとして。本体に取り込むほどのクオリティでもないためこちらに追加)

その他の変更
  * Config に URL が設定されていない場合、アクセスされたURLから自動的に検出されるようになりました。(Ethna_Util::getUrlFromRequestUri())


bug fix
^^^^^^^

* ethna make-plugin-package のデフォルトインストールディレクトリが誤っていたバグを修正
* Ethna_Plugin::includePlugin メソッドの実装が動作するものではなかったので変更
* Ethna_Plugin_Cachemanager のクラスのプロパティに指定する $namespace が意味をなしていなかったので修正 (#17753)
* PROJECT_DIR/lib/Ethna/extlib 以下にファイルを設置するタイプのプラグインを pear-local などでインストールすると、それ以後ethnaコマンドが使えなくなる問題を修正
* 新しいプラグインの命名規則に従っていない古いプラグインを別物として読み込もうとしてクラス名がかぶる問題を修正(#17875) thanks: id:okonomi
* checkMailAddress でメールアドレスの@以前に/が含まれる場合にvalidationに引っかかる問題を修正 (#3 thx. DQNEO) https://github.com/ethna/ethna/pull/3
* setFormDef_PreHelper() 内で $this->af がセットされていない問題の修正 (#4 thx. DQNEO) https://github.com/ethna/ethna/pull/4

beta1 .. beta2
^^^^^^^^^^^^^^
* require のパスを修正 (thx. seiya, https://github.com/sotarok/ethna/issues/#issue/1)

beta3 .. beta4
^^^^^^^^^^^^^^
* Ethna_DB_PEAR のバグ修正 (thx. polidog, #40)
* clear-cache コマンドのバグ修正 (thx. ucchee, #41)
* Ethna_Plugin_CacheManager_Memcache の修正。

  * delete コマンド
  * 複数サーバのバランシングができていなかった件を修正 (thx. DQNEO #30)

* Ethna_DB_ADOdb のエラーハンドリング, Ethna_DB_* の実装・コメントの修正

  * thx. ryuzo98 #38, DQNEO #48

* その他テストの追加、アクセし修飾子の修正など (thx. okonomi)
