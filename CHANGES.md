変更点一覧
==========

2.6.0
-----

-   Ethna 2.5.0 preview5 に含まれていて、Ethna 2.5.0
    に含まれなかった変更点について、CHANGES の整理 (多少重複します)
    -   2.6.0 の変更点一覧が、preview5
        からの差分となっていたため、preview5 -\> (元)preview6 での
        fix事項等はCHANGESから削除

### features

Ethna本体に関する変更点
:   -   [Breaking B.C] PHP 5.3 対応のための変更 (B.C. PHP 4
        非対応となります)
        -   非推奨シンタックスの除去 (Remove DEPRECATED syntax)
        -   不要な参照渡し、new演算子の参照代入の除去
        -   アクセス修飾子、static修飾子の導入(一部)
        -   コンストラクタメソッド名の変更(クラス名から\_\_construct()へ)
    -   命名規則の変更
        -   class/ 以下のクラスついて、命名規則を変更しました
            (ファイル名がフルクラス名ではなくなりました)
    -   skeleton 関係
        -   デフォルトで生成されるレイアウトテンプレートの調整
        -   cssの変更
        -   UrlHandler と .htaccess (mod\_rewrite)
            を利用するためのひな形を生成
    -   セッションハンドラのなど，セッションに関する設定の変更をするための記述を
        APPID-ini.php にできるようになりました．
    -   本体テスト環境のsimpletest 1.1系への対応

DB に関する変更点
:   -   Creole 削除: メンテナンスされていないため、Creole
        は以後サポートしません。
    -   これまではADOdbのみで使われていたDSNのパースを、公式スペックとしました(PEAR\_DBには直接渡されていたため)。ただし、このパーサが使われるかどうかは各DBドライバに依存します。

UrlHandler に関する変更点
:   -   path\_regexp が定義されている場合、path
        の定義は必須ではなくなりました (sf\#19237)
    -   UrlHandler\_Simple という軽量 UrlHandler を同梱しました (thx.
        riaf \#17 on GitHub)

Renderer/View に関する変更点
:   -   Smarty3 追加
    -   Rhaco 削除:
        rhacoテンプレートレンダラは以後サポートしません(いつのrhacoのバージョンで動くのかもわかりませんでした)
    -   Ethna\_ActionClass から、Ethna\_ViewClass\#preforward
        に引数を渡せるようにした
        -   return array('forward\_name', \$params);
            の形式で渡せば、\$params が preforwardの引数として渡される
    -   汎用ビュークラスを実装
        -   ビューへの出力時によく使われる処理を雛形として実装したもの
        -   Ethna\_View\_Json.php
        -   Ethna\_View\_403.php
        -   Ethna\_View\_404.php
        -   Ethna\_View\_500.php
        -   Ethna\_View\_Redirect.php
            -   アクションクラスで return array('redirect',
                '<http://example.com>'); とすれば <http://example.com>
                にリダイレクトされる
    -   レイアウトテンプレートを実装
        -   HTMLの外側に当たる雛形のテンプレートを描くためのもの。各アクションの出力はこのテンプレートの出力でラップされる
        -   デフォルトは template/{locale\_name}/layout.tpl
            に置かれている。
        -   この機能はデフォルトで有効になっている。無効にしたければ、[appid]\_ViewClass.php
            の \$use\_layout を false にする(既存プロジェクトをEthna
            2.6に移行する場合、こうすれば動作するはず)
    -   PROJECT\_DIR/lib/Ethna/extlib/Plugin/Smarty
        をデフォルトでSmartyプラグインディレクトリに指定するように，skel
        に追加
    -   renderer の設定を config に書けるようになりました
        (一部、かつ、実装は renderer 依存)
        -   Smarty2 の場合 'smarty', Smarty3 の場合 'smarty3'
            をキーとした配列に、left/right delimiter
            の設定を記述できます
        -   'path' として、include するファイルの path
            を指定できるようになりました
        -   Ethna Info は、Smarty2 を利用するため、Smarty3
            を使う場合でも Ethna Info を見るみは Smarty2 が必要です
    -   Ethna\_Renderer の仕様変更 (Breaking B.C.)
        -   レンダラとしての Ethna\_Renderer の仕様変更
            -   Smarty 以外にも実は PHP などで利用できる Ethna\_Renderer
                でしたが、以下のように仕様を変更しました。
            -   テンプレートは何度でも render 可能になりました (これまで
                include\_once だったので1度しか render できませんでした)
            -   setProp() された変数(assignされた変数) は、\$assign名
                でアクセスできるようになりました。
        -   レンダラエンジンの親クラスとしての Ethna\_Renderer (Renderer
            プラグイン開発者向け情報)
            -   今後エンジンは getName()
                を実装し、エンジン名を返す必要があります
            -   Renderer の \$config プロパティには、iniで定義された配列
                \$config の、\$config['renderer'][エンジン名] が入ります

プラグイン機構に関する変更点
:   -   Ethna\_Plugin::import
        という，プラグインソースをincludeするための，staticメソッドを追加．
    -   すべてのPluginの基底となる抽象クラス，Ethna\_Plugin\_Abstractを追加
        -   既存のプラグインの親クラスを，Ethna\_Plugin\_Abstract
            を継承するように変更
        -   Plugin に設定を受け渡す方法を変更したため，etcのskelを変更。
        -   また、それに伴い，Ethna\_Plugin\_Cachemanager\_Memcacheの設定方法を変更
    -   Ethna\_Plugin\_Cachemanager に config からデフォルト の
        namespace を指定可能とした
    -   pecl::memcached 版に対応した
        Ethna\_Plugin\_Cachemanager\_Memcached のバンドル
    -   [Breaking B.C] プラグインに関する変更
    -   [Breaking B.C]
        プラグインから名前空間を除去することで、複数アプリケーションでの利用を可能に
        -   検索用のアプリケーションIDを削除した
        -   ファイル名の命名規則を変更
        -   extlibの設置
    -   プラグイン関連のethnaコマンドを整理し、インストール、アンインストール関連コマンドは
        ethna pear-local コマンドに一本化
        -   channel-update (削除)
        -   info-plugin (削除)
        -   install-plugin (削除)
        -   uninstall-plugin (削除)
        -   upgrade-plugin (削除)
        -   list-plugin (削除)
    -   プラグインパッケージのスケルトンを生成するコマンドとして ethna
        create-plugin コマンドを追加
        -   複数のtypeのプラグイン同時作成が可能に
        -   Ethnaプロジェクト内でのプラグインの自動生成が可能に
        -   ethna make-plugin-package との連動が可能に
    -   ethna create-plugin コマンドの出力から ethna make-plugin-package
        を実行できるようにコマンドを再実装
        -   これにより、複数のプラグインを含んだパッケージの作成が可能に
    -   Debugtoolbar同梱
        (extlibのサンプルとして。本体に取り込むほどのクオリティでもないためこちらに追加)

その他の変更
:   -   Config に URL
        が設定されていない場合、アクセスされたURLから自動的に検出されるようになりました。(Ethna\_Util::getUrlFromRequestUri())

### bug fix

-   ethna make-plugin-package
    のデフォルトインストールディレクトリが誤っていたバグを修正
-   Ethna\_Plugin::includePlugin
    メソッドの実装が動作するものではなかったので変更
-   Ethna\_Plugin\_Cachemanager のクラスのプロパティに指定する
    \$namespace が意味をなしていなかったので修正 (\#17753)
-   PROJECT\_DIR/lib/Ethna/extlib
    以下にファイルを設置するタイプのプラグインを pear-local
    などでインストールすると、それ以後ethnaコマンドが使えなくなる問題を修正
-   新しいプラグインの命名規則に従っていない古いプラグインを別物として読み込もうとしてクラス名がかぶる問題を修正(\#17875)
    thanks: id:okonomi
-   checkMailAddress
    [でメールアドレスの@以前に/が含まれる場合にvalidationに引っかかる問題を修正](mailto:でメールアドレスの@以前に/が含まれる場合にvalidationに引っかかる問題を修正)
    (\#3 thx. DQNEO) <https://github.com/ethna/ethna/pull/3>
-   setFormDef\_PreHelper() 内で \$this-\>af
    がセットされていない問題の修正 (\#4 thx. DQNEO)
    <https://github.com/ethna/ethna/pull/4>

### beta1 .. beta2

-   require のパスを修正 (thx. seiya,
    <https://github.com/sotarok/ethna/issues/#issue/1>)

### beta3 .. beta4

-   Ethna\_DB\_PEAR のバグ修正 (thx. polidog, \#40)
-   clear-cache コマンドのバグ修正 (thx. ucchee, \#41)
-   Ethna\_Plugin\_CacheManager\_Memcache の修正。
    -   delete コマンド
    -   複数サーバのバランシングができていなかった件を修正 (thx. DQNEO
        \#30)
-   Ethna\_DB\_ADOdb のエラーハンドリング, Ethna\_DB\_\*
    の実装・コメントの修正
    -   thx. ryuzo98 \#38, DQNEO \#48
-   UnitTestCase が動作しなかったバグを修正
-   Debugtoolbar を debug が on の時のみ動作するように修正
-   その他テストの追加、アクセス修飾子の修正など (thx. okonomi)

2.5.0
-----

### features

- フォーム定義に関する変更
    -  フォーム定義を動的に変更するためのAPIをさらに追加
    -  Ethna_ActionForm#setFormDef_ViewHelper
- APPID_Controller.php のスケルトンに継承を想定したメソッドを追加
    -  skel/app.controller.php _setDefaultTemplateEngin
- add-project 時の www 以下に出来るエントリポイントから APPID_Controller へのパスを相対パスに変更
- ethna コマンドの挙動変更
    -  add-project -b オプションの挙動変更
    -  ethna help コマンドを追加
    -  Filterは一貫してプラグインを使うように変更したため、add-project時の app/filter ディレクトリを削除。
- 指定 Action が存在しない場合、app/action 以下を全て include する仕様を変更
- controller での smarty_xx_plugin の機能を削除
    -  フォームヘルパのテキストエリアに value 属性を付加していた動きを修正。(thanks: syachi5150)
        -   http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16326
    -  [Breaking B.C] ルールがユーザにとって直感的ではないとの理由から、フォーム定義の max と フォームヘルパの maxlength の連携機能を削除 (thanks: syachi5150)
        -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16325
    -  Windowsユーザへの便宜のため、zipアーカイブで成果物を配布するオプションを追加
- 組み込みの Smarty プラグインの追加
        -  modifier_explode (文字列を，ある文字で分割して配列に変換する）
- 国際化に関する変更
    -  デフォルトのタイムゾーンとして、date.timezone を 'Asia/Tokyo' に設定
    -  Ethna_I18N クラス に setTimeZone メソッドを追加 (static呼出)
- Ethna_MailSender にて、メール送信に問題がある場合の設定として 'mail_func_workaround' を追加
    -  この値を true に設定すると、メールヘッダの改行コードを一律 CRLF にする処理を回避する
    -  $mail = new Ethna_MailSender(); $mail->setOption(array('mail_func_workaround')); でも設定可能
- Smarty の設定（現在はデリミタのみ）を [appid]-ini.php に書くことが出来るようにした

### bug fix

- Ethna_Controller#getTemplatedir を無視してテンプレートディレクトリを決定していたバグを修正(thanks: hiko)
    -  getTemplatedirメソッドをオーバライドしても強制的にロケールが付加されていた
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=15570
- "ethna pear-local list -a" の実行結果がエラーになってしまうバグを修正
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=15760
- safe-mode が ON の際に、CacheManager_Localfile がディレクトリを生成できないので、tmp ディレクトリ直下にキャッシュファイルを作成するようにした
    -  skel/skel.app_manager.php も修正
- APPID-ini.php が存在しない場合，またはURLが設定にない場合，デフォルトURLが HTTP_HOST で設定されていたが，末尾に / がなかったので修正
- フォームヘルパで自動的に出力されるhiddenタグの閉じ忘れを修正(thanks: id:syachi5150)
- ethna add-app-manager コマンドで生成されるファイル名およびクラス名が間違っていたバグを修正(thanks: id:syachi5150)
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16137
- Validatorが出力するメッセージからフォーム名の後ろのスペースを削るように修正。(thanks: id:syachi5150)
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16336
- Smarty 拡張プラグインの wordwrap_i18n にアルファベットのみを渡した場合に正しい結果が返らないバグを修正
    -  末尾のスペースを取り除く挙動も wordwrap に合わせて削除
    -  http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16839
- ethna add-test コマンドのヘルプが機能していなかったバグを修正
- 存在しない(or 削除された) ethnaコマンドを指定すると Fatal Error が起きるバグを修正 (thanks:kondo_)
    -  http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=17894
- Ethna_Plugin_Logwriter の debug_backtrace の一部が取得できず、E_NOTICE が出るバグを修正 (thanks: http://www.remix.gr.jp/)
- cli 環境で Ethna_Session::start を叩いたときに $_SERVER 変数がないために E_NOTICE が出る問題を修正
- PHP 5.3.0 で新設された E_DEPRECATED を ON にすると Fatal Error が起きるバグを修正 (#18418)
- iniディレクティブ date.timezone が設定されてないために、E_WARNING が PHP 5.3.0 で出ていたバグを修正
- Smartyのデリミタを変更している場合にi18nコマンドが機能しないバグを修正 (#18668)
- formタグのname属性が設定できなくなっていたバグを修正 (thanks: shutta) (#19037)
- Ethna_Session#isAnonymous メソッドが状態を正しく取得できない場合があるバグを修正(thanks:longkey1)
    -  http://ml.ethna.jp/pipermail/users/2008-February/000899.html
- Ethna_ActionForm::setDef に渡す値によっては、空キーにフォーム定義が入ってしまうバグを修正。(thanks:tohokuaki #18856)

2.5.0-preview5
--------------

### features

- フォーム定義に関する変更
    -  フォーム定義を動的に変更するためのAPIをさらに追加
    -  Ethna_ActionForm#setFormDef_ViewHelper
- APPID_Controller.php のスケルトンに継承を想定したメソッドを追加
    -  skel/app.controller.php _setDefaultTemplateEngin
- add-project 時の www 以下に出来るエントリポイントから APPID_Controller へのパスを相対パスに変更
- ethna コマンドの挙動変更
    -  ethna help コマンドを追加
- 指定 Action が存在しない場合、app/action 以下を全て include する仕様を変更
    -  include せず、fallback用のactionを実行する
- add-project -b オプションの挙動変更
- controller での smarty_xx_plugin の機能を削除
- ビューまわりの変更
    -  Ethna_ActionClass から、Ethna_ViewClass#preforward に引数を渡せるようにした
        -  return array('forward_name', $params); の形式で渡せば、$params が preforwardの引数として渡される
    -  汎用ビュークラスを実装
        -  ビューへの出力時によく使われる処理を雛形として実装したもの
        -  Ethna_View_Json.php
        -  Ethna_View_403.php
        -  Ethna_View_404.php
        -  Ethna_View_500.php
        -  Ethna_View_Redirect.php
        - - アクションクラスで return array('redirect', 'http://example.com');
     とすれば http://example.com にリダイレクトされる
    -  レイアウトテンプレートを実装
        -  HTMLの外側に当たる雛形のテンプレートを描くためのもの。各アクションの出力はこのテンプレートの出力でラップされる
        -  デフォルトは template/{locale_name}/layout.tpl に置かれている。
        -  この機能はデフォルトで有効になっている。無効にしたければ、[appid]_ViewClass.php の $use_layout を false にする
    -  フォームヘルパのテキストエリアに value 属性を付加していた動きを修正。(thanks: syachi5150)
        -   http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16326
- [Breaking B.C] プラグインに関する変更
    -  プラグインから名前空間を除去することで、複数アプリケーションでの利用を可能に
    -  検索用のアプリケーションIDを削除した
    -  ファイル名の命名規則を変更
    -  extlibの設置
    -  プラグイン関連のethnaコマンドを整理し、インストール、アンインストール関連コマンドは ethna pear-local コマンドに一本化
        -  ethna channel-update (削除)
        -  ethna info-plugin (削除)
        -  ethna install-plugin (削除)
        -  ethna uninstall-plugin (削除)
        -  ethna upgrade-plugin (削除)
        -  ethna list-plugin (削除)
    -  プラグインパッケージのスケルトンを生成するコマンドとして ethna create-plugin コマンドを追加
        -  複数のtypeのプラグイン同時作成が可能に
        -  Ethnaプロジェクト内でのプラグインの自動生成が可能に
        -  ethna make-plugin-package との連動が可能に
    -  ethna create-plugin コマンドの出力から ethna make-plugin-package を実行できるようにコマンドを再実装
        -  これにより、複数のプラグインを含んだパッケージの作成が可能に
    -  Filterは一貫してプラグインを使うように変更したため、add-project時の app/filter ディレクトリを削除。
- Smartyに関する変更
    -  Smarty を 2.6.26 に追随
    -  組み込みの Smarty プラグインの追加
        -  explode修正子 (文字列を，ある文字で分割して配列に変換する）
- その他雑多な変更
    -  [Breaking B.C] ルールがユーザにとって直感的ではないとの理由から、フォーム定義の max と フォームヘルパの maxlength の連携機能を削除 (thanks: syachi5150)
        -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16325
    -  Windowsユーザへの便宜のため、zipアーカイブで成果物を配布するオプションを追加

### bug fix

- Ethna_Controller#getTemplatedir を無視してテンプレートディレクトリを決定していたバグを修正(thanks: hiko)
    -  getTemplatedirメソッドをオーバライドしても強制的にロケールが付加されていた
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=15570
- "ethna pear-local list -a" の実行結果がエラーになってしまうバグを修正
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=15760
- safe-mode が ON の際に、CacheManager_Localfile がディレクトリを生成できないので、tmp ディレクトリ直下にキャッシュファイルを作成するようにした
    -  skel/skel.app_manager.php も修正
- APPID-ini.php が存在しない場合，またはURLが設定にない場合，デフォルトURLが HTTP_HOST で設定されていたが，末尾に / がなかったので修正
- フォームヘルパで自動的に出力されるhiddenタグの閉じ忘れを修正(thanks: id:syachi5150)
- ethna add-app-manager コマンドで生成されるファイル名およびクラス名が間違っていたバグを修正(thanks: id:syachi5150)
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16137
- Validatorが出力するメッセージからフォーム名の後ろのスペースを削るように修正。(thanks: id:syachi5150)
    -  https://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16336
- Smarty 拡張プラグインの wordwrap_i18n にアルファベットのみを渡した場合に正しい結果が返らないバグを修正
    -  末尾のスペースを取り除く挙動も wordwrap に合わせて削除
    -  http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16839
- Ethna_Session#isAnonymous メソッドが状態を正しく取得できない場合があるバグを修正(thanks:longkey1)
    -  http://ml.ethna.jp/pipermail/users/2008-February/000899.html
- ethna add-test コマンドのヘルプが機能していなかったバグを修正

2.5.0-preview4
--------------

### bug fix

- フォーム定義が配列で、Ethna_ActionForm#getHiddenVars の値を Ethna_ActionForm#setAppNE した場合、クロスサイトスクリプティング
脆弱性が存在するバグを修正 (thanks: shuitic)
    -  http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=17332

2.5.0-preview3
--------------

### features

- アクションフォームに関する変更
    -  フォーム定義を多次元配列に対応させました (thanks: id:syachi5150)
        -  http://d.hatena.ne.jp/syachi5150/20081022/1224676038
    -  フォーム定義を「'def' => array(),」 と定義しなくても、「'def',」 と定義するだけで親のフォームテンプレートの定義を補うようにした (thanks: sotarok)
    -  フォーム定義を動的に変更するためのAPIを追加
        -  Ethna_ActionForm#setFormDef_PreHelper
    Ethna_Backend や Ethna_Session が初期化後に呼ばれる
- フォームヘルパに関する変更
    -  1つのテンプレートに 複数 {form} が指定されたときに、submitされたformに対してのみ補正処理が働くように改善 この場合、{form name=...} 属性の指定が必須
    -  1つのテンプレートに 複数 {form} が置かれた場合に、それぞれのフォームの配列を区別するようにした
- Smarty プラグインに関する変更
    -  Ethna 組み込みの Smarty プラグインを分割
        -  Ethna 組み込みの Smarty プラグインとして class/Plugin/Smarty/ に Smarty のプラグイン形式で個別に作成
        -  それに伴い Ethna_Smarty_Plugin クラスは削除
        -  読み込み順は次のように指定 1. Controller の plugin ディレクトリ 2. Ethna 組み込みの Plugin/Smarty/ ディレクトリ 3. samrty デフォルトのプラグイン
    -  デフォルトの smarty プラグイン よりも Controller の plugins ディレクトリに定義されたプラグインを優先させるように変更
    -  アプリケーション独自のSmarty Pluginの定義場所を app/plugin/Smarty にできるようデフォルトでディレクトリの作成、コントローラに値のセットするよう変更
- その他雑多な変更
    -  Smarty を 2.6.22 に追随
    -  アプリケーションの最終処理を行うメソッドとして、Ethna_Controller#end を追加
    -  フィルタを一貫してプラグインから取得するように変更

### bug fix

- safe-mode が ON の際に、Ethna_View_Test がエラーを吐く現象を回避 (thanks:longkey1 [ethna-users:1059])
- "ethna add-view" コマンドにて、locale 及び client encoding のデフォルト設定が誤っていたバグを修正
- Ethna_Renderer_Rhaco.php を 1.x 系の最新バージョン 1.6.1 に追随 (thanks: id:akiraneko [ethna-users:1081])
- 複数ファイルをアップロード(つまり配列を使用)する際、必須チェックが機能しなかったバグを修正(thanks: id:syachi5150)
- ethna add-app-manager コマンドで生成されるアプリケーションマネージャのクラス名が、[Appid]_Controller#getManagerClassName の設定を反映するように修正。
- smarty_modifier_unique プラグインが、仕様通り動作していなかったバグを修正
- Ethna_PearWrapper のエラー処理が誤っていたのを修正 (thanks: id:nazo)
    -  http://wassr.jp/user/nazo/statuses/SkfJTckkN2
- Ethna_ActionForm#getHiddenVars メソッドで、フォーム定義が配列で設定された値がスカラーの場合に警告が出ていたのを修正(thanks: maru_cc)
    -  逆に、フォーム定義がスカラーで値が配列の場合は救いようがないので警告扱い
- www/info.php を実行したり、www/unittest.php を実行すると、サーバが応答しなくなることがあるバグを修正
    -  アクションクラスの書き方によっては、Ethna_InfoManager が 無限ループに陥っていたため
    -  http://sourceforge.jp/tracker/index.php?func=detail&aid=10006&group_id=1343&atid=5092

2.5.0-preview2
--------------

### features

- PEAR依存を排除するための変更。依存を排除する理由は以下の通り。
  1. PEAR が PEAR2 に移行するに伴い、APIが不安定になること
  2. Ethna が依存している PEAR_Error は既に非推奨であること
  3. 外部ライブラリにできうる限り依存しない方がユーザの便宜となる
  4. PEAR に依存していると、PHPライセンスと抵触しているライセンスで配布できない
    -  Console_Getopt の代替として、Ethna_Getopt.php を追加 (Public Domain)
    -  性質上依存せざるを得ない以下のファイルを除き、Console_Getopt への依存を排除
        -  ETHNA_BASE/bin/ethna_make_package.php
        -  ETHNA_BASE/class/Ethna_PearWrapper.php
    -  [Breaking B.C] Ethna から PEAR_Error まわりの依存を排除。これに伴い、Ethnaクラス が持っていた PEARコアコンポーネンツ の機能は使えなくなっている。
        -  Ethnaクラス に PEAR ライクなエラーチェックメソッドを追加し、それに伴う変更
        -  Ethna_Error で PEAR を呼び出していた部分を修正し、PEARに任せていたメンバ設定等を最実装
        -  PEAR.php で定義されていた OS_WINDOWS 定数の代替として、 ETHNA_OS_WINDOWS 定数を定義した
    これは PEAR が、OS_WINDOWS 定数が再定義されているかをチェックしていないため
- 国際化メッセージの生成支援機構として、i18n コマンドを実装
    -  gettext, Ethna組み込みのメッセージカタログに対応
    -  ethna i18n [-b|    - basedir=dir] [-l|    - locale] [-g|    - gettext] [extdir1] [extdir2] ...
    -  メッセージファイルが存在する場合は、Ethna 組み込みのメッセージカタログの場合は、既存の翻訳
   を自動的にマージする。gettext の場合は、新たにファイルを生成し、msgmerge プログラムを使って
   翻訳を既存のものとマージするように促す
- 配布する Smarty を 2.6.20 に追随
- [Breaking B.C] 互換性を保つために残されていた内部メソッドを削除
    -  Ethna_ViewClass#_getTemplateEngine
- Ethna_ActionClass のメンバに $logger(Ethna_Logger) を追加
- Ethna_ViewClass のメンバに $ctl(Ethna_Controller) を追加
    -  i18n 周りの情報を容易に変更させるようにするため
- Ethna_Controller#_setLanguage メソッドを、backend, Session, actionform の初期化が終わってから呼ぶようにした。
- 2.5.0 preview1 で追加した Ethna_ViewClass#_setLanguage メソッドを削除
    -  アクション実行後のロケール変更はあまり意味がないため :(

### bug fix

- テストディレクトリの変更のタイミングによっては、Ethna_UnitTestMangerがWARNINGを出す問題を回避 (thanks: maru_cc)
- selected="selected" の修正漏れを修正 (thanks:maru_cc)
- [Breaking B.C] Ethna_Plugin_CacheManager_Memcache の接続デフォルトが persistent になっていたのを通常接続に変更
    -  [appid]/etc/[appid]-ini.php の memcache_use_connect 設定を memcache_use_pconnect に変更
- プラグインのクラス名にアンダーバーを許していなかったが、PHPのクラス名的に正当な文字であればOKにするように変更(thanks:maru_cc)
- Ethna_I18N.php で、メッセージをパースする際に空行を見逃していたバグを修正
- Ethna_MailSender にてメールを送信する際、テンプレートが存在しなかった場合にも空メールを送ってしまうバグを修正 (thanks: ryosuke@sekido.info -> [ethna-users:1053])
- smarty_modifier_checkbox が仕様に反する動作をしていたバグを修正し、仕様を厳密化した(thanks: maru_cc)
    -  checked が付くのはスカラーで、0 と空文字列、null, false 以外の場合とする
- Ethna_ActionError#_getActionForm で、E_NOTICE が出る問題を回避

2.5.0-preview1
--------------

### features

- ソースコード全体をUTF-8化
    -  但し、日本語のソースコードコメントはそのまま
    -  [Breaking B.C] フレームワークで扱う内部エンコーディング(mb_internal_encoding)もデフォルトはUTF-8に変更。但し、これは Ethna_Controller#_getDefaultLanguage
   をオーバーライドし、クライアントエンコーディングの値を変えることで変更可能です。
    -  内部エンコーディングの変更に伴い、動作しなくなった箇所を修正
        -  Ethna_Plugin_Validator_Min.php
        -  Ethna_Plugin_Validator_Max.php
        -  VAR_TYPE_STRING の場合の、最大値最小値のプラグインを再編し、
    マルチバイトのものとそうでないものを分離。互換性確保用途のプラグインも追加
        - - Ethna_Plugin_Validator_MbStrMax.php     (マルチバイト文字列最大値)
        - - Ethna_Plugin_Validator_MbStrMin.php     (マルチバイト文字列最小値)
        - - Ethna_Plugin_Validator_StrMax.php       (シングルバイト文字列最大値)
        - - Ethna_Plugin_Validator_StrMin.php       (シングルバイト文字列最小値)
        - - Ethna_Plugin_Validator_StrMaxCompat.php (2.3.x までの互換性確保用)
        - - Ethna_Plugin_Validator_StrMinCompat.php (2.3.x までの互換性確保用)
    -  内部エンコーディングの変更に伴う動作の変更
        -  Ethna_Plugin_Validator_Mbregexp のデフォルトのエンコーディングは、クライアントエンコーディングが仮定されます。デフォルトはUTF-8です。
- 国際化 (i18n) のための機能追加および変更
    -  [Breaking B.C] 言語名として解釈していた部分をロケール名に変更
        -  これにより、[appid]template/ja, [appid]/locale/ja の「ja」の部分が ja_JP に置き換わります。よって、古いバージョンから移行する場合はディレクトリ名の変更が必要です。
        -  Ethna_ViewClass に、言語切り替え用の _setLanguage メソッドを追加 (protected)
        -  Ethna.php で定義されていた、LANG_JA, LANG_EN はこの変更により使用されないので削除
    -  [Breaking B.C] gettext を使用する際には [appid]/etc/[appid]-ini.php で 'use_gettext' => true と設定しないと gettext を使わないようにした
        -  2.3.5 までのコードは、gettext.so がロードされていれば *無条件に* gettext が実行されるようになっているので、Ethna 独自のメッセージカタログとの選択がわかりづらいため。
        -  2.3.5までのコードで gettext を利用している場合は、設定が明示的に必要です。
    -  "ethna add-project" コマンドに [-l|    - locale] [-e|    - encoding] オプションを追加
    -  "ethna add-[view|templete]" コマンドに [-l|    - locale] [-e|    - encoding] オプションを追加
    -  スケルトンの日本語コメントをすべてASCIIに変更(好みのエンコーディングで編集できるようにするため)
    -  gettextを使わない場合向けに、Ethna独自のメッセージカタログを実装
        -  ini ファイルライクなフォーマットで msgid と翻訳を格納する方式
        -  Ethna_I18N#setLanguage で出力ロケールの切り替えも可能
- [Breaking B.C] レンタルサーバを考慮して、[appid]_Controllerの include_path を、[appid]/lib を優先するように変更
    -  include_path の順番に依存するコードは少ないとは思いますが、移行の際は注意すべきです。
- "ethna add-project" コマンドに [-s|skeldir] オプションを追加
    -  指定されたスケルトンディレクトリに、ETHNA_HOME/skel と同じファイル名のものが存在する場合はそちら
   を優先した上で、ETHNA_HOME/skel にないファイルは [appid]/skel にコピーする
- [Breaking B.C] Ethna_ActionForm のバリデータは、プラグインのものしか使用しなくなりました。
    -  Ethna_ActionForm, [Appid]ActionForm の use_validator_plugin 変数を削除

### bug fixes

- tpl/info.tpl のタグミスを修正
- smarty_modifier_plugin が配列の場合に、プラグインとして登録されないバグを修正
- フォームヘルパでセレクトボックスの配列フォームを作ると値が保持されない点を修正 (ethna-users:0868)
- smarty_modifier_select の戻り値が、諸々のHTML標準と異なっていたバグを修正(thanks: maru_cc)
    -  selected="true" -> selected="selected"
- アプリケーションIDの始めの文字に数値を許していたバグを修正
    -  クラス名のprefixになるため、数値を許すと自動生成物がコンパイルエラーを起こす
- Ethna_Util#getRandom で open_basedir が有効な場合に、 /proc を開けず警告が出る点を回避(thanks. sotarok)
    -  http://d.hatena.ne.jp/sotarok/20070813/1187055110
- Ethna_ClassFactory#getManager の第1引数を、大文字小文字を区別しないように修正。(thanks:maru_cc)
    -  第1引数はクラス名の一部として扱われており、PHPがクラス名の大文字小文字を区別しないことから、
   大文字小文字を区別せず同じインスタンスを返すのが妥当と考えられる。
- Ethna_Plugin_LogWriter クラスにて、バックトレース走査時の軽微なバグを修正(ethna-users:1024, thanks:sfio)
- Ethna_Config.php にて、設定ファイルのロックが機能していなかったバグを修正

2.3.7 [#ca10ecac]
-----------------

### bug fix [#qb2a22c6]

- フォーム定義が配列で、Ethna_ActionForm#getHiddenVars の値を Ethna_ActionForm#setAppNE した場合、クロスサイトスクリプティング
脆弱性が存在するバグを修正 (thanks: shuitic)
    -  [[http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=17332:http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=17332]]

2.3.6 [#w8dda865]
-----------------

### features [#f44940f9]

- レンタルサーバを考慮して、[appid]_Controllerの include_path を、[appid]/lib を優先するように変更

### bug fixes [#mf615558]

- 2.5.0 preview3からのバックポート
    -  複数ファイルをアップロード(つまり配列を使用)する際、必須チェックが機能しなかったバグを修正(thanks: id:syachi5150)
        -  このバグは重大なので全ての安定版ユーザはアップデートを推奨
    -  プラグインを使用しない場合に、required_num の場合について、ファイルの場合は1つ入力されていたらvalidとされていたのを、
プラグインの動作に合わせて一応修正
        -  この点は通常ユーザには影響しない。プラグインを使用するのがデフォルトだから。
    -  Ethna_Renderer_Rhaco.php を 1.x 系の最新バージョン 1.6.1 に追随 (thanks: id:akiraneko [ethna-users:1081])
    -  smarty_modifier_unique プラグインが、仕様通り動作していなかったバグを修正
    -  Ethna_ActionForm#getHiddenVars メソッドで、フォーム定義が配列で設定された値がスカラーの場合に警告が出ていたのを修正(t
hanks: maru_cc)
        -  逆に、フォーム定義がスカラーで値が配列の場合は救いようがないので警告扱い
    -  www/info.php を実行したり、www/unittest.php を実行すると、サーバが応答しなくなることがあるバグを修正
        -  アクションクラスの書き方によっては、Ethna_InfoManager が 無限ループに陥っていたため
        -  http://sourceforge.jp/tracker/index.php?func=detail&aid=10006&group_id=1343&atid=5092
- 2.5.0 preview2からのバックポート
    -  selected="selected" の修正漏れを修正 (thanks:maru_cc)
    -  Ethna_MailSender にてメールを送信する際、テンプレートが存在しなかった場合にも空メールを送ってしまうバグを修正 (thanks
: ryosuke@sekido.info -> [ethna-users:1053])
    -  smarty_modifier_checkbox が仕様に反する動作をしていたバグを修正し、仕様を厳密化した(thanks: maru_cc)
        -  checked が付くのはスカラーで、0 と空文字列、null, false 以外の場合とする
    -  Ethna_ActionError#_getActionForm で、E_NOTICE が出る問題を回避
- 2.5.0 preview1からのバックポート
    -  tpl/info.tpl のタグミスを修正
    -  smarty_modifier_select の戻り値が、諸々のHTML標準と異なっていたバグを修正(thanks: maru_cc)
        -  selected="true" -> selected="selected"
    -  アプリケーションIDの始めの文字に数値を許していたバグを修正
        -  クラス名のprefixになるため、数値を許すと自動生成物がコンパイルエラーを起こす
    -  Ethna_Plugin_LogWriter クラスにて、バックトレース走査時の軽微なバグを修正(ethna-users:1024, thanks:sfio)
    -  Ethna_Config.php にて、設定ファイルのロックが機能していなかったバグを修正
- その他安定版にのみ影響するもの
    -  アクションフォームクラスのスケルトンの一部で、$use_validator_plugin = false となっていたのをデフォルトのtrueに修正
        -  これはプロジェクト作成時の app/action/Index.php にのみ影響する。ユーザはこれを通常は再利用しないと考えられるので、通
常は影響ない

2.3.5
-----

### features

- PEAR チャンネルサーバに ethna/simpletest, ethna/Smarty を追加
    -  インストール後のsimpletest, Smartyのパスで悩む罠を軽減することが目的
    -  pear コマンドで Ethna をインストールするときにこれらを Optional に依存するように設定。既存のインストールを考慮して、required にはしていない。
- Ethnaコマンドに一般的なテストケースコマンドとして add-test コマンドを追加(thanks: BoBpp)
    -  ethna add-test -s [skelname] [name] で実行できます
    -  http://blog.as-roma.com/BoBlog/index.php?itemid=1338
    -  これは自動登録されるため、[appid]_UnitTestManager に定義を追加する必要はありません(thanks: id:okonomi)
        -  http://d.hatena.ne.jp/okonomi/20080408
- Ethna_Renderer_Rhacoを追加(experimental)
- Ethna_DB_ADOdbのdebug時のログ出力をEthnaのLoggerに変更(@see http://d.hatena.ne.jp/sotarok/20071224)
- Ethna add-[|action|view]-test コマンドで生成されるテストケースがデフォルトでfailするように改善
- Ethna のユニットテスト実行時に [appid]/etc/[appid]-ini.php のデバッグ設定がfalseの場合のエラー処理を改善
    -  エラー処理をphpに任せて画面を真っ白にするのではなく、親切なエラーメッセージを表示する
- [action|view] のユニットテスト生成時、対応するアクション(ビュー)スクリプトがない場合は警告を生成するようにした。
- Ethna の add-[action|view] コマンドで、同時にユニットテストを作成できるようにするオプションを追加。
    -  ただし、add-view コマンドで -t を指定した場合は、これらのオプションは無視される。
    -  ethna add-[action|view] add-view [-w|    - with-unittest] [-u|    - unittestskel=file] [action|view]

### bug fixes

- ethna pear-local コマンドで Ethna を [appid]/lib/ にインストールすると、[appid]_Controller.php のinclude_path
  の設定によっては ethnaコマンドが動かなくなるのを回避 (thanks: sotarok)
    -  ethna pear-local コマンドで Ethna を [appid]/lib にインストールしても、[appid]/bin/ethna が使えるようにした。
- 配列のフォームをvalidateする際、値がnullだとフィルタが適用されないバグを修正
- Ethna_Plugin_Cachemanager_Memcache に引数がなかったためにプラグイン呼び出しに失敗していたバグを修正(thanks sfio, ethna-users:0818)
- Ethna_PearWrapper、Ethna_Plugin_Csrf_Session, Ethna_InfoManager 等を微調整(thanks sfio, ethna-users:0825)
- form_input の default 属性が、入力値で上書きできなかったバグを修正(thanks sotarok, ethna-users:0836)
- call_user_func の戻り値がオブジェクトだった場合に、E_NOTICEが出る問題を回避(PHP 4.4限定) [ethna-users:0910]
- ActionForm の validate test の結果が、次のテストに引き継がれてしまうバグを修正(thanks: maru_cc)

2.3.2
-----

### features

- %%[breaking B.C.]%% Ethna_UrlHandler (URLハンドラ) をプラグイン化
    -  Ethna_Plugin_Urlhandler_Default を追加
    -  %% $action_map を App_Urlhandler から App_Plugin_Urlhandler_Default
に移動する必要があります %%
    -  やっぱり戻しました。プラグインを呼び出したいときにApp_UrlHandlerクラスで指定するように変更。
- プラグインのクラスが既に存在する場合は特別にファイルの検索をスキップするようにした。
- Ethna_ViewClass::_getFormInput_* で $separator のデフォルトを '' から "\n" に変更
- Ethna_Controller::_trigger_XMLRPC で $GLOBALS['HTTP_RAW_POST_DATA'] を使わずに 'php://input' を使うように変更
    -  php.ini の設定が不要になりました。
- Ethna_MailSender
    -  $type 引数を $template と rename して、より積極的にテンプレート名と解釈するようにした。
        -  $def を特に指定しなければ ViewClass の forward_name と同様に template/ja/mail/ 以下からテンプレートを探します。
    -  multipart: 2 つ以上の添付、ファイル名を指定した添付に対応しました。
        -  ただしデフォルトの content-type は application/octet-stream でごまかしているのと、日本語ファイル名がてきとうです。
- Ethna_Renderer, Ethna_Renderer_Smarty
    -  perform() の第2引数に $capture フラグを追加
    -  true のときは Smarty 的に display でなく fetch になります。
- Ethna_Util::isRootDir() 追加
- ethna_make_packageで.svnに対応
- Ethna_Plugin_Validator_Mbregexp　追加 (thx: mumumu)
    -  mb_eregを使ったマルチバイト対応正規表現プラグイン
- Ethna_Plugin_Handle_PearLocal　追加
    -  PEARパッケージを各プロジェクト毎に管理できるプラグイン
- View のユニットテストができなくなっていたバグを修正(thx: sfio, ethna-users:0651)

### bug fixes

- raiseError()類の引数が間違っていたのを修正 (thx: sfio)
- プラグインパッケージインストール時に '{$application_id}' が置換されないバグを修正
- add-template が正しく動作していなかったのを修正
- Ethna_ViewClass::_getFormInput_Select で multiple を考慮していなかったのを修正
- Ethna_AppObject::_getSQL_SearchId で救済になってないエラーのスキップを削除
    -  有効な key がないときに、どちらにしろ SQL エラーになってた
- OS_WINDOWSでgetAppController()が無限ループになっていたのを修正
    -  ルートディレクトリ判定に失敗していた
- Console_Getoptなどのアップグレードに対応
    -  php4対応のreference返しがなくなっていたのに伴って発生していたnoticeを回避
- xmlrpcのパラメータがActionFormに渡っていなかったのを修正(#9845)
- file_type の検査 が機能しない問題を修正
- MailSenderでテンプレートファイルを指定しない場合の挙動を修正
- MailSenderのBare LFをCRLFに置換(#9898, ethna-users:0588)
- Smarty の $script 変数の値が、PATH_INFOの値が含まれると潜在的に誤動作するバグを修正(thx: cockok, ethna-users:0687)

2.3.1
-----

### features

- ethnaコマンドで@PHP-BIN@が置換されずに残っている場合(CVS版を使っているときなど)に対応
- デフォルトテンプレートにバージョン番号をこっそり追加

### bug fixes

- Mac/Windowsでpear経由でのインストールに失敗していた問題を解消
    -  すべてのroleをphpにして、ethna.{sh,bat}のみscriptを指定
- Ethna_ViewClass::setPlugin() で $plugin の検証に is_callable を使用 (ethna-users:0507)
- install-plugin が正しく動いていなかったのを修正 (#9582)
- ethna.shでPHPのパスが指定されていなかったのを修正(ethna-users:0508)
- Ethna_AppObjectで'key'の条件にunique_key, multiple_keyが漏れていたのを修正
- Ethna_ViewClassで<label id="foo">となっていたのを<label for="foo">に修正

2.3.0
-----

### features

- ethnaコマンドのハンドラ再編
    -  全般的にgetopt化
        -  "    - basedir" で対象アプリの場所を指定
        -  "    - skelfile" で生成元のスケルトンファイルを指定
    -  全てのgeneratorで "アプリ -> Ethna本体" の順にスケルトンファイルを探すように変更
    -  add-action-cli, add-action-xmlrpcを廃止、add-actionに "    - gateway=www|cli|xmlrpc" を追加
    -  add-entry-point追加
        -  ethna add-entry-point     - gateway=cli foo で bin/foo.php, app/action/Foo.php を生成
    -  pearコマンドを使うハンドラに "    - pearopt" を追加(experimental)
        -  ethna install-plugin -p    - alldeps -p    - force foo bar のように指定する
    -  Ethna_Handle::_getopt()の出力を変更

- misc追加
    -  plugin packagerのサンプル
    -  おまけ: _ethna (zshの補完関数)

- Smarty, PEAR_DBのincludeのタイミングを変更
    -  必要時に Ethna_ClassFactory::_include() を使うようにした。

- Ethna_AppObjectをpostgres, sqliteに簡易対応
    -  1テーブルの1レコードが1オブジェクトに対応するような単純なモデルのみ対応
    -  まだdb typeごとに調整が必要になることがあります。
    -  pgsqlでsequenceに対応
    -  テーブル名、カラム名の自動quoteに対応

- add-* ハンドル機能追加
    -  add-template:     - skelfile オプションで生成元のスケルトンファイルを指定できるようにした

- {form_input}ヘルパー
    -  select, radio, checkboxに対応
    -  選択肢をフォーム定義で指定できるようにした(afのmethod, property, managerなど)
    -  外側の{form}ブロックからaction名, default値を取得できるようにした
    -  フォーム定義からもdefault値を指定できるようにした

- Ethna_Plugin_Handle_{Install,Upgrade}Plugin に     - state オプションを追加
- local のプラグインの prefix を App に変更(app_idの予約語扱い)

- Ethna_Plugin_Handle_ClearCache 追加
    -  現状 smarty, pear, cachemanager_localfile, tmp以下問答無用で削除、のみの対応
- ethna_error_handler() の print 条件を変更
    -  Logwriter プラグイン化に伴う $has_echo 条件のバグを修正
    -  $has_echo に加えて $config->get('debug') を見るようにした
- Ethna_Handle で Ethna_Controller と App_Controller が共存する場合の扱いが混乱していたのを整理
- Ethna_Hanlde に mkdir(), chmod(), purgeDir() を追加
- Cachemanager プラグイン中の PEAR::raiseError() を Ethna::raiseError() に変更
- Ethna_Logger で Ethna_Config オブジェクトの取得に失敗したときの処理を修正
- ethna {install,uninstall,upgrade}-plugin で skel から generate されるファイルの上書き確認を廃止

- Ethna_Plugin_Handle_ListPlugin
    -  パッケージ管理に係わらずプラグインの一覧を表示
    -  パッケージ管理下にあるときはパッケージ名とバージョンを表示
- Ethna_Plugin_Handle_UpgradePlugin, Ethna_Plugin_Handle_ChannelUpdate
    -  プラグインパッケージのupgrade, pear channelのupdateに対応
    -  http://pear.server/get/Package-1.2.3.tgz のようなinstall, upgradeに対応
- PearWrapper, Ethna_Handleでのデフォルトターゲット(localかmasterか)をlocalに変更、統一
- Ethna_Plugin_Handle_{Install,Uninstall,Info,List}Plugin
    -  master, localのハンドラを分けていたのを統合
    -  ダウンロード済みの tgz に対応
    -  Console_GetOpt で     - channel,     - basedir,     - local,     - master のオプションを追加
    -  new PEAR_Error() 時の error handler を callback($ui, 'displayFatalError') に変更

- Ethna_UrlHandlerクラスを追加(ステキurl対応)
- Smartyプラグイン関数smarty_function_url追加
- Ethna_AppObjectからのフォーム定義生成サポート追加
    -  [2006/08/23] 激しくα
- Ethna_ClassFactory::getObject()でクラス定義に無いキーが渡された場合はEthna_AppObject()のキーであると仮定してオブジェクト生成
- アプリケーションスケルトン生成時にアプリケーション固有のActionClass, ActionForm, ViewClassも生成するように変更
- Ethna_SkeltonGeneratorクラスをEthna_Generatorクラスに名称変更
- Ethna_SkeltonGeneratorクラスの各メソッドをプラグイン化
- Ethna_Config::get()で引数を指定しないと全設定を格納した配列を返すように変更
- Ethna_ViewClass::_getTemplateEngine()で設定値を格納した$configテンプレート変数を設定するように変更
- Ethnaのパッケージシステムを追加
    -  ethna用のpear channelからプラグインのパッケージをインストールできるようになります
    -  Ethna_PearWrapper, Ethna_Plugin_Handle_{Install,Info,List,Uninstall}_Plugin_{Master,Local}を追加
    -  local: アプリケーション(プロジェクト)のディレクトリ、master: Ethna本体のあるディレクトリのイメージです
    -  PearWrapperはethnaコマンド(Handle)から呼び出されることが前提
    -  Ethna_SkeltonGeneratorにあったメソッドをEthna_Handleに移動、少し追加

- エラーハンドリング方針を多少変更
    -  @演算子を使ったエラー抑制を廃止

- [breaking B.C.] Ethna_ClassFactoryのリファクタリング
    -  Ethna_Backend::getObject()メソッドを追加しました
    -  これにより、Ethna_Controllerの$classメンバに
 $class = array(
   // ...
   'user' => 'Some_Foo_Bar',
 ),
と記述することで
 $user =& $this->backend->getObject('user');
としてSome_Foo_Barクラスのオブジェクトを取得することが出来ます
    -  クラス定義が見つからない場合は下記の順でファイルを探しに行きます(include_path)
+++ Some_Foo_Bar.php (そのまま)
+++ Foo/Some_Foo_Bar.php (Ethna style)
+++ Foo/Bar.php (Ethna & PEAR style)
+++ Some/Foo/Bar.php (PEAR style)
    -  アプリケーションマネージャの生成もEthna_ClassFactoryで行われます(Ethna_ClassFactory::getManager()が追加されています)
    -  これに伴い、〜2.1.xではコントローラクラスに
 $manager = array(
   'um' => 'User',
 );
のように記述されていると、Ethna_ActionClass、Ethna_ViewClass、Ethna_AppObject、Ethna_*Managerで
 $this->um
としてマネージャオブジェクトにアクセスできていたのですが、この機能が廃止されています(不評なら戻します@preview2)
- Ethna_Plugin_Logwriter_File::begin()でログファイルのパーミッションを設定するように変更
- ハードタブ -> ソフトタブ
- test runnerの追加
- [breaking B.C.] Ethna_Loggerリファクタリング
    -  Ethna_LogWriterのプラグイン化
    -  カンマ区切りでの複数ファシリティサポート
    -  _getLogWriter()クラスをオーバーライドしている方に影響があります(2.3.0以降はPlugin/Logwriter以下にLogwriterクラスを置いて、ファシリティでその名前を指定すれば任意のLogwriterを追加可能です)
- [breaking B.C.] Ethna_Renderer追加
    -  〜2.1.xでは直接扱っいてたテンプレートエンジンオブジェクトをEthna_Rendererクラスでwrapしました
    -  Ethna_Controller::getTemplateEngine()はobsoleteとなりますので今後はEthna_Controller::getRenderer()をご利用ください
    -  Ethna_Controller::_setDefaultTemplateEngine(), Ethna_View::_setDefault(), Ethna_Controller::getTemplateEngine()の引数、戻り値は2.1.xまでのSmartyオブジェクトではなくEthna_Rendererオブジェクトとなります
    -  これに伴い、Ethna_Controller::_setDefaultTemplateEngine(), Ethna_Controller::getTemplateEngine()を利用しているアプリケーションではアップデート時にEthna_Renderer::getEngine()を利用して後方互換性を維持するように変更が必要となります
 e.g.
 $smarty =& $this->controller->getTemplateEngine();
 →
 $renderer =& $this->controller->getTemplateEngine();
 $smarty =& $renderer->getEngine();
- プラグインシステム追加(w/ Ethna_Pluginクラス)
    -  Ethna_Handle, Ethna_CacheManager, Ethna_LogWriterをプラグインシステムに移行
    -  Ethna_ActionFormのバリデータをプラグインシステムに移行(Ethna_ActionForm::use_validator_pluginがtrueのときのみ)
    -  see also
        -  http://ethna.jp/ethna-document-dev_guide-plugin.html
        -  http://ethna.jp/ethna-document-dev_guide-form-validate_with_plugin.html
- ethnaコマンドにアクション名、ビュー名のチェック処理を追加(Ethna_Controller::checkActionName(), Ethna_Controller::checkViewName()を追加)
- Ethna_CacheManager_Memcache(キャッシュマネージャのmemcacheサポート)追加
- Ethna_Sessionにregenerate_idメソッドの追加
- Ethna_Plugin_Csrf(CSRF対策コード)追加



### bug fixes

- [[#9009>http://sourceforge.jp/tracker/index.php?func=detail&aid=9009&group_id=1343&atid=5092]](%s等があるSQLをEchoLoggerでDebugするとWarning)
- アクション定義のform_pathが正しく動作していなかった問題を修正
- コントローラが複数あるときにset_error_handler()が何度も実行されるのを回避
- CacheManager_Localfileの@statでのWARNINGを回避
- Ethna_Plugin_Validator_Customでエラーが2重登録されていたのを修正
- プラグインの親クラスがないときにエラーになっていたのを修正
- Ethna_DB_PEAR, Ethna_AppObjectのWARNINGを回避([ethna-users:0383])
- Windowsでホームディレクトリの.ethnaファイルが参照されない問題を修正
- session_startしていないとrestoreメソッドがうまく動かない問題を修正
- ethnaコマンドにサポートされていないオプションのみを指定して起動した場合(ethna -hなど)にFatal Errorとなる問題を修正
- Ethna_Backend::getDBのNoticeエラーを修正
- キャッシュマネージャのエラーコードが256以上(アプリケーション用)になっていた問題を修正
- ethna add-action-testしたときにファイルがapp/action_cliに生成されてしまう問題を修正
- Ethna_SkeltonGeneratorクラスのtypoを修正(proejct -> project)
- Ethna_ActionFormでプラグインを使わないときにフィルタが機能しないバグを修正


[2006/06/07] 2.1.2
------------------

### bug fixes

- Ethna_Controller::getActionRequest()メソッドのデフォルト状態の振舞いを修正


[2006/06/07] 2.1.1
------------------

### bug fixes

- ethna.batのパスを修正

[2006/06/06] 2.1.0
------------------

### features

- ethnaコマンドのETHNA_HOMEをインストール時に決定するように改善
- Ethna_ActionForm::validate() で多次元配列が渡されたときのnoticeを回避
- Ethna_Backend::setActionForm(), Ethna_Backend::setActionClass()メソッドを追加
- Ethna_FilterのスケルトンにpreActionFilter()/postActionFilter()を追加
- Ethna_AppObject::_getPropDef()にキャッシュ処理を追加
- Ethna_CacheManagerクラスを追加(w/ localfile) - from GREE:)
- Ethna_DB::getDSN()メソッドを追加
- iniファイルのスケルトンにdsnサンプル追加
- add-templateコマンド追加(by nnno)
- add-project時のデフォルトテンプレートデザインを変更
- ethnaコマンドに-v(    - version)オプションを追加
- smarty_modifier_select(), smarty_function_select()の"selected"属性のxhtml対応(selected="true")
- {form_name}, {form_input}プラグイン追加(激しくexperimentalというかongoing)
- Ethna_ViewClassでhelperアクションフォーム対応
    -  Ethna_ViewClass->helper_action_form = array('some_action_name' => null, ...)とすると{form_name}とかで使えます
- [breaking B.C.] Ethna_ActionClassのpreforward()サポート(むかーしのコードにありましたのです)削除
- (ぷち)省エネブロックプラグイン{form}...{/form}追加
    -  ethna_action引数も追加(勝手にhiddenタグ生成)
- Ethna_Controllerに$smarty_block_pluginプロパティを追加
- ethnaコマンドにadd-action-cliを追加
- [breaking B.C.] main_CLIのアクション定義ディレクトリをaction_cliに変更
    -  controllerのdirectoryプロパティに'bin'要素を追加
- ethnaコマンドにadd-app-managerを追加(thanks butatic)
- Ethna_ActionForm リファクタリング (by いちい)
    -  $this->form の省略値補正を setFormVars() からコンストラクタに移動
    -  フォーム値のスカラー/配列チェックを setFormVars() でするように変更
        -  vaildate() する前に setFormVars() でエラー (handleError()) が発生することがあります
    -  フォーム値のスカラー/配列チェックでフォーム値定義と異なる場合は null にする
    -  ファイルデータの再構成を常に行うように変更
    -  フォーム値定義が配列で required, max/min の設定がある場合のバグを修正
    -  _filter_alnum_zentohan() を追加 (mb_convert_kana($value, "a"))
- XMLRPCゲートウェイにfaultCodeサポートを追加
    -  actionでEthna_Error(あるいはPEAR_Error)オブジェクトを返すとエラーを返せます
- XMLRPCゲートウェイサポート追加(experimental)
    -  ethna add-action-xmlrpc [action]でXMLRPCメソッドを追加可能
    -  引数1つとフォーム定義1つが定義順に対応します
    -  ToDo
        -  出力バッファチェック
        -  method not foundなどエラー処理対応
- Ethna_ActionFormクラスのコンストラクタでsetFormVars()を実行しないように変更
- スケルトンに含まれる'your name'をマクロ({$author})に変更(~/.ethna対応)
- なげやり便利関数file_exists_ex(), is_absolute_path()を追加
- SimpleTestとの連携機能を追加(ethnaコマンドにadd-action-test,add-view-testの追加など)
    -  SimpleTestのインストールチェックを追加
- package.xml生成スクリプト改善(ethnaコマンドインストール対応など)
- Haste_ADOdb, Haste_Creoleマージ(from Haste Project by haltさん)
- Ethna_AppObjectクラスのテーブル/プロパティ定義自動生成サポート追加(from generate_app_object originally by 井上さん+haltさん)
- Ethna_Controller::getAppdir()メソッドを追加
- Ethna_Controller::getDBType()の引数がnullだった場合に定義一覧を返すように変更
- ethnaコマンドラインハンドラを追加(+ハンドラをpluggableに+add-viewでテンプレート生成サポート)−please cp bin/ethna to /usr/local/bin or somewhere
 generate_project_skelton.php -> ethna add-project
 generate_action_script.php   -> ethna add-action
 generate_view_script.php     -> ethna add-view
 generate_app_object.php      -> ethna add-app-object
- [breaking B.C.] client_typeを廃止 -> gateway追加
    -  CLIENT_TYPE定数廃止
    -  Ethna_Controller::getClientType(), Ethna_Controller::setClientType()廃止
    -  Ethna_Controller::setCLI()/Ethna_Controller::getCLI() -> obsolete
    -  GATEWAY定数追加(GATEWAY_WWW, GATEWAY_CLI, GATEWAY_XMLRPC, GATEWAY_SOAP)
    -  Ethna_Controller::setGateway()/Ethna_Controller::getGateway()追加
    -  作りかけのAMFゲートウェイサポートを(一旦)廃止
- Ethna_SkeltonGenerator::_checkAppId()をEthna_Controller::checkAppId()に移動
- generate_app_objectを追加
- クラスのメソッドもSmartyFunctionとして登録できるように修正

### bug fixes

- [[#8435>http://sourceforge.jp/tracker/index.php?func=detail&aid=8435&group_id=1343&atid=5092]](Ethna_AppObject prop_def[]['seq']が未設定)
- [[#8079>http://sourceforge.jp/tracker/index.php?func=detail&aid=8079&group_id=1343&atid=5092]](FilterでBackendを呼ぶとActionFormの値が空になる)
- [[#8200>http://sourceforge.jp/tracker/index.php?func=detail&aid=8200&group_id=1343&atid=5092]](PHP5.1.0以降でafのvalidate()で日付チェックが効かない)
- [[#8179>http://sourceforge.jp/tracker/index.php?func=detail&aid=8179&group_id=1343&atid=5092]](getManagerの戻り値が参照渡しになっていない)
- [[#8400>http://sourceforge.jp/tracker/index.php?func=detail&aid=8400&group_id=1343&atid=5092]](AppObject prop_def[]['form_name']がNULL)
- [[#7751>http://sourceforge.jp/tracker/index.php?func=detail&aid=7751&group_id=1343&atid=5092]](SAFE_MODEでmail関数の第５引数があるとWaning)を修正
- [[#8496>http://sourceforge.jp/tracker/index.php?func=detail&aid=8496&group_id=1343&atid=5092]](Ethna_AppObject.php内のtypo)を修正
- [[#8387>http://sourceforge.jp/tracker/index.php?func=detail&aid=8387&group_id=1343&atid=5092]](checkMailaddressやcheckURLでNotice)を修正
- [[#8130>http://sourceforge.jp/tracker/index.php?func=detail&aid=8130&group_id=1343&atid=5092]](Noticeつぶし)を修正
- typo fixed (aleady -> already)
- [[#7717>http://sourceforge.jp/tracker/index.php?func=detail&aid=7717&group_id=1343&atid=5092]](Ethna_AppObject::add()でNotice)を修正
- [[#7664>http://sourceforge.jp/tracker/index.php?func=detail&aid=7664&group_id=1343&atid=5092]](Ethna_AppObjectのバグ)を修正
- [[#7729>http://sourceforge.jp/tracker/index.php?func=detail&aid=7729&group_id=1343&atid=5092]](ethna_infoがFirefoxだとずれる)を修正

- (within beta) ethna_handle.phpが無用にob_end_clean()する問題を修正
- (within beta) ethna add-viewでプロジェクトディレクトリを指定した場合に正しくファイルが生成されない問題を修正
- (within beta) Windows版のethnaコマンドがパッケージからインストールした場合実行できない問題を修正
- (within beta) ActionFormの配列のフォーム値が破壊される問題を修正(by sfioさん)


[2006/01/29] 0.2.0
------------------

### features

- 文字列のmin/maxエラーのデフォルトエラーメッセージを修正
- フォーム値定義にカスタムエラーメッセージを定義できるように変更
- Ethna_Controller::main_CLI()メソッドにフィルタを無効化させるオプションを追加
- Ethna_ActionFormクラスのフォーム値定義をダイナミックに変更出来るように修正
- Ethna_ActionFormクラスのフォーム値定義にテンプレート機能を追加
- Ethna_Backend::getActionClasss()メソッドの追加(実行中のアクションクラスを取得)
- ~/.ethnaファイルによるユーザ定義スケルトンマクロの追加
- smarty_function_selectに$empty引数を追加
- mb_*の変換元エンコーディングを、EUC-JP固定から内部エンコーディングに変更
- Ethna_Backend::begin()、Ethna_Backend::commit()、Ethna_Backend::rollback()を廃止
- Ethna_Controller::getDB()をEthna_Controller::getDBType()に変更
- Ethna_DBクラスを抽象クラス(扱い)として新たにEthna_DBクラスを実装したEthna_DB_PEARクラスを追加
- Ethna_LogWriterクラスを抽象クラス(扱い)として新たにEthna_LogWriterクラスを実装したEthna_LogWriter_Echo、Ethna_LogWriter_File、Ethna_LogWriter_Syslogクラスを追加
- log_facilityがnullの場合のログ出力クラスをEthna_LogWriter_EchoからEthna_LogWriterに変更(ログ出力なし)
- log_facilityにクラス名を書いた場合はそのクラスをログ出力クラスとして利用するように変更
- Ethna_Filter::preFilter()、Ethna_Filter::postFilter()がEthna_Errorオブジェクトを返した場合は実行を中止するように変更
- Ethna_InfoManagerの設定表示項目を追加
- Ethna_ActionForm::isForceValidatePlus()、Ethna_ActionForm::setForceValidatePlus()メソッドと、$force_validate_plusメンバを追加($force_validate_plusをtrueに設定すると、通常検証でエラーが発生した場合でも_validatePlus()メソッドが実行される−デフォルト:false)
- フォーム値定義のcustom属性にカンマ区切りでの複数メソッドサポートを追加

### bug fixes

- htmlspecialcharsにENT_QUOTESオプションを追加
- Ethna_AppSQLクラスのコンストラクタメソッド名を修正
- [[#7659>http://sourceforge.jp/tracker/index.php?func=detail&aid=7659&group_id=1343&atid=5092]](Ethna_Config.phpでNoticeエラー)を修正
- Ethna_SOAP_ActionForm.phpのtypoを修正
- [[#6616>http://sourceforge.jp/tracker/index.php?func=detail&aid=6616&group_id=1343&atid=5092]](セッションにObjectを格納できない)を修正
- [[#7640>https://sourceforge.jp/tracker/index.php?func=detail&aid=7640&group_id=1343&atid=5092]](機種依存文字のチェックでエラーメッセージが表示されない。)を修正
- [[#6566>https://sourceforge.jp/tracker/index.php?func=detail&aid=6566&group_id=1343&atid=5092]](skel.action.phpのサンプルでtypo)を修正
- [[#7451>https://sourceforge.jp/tracker/index.php?func=detail&aid=7451&group_id=1343&atid=5092]](PHP 5.0.5対応)を修正
- .museum対応
- Ethna_Backendクラスのクラスメンバ多重定義を修正
- BASE定数の影響でコントローラの継承が困難な問題を修正
- Windows環境で定義されていないLOG_LOCAL定数を評価してしまう問題を修正
- [[#6423>http://sourceforge.jp/tracker/index.php?func=detail&aid=6423&group_id=1343&atid=5092]](php-4.4.0で大量のエラーの後、Segv(11))を修正(patch by ramsyさん)
- [[#6074>http://sourceforge.jp/tracker/index.php?func=detail&aid=6074&group_id=1343&atid=5092]](generate_project_skelton.phpの動作異常)を修正
- safe_mode=onの場合にuid/gid warningが発生する(可能性のある)問題を修正
- 不要な参照渡しを削除
- その他細かな修正(elseif -> else if等)
- PATH_SEPARATOR/DIRECTORY_SEPARATORが未定義の場合(PHP 4.1.x等)の問題を修正
- smarty_modifier_wordwrap_i18n()の改行対応
- ユーザ定義フォーム検証メソッドが呼び出されない(ことがある)問題を修正
- マルチカラムプライマリキー利用時にオブジェクトの正当性が正しく判別できない問題を修正
- Ethna_AppObjectのJOIN検索がSQLエラーになる（ことがある）問題を修正
- セッションを復帰させるタイミングを遅延(無限ループする問題を修正)
- Ethna_MalSenderからmail()関数にオプションを渡せるように修正
- Ethna_View_List::_fixNameObjectに対象オブジェクトも渡すように修正


[2005/03/02] 0.1.5
------------------

### features

- Ethna_Controller::getCLI()(CLIで実行中かどうかを返すメソッド)を追加
- ethna_error_handlerがphp.iniの設定に応じてPHPログも出力するように変更
- Smartyプラグイン(truncate_i18n)を追加
- Ethna_AppObject/Ethna_AppManagerにキャッシュ機構を追加(experimental)
- メールテンプレートエンジンのフックメソッドを追加
- MIMEエンコード用ユーティリティメソッドを追加
- include_pathのセパレータのwin32対応

### bug fixes

- ethna_error_handlerのtypoを修正
- Ethna_Sessionクラスでログが正しく出力されない問題を修正


[2005/01/14] 0.1.4
------------------

### features

- Ethna_AppObjectでJOINした場合に、(可能なら)プライマリキーでGROUP BYするように変更

### bug fixes

- __ethna_info__が全く動作しない問題を修正:(


[2005/01/13] 0.1.3
------------------

### features

- Ethna_AppSearchObjectの複合条件対応
- Ethna_ClassFactoryクラスを追加
- Ethna_Controllerのbackend, i18n, session, action_errorメンバを廃止
- Ethna_Controller::getClass()メソッドを廃止
- Ethna_ActionClassにauthenticateメソッドを追加
- preActionFilter/postActionFilterを追加(experimental)
- Ethna_View_List(リスト表示用ビュー基底クラス)のソート対応
- 組み込みSmarty関数is_error()を追加
- Ethna_ActionForm::handleErrorの第2引数を廃止
- Ethna_ActionForm::_handleErrorをpublicメソッドに変更(Ethna_ActionForm::handleErrorに名称変更)
- Ethna_ActionForm::getDefメソッドに引数を追加(省略可)

### bug fixes

- フォーム定義に配列を指定していた場合のカスタムチェックメソッドの呼び出しが正しく行われない問題を修正
- フォーム定義に配列を指定していた場合の必須チェックが正しく行われない問題を修正
- __ethna_info__がサブディレクトリに定義されたアクションを正しく取得できない問題を修正
- VAR_TYPE_FILEの場合はregexp属性が無効になるように修正


[2004/12/23] 0.1.2
------------------

### features

- __ethna_info__アクションを追加
- class_path, form_path, view_path属性のフルパス指定サポートを追加
- スクリプトを1ファイルにまとめるツール(bin/unify_script.php)を追加

### bug fixes

- プロジェクトスケルトン生成時にアプリケーションIDの文字種/予約語をチェックするように修正
- 'form_name'を指定すると無用に警告が発生する問題を修正
- 絶対パス判定のプラットフォーム依存を修正(Windows対応改善)
- VAR_TYPE_INTとVAR_TYPE_FLOATの定義値が重複していた問題を修正
- SOAP/Mobile(AU)でアクションスクリプトのパスが正しく取得できない問題を修正
- Ethna_Util::getRandom()でmt_srand()しつつrand()を呼んでいた箇所をmt_rand()を呼び出すように修正
- CHANGESのエンコーディング修正(ISO-2022-JP -> EUC-JP)
- フレームワークが発行するSQL文に一部残っていたセミコロンを削除
- エントリポイント(index.php)に記述されたデフォルトアクション名の1要素目にアスタリスクが使用されていると、正しく動作しない(かもしれない)問題を修正~
例(こんな場合):
 <?php
 include_once('../../app/Sample_Controller.php');
 Sample_Controller::Main('Sample_Controller', array(
  'login*',
 ));
 ?>


[2004/12/10] 0.1.1
------------------

### bug fixes

- ビューオブジェクトのpreforward()が呼ばれないことがある問題を修正
- アクション/ビューのスケルトン生成時にファイルを上書きしないように修正
- ビューのスケルトンでクラス名が正しく置換されない問題を修正

[2004/12/09] 0.1.0
------------------

- 初期リリース
