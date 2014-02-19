# Ethna

[![Build Status](https://travis-ci.org/ethna/ethna.png?branch=master)](https://travis-ci.org/ethna/ethna)

Ethna(えすな)は、PHPを利用したウェブアプリケーションフレームワークで、絶妙に妥協をモットーとしています。

* Web: http://ethna.jp/
* Issues: [Github Issues](https://github.com/ethna/ethna/issues)
* IRC: #Ethna on irc.freenode.net.

Current Status
--------------

2.7.x-dev

Getting Started
---------------

composerのcreate-projectを使うことで簡単に新規プロジェクトの作成が行えます。

````
composer create-project ethna/ethna-project -s dev {ProjectName}
````

初期インストールが終わるとプロジェクトのセットアップ用にいくつか入力すると
よしなにやってくれるはずです。

````
php -S localhost:8080 -t www
````

あとは大抵built in serverで開発できると思います。
ethnaコマンドはvendor/bin/ethnaになってるので間違えないよう。

How to update
-------------

create-projectした後にappディレクトリとかの中身をコピペするのが多分すこしは楽です。
が、どちらにしろ大変だと思うのでがんばって！

Requirements
--------------

* PHP 5.3.3 higher

# License

The BSD License

Copyright (c) 2004-2005, Masaki Fujimoto All rights reserved.