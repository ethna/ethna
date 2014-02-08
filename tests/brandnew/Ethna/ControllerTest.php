<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

use Prophecy\PhpUnit\ProphecyTestCase;

/**
 * Ethna_Controller
 *
 * @package Ethna
 */
class Ethna_ControllerTest2 extends ProphecyTestCase
{
    public function testController()
    {
        $controller = new Ethna_Mock_Controller();
        $this->expectOutputString("foo");

        /**
         * NOTE(chobie): 内部で直接作ってるからてすとしづらいよねー
         *
         * [イメージ図]
         *
         * Controller
         *           `Session
         *           `I18n
         *           `Backend
         *                   `ActionForm
         *                   `ActionClass
         *           `View
         *
         * prefilter かける
         *   action とか必要なパラメータつくる
         *   pre action filterかける
         *   backend つくる
         *   session つくって restore
         *   i18nつくって設定
         *   backendでperform
         *     action classつくる
         *     authenticate
         *     pre
         *     performして遷移先を返す
         *   post action filterかける
         *   viewがあればつくる
         *     view::preforward
         *     view::forward
         * postfilter かける
         *
         * ってのがEthna2.xのおおざっぱな処理の流れ。
         * ふつーのひとはAction::performだけ気にしてればいい。
         */
        $this->assertNull($controller->trigger("dummy"));
        echo "foo"; // とりあえず
    }
}
