Ethna
=======
.. image:: http://stillmaintained.com/ethna/ethna.png

Ethna(えすな)は、PHPを利用したウェブアプリケーションフレームワークで、
似たようなコードを書かなくてよいことを目標としています。

Webサイトは http://ethna.jp/ です。

Requirements
--------------

* Ethna 2.5.x

    * PHP 4系、PHP 5.x

* Ethna 2.6.x

    * PHP 5.2.6 以上


インストール
--------------

PEARコマンドでインストールする
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

もっとも簡単で確実な方法です。 ``-a`` オプションをつけてインストールすることで Smarty なども同時にインストール可能です。 ::

    $ sudo pear channel-discover pear.ethna.jp
    $ sudo pear install -a ethna/ethna


その他
^^^^^^^

詳しくは Wiki を参照してください。

* http://ethna.jp/ethna-document-tutorial-install_guide.html


Ethna の情報源
--------------

ドキュメント
^^^^^^^^^^^^^^^

公式ドキュメント:
    http://ethna.jp/ethna-document.html

API ドキュメント:
    http://ethna.jp/doc/

メーリングリスト
^^^^^^^^^^^^^^^^

ユーザ向けメーリングリスト(ethna-users):
    http://ml.ethna.jp/mailman/listinfo/users

Git リポジトリ 更新状況(ethna-cvs):
    http://ml.ethna.jp/mailman/listinfo/cvs

IRC
^^^^^^^

freenode の `#Ethna` でEthnaの使い方や開発について議論しています。コミッタもいますので、バグなどについて文句を言うのはここが一番伝わりやすいかもしれません。

* サーバ: irc.freenode.com
* チャネル: #Ethna

IRCって何? という方は、IRC普及委員会 を参照して下さい。:
    http://irc.nahi.to/

バグ、要望等を報告する方法
--------------------------

Ethna を使っていて、バグや変な挙動を見つけた場合は、開発者に報告をお願いします。報告する手段は以下の通りです。

IRCチャンネル
    freenode の #Ethna。ここが一番伝わりやすいです

GitHub の Issues または Pull Request
    修正したよ、といったものは直接 Pull Requset を送っていただいて構いません。その際、ブランチの運用ルールは `開発について` を参照してください。

ユーザ向けメーリングリスト
    ethna-usersにバグについて投稿する

「Ethna」をキーワードにしてブログを書く
    コミッタは「Ethna」をキーワードにしたブログをブログ検索でウォッチしています。バグや不満や感想等、Ethnaをキーワードにしたブログを書いてみると、コミッタが見て反応する可能性があります。

開発について
-------------

開発用のツール
^^^^^^^^^^^^^^^^

* `gitFlow <https://github.com/nvie/gitflow>`_ を利用しています。また、ブランチ運用ルールも基本的にこれに沿っています。

テスト実行
^^^^^^^^^^^^^^^^

simpletest によるテストの実行は次のようにします。 ::

    $ php bin/ethna_run_test.php

詳細出力 ::

    $ php bin/ethna_run_test.php --verbose

テストを指定して実行 ::

    $ php bin/ethna_run_test.php test/Logger_Test.php

Pull Request
^^^^^^^^^^^^^^^^

バグ修正などの Pull Request など大歓迎です。

* 最新の develop から任意の名前で branch を切り、develop ブランチに対して pull request を送ってください。

    * ブランチを切ってから時間が立ったなどで差分が生じている場合かならず手元で rebase してください

* master ブランチへは直接 merge しません。


branch運用ルール
^^^^^^^^^^^^^^^^

前述の gitFlow の Branching Model に基づいて概ね以下のように運用しています。 (この運用ルールが出来る前のブランチは、必ずしもこの通りのものではありません。ブランチはSVNから移行したものもあります)


master
    最新のリリースのあるブランチです。

develop
    開発中のブランチです。すべてのfeatureブランチは、featureブランチでの開発が完了後developにmergeします。

feature/xxxx
    特定の機能追加、バグ修正のためのブランチです。

release/xxxx
    特定のリリース候補ブランチ。developブランチからreleaseブランチを切り、テストや修正などを行ったあとここからmasterにmergeします。

version/xxxx
    特定のバージョンのリリース後ブランチです。リリース後、修正などでバックポートの必要性が生じた場合など、基本的にこのブランチ上をリリース対象とします。


tagについて
^^^^^^^^^^^^^^^^

基本的に `バージョン名` でタグを切っています。Git移行以前のものは `ETHNA_2_x_x` などといった名前がついているかもしれません。

suffixナシ
    stableリリース
RCX
    RCリリース
betaX
    betaリリース
pX
    preview版


ライセンス
--------------

    The BSD License
    
    Copyright (c) 2004-2011, Masaki Fujimoto
    All rights reserved.
    
    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions
    are met:
    
      - Redistributions of source code must retain the above copyright
        notice, this list of conditions and the following disclaimer. 
      - Redistributions in binary form must reproduce the above
        copyright notice, this list of conditions and the following
        disclaimer in the documentation and/or other materials provided
        with the distribution. 
      - Neither the name of the author nor the names of its contributors
        may be used to endorse or promote products derived from this
        software without specific prior written permission. 
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
    "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
    LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
    A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
    OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
    SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
    LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
    THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
    OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

