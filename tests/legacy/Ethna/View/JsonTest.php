<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_View_JsonTest extends PHPUnit_Framework_TestCase
{
    public function testNotFoundView()
    {
        $controller = new Ethna_Controller_Dummy();
        $form = new Ethna_ActionForm_Dummy($controller);
        $controller->setActionForm($form);
        $backend = $controller->getBackend();
        $backend->setActionForm($form);

        $view = new Ethna_View_Json($backend, "dummy", "");

        ob_start();
        $param = array("a", "あいうえ");
        @$view->preforward($param);
        @$view->forward();
        $content = ob_get_clean();

        $this->assertEquals($content, '["a","\u3042\u3044\u3046\u3048"]');
    }

    function test_preforward_non_utf8()
    {
        $controller = new Ethna_Controller_Dummy();
        $form = new Ethna_ActionForm_Dummy($controller);
        $controller->setActionForm($form);
        $backend = $controller->getBackend();
        $backend->setActionForm($form);

        $view = new Ethna_View_Json($backend, "dummy", "");
        $controller->setClientEncoding('EUC-JP');

        ob_start();
        $param = array("a", "あいうえ");
        mb_convert_variables('EUC-JP', 'UTF-8', $param);
        @$view->preforward($param);
        @$view->forward();
        $content = ob_get_clean();

        $this->assertEquals($content, '["a","\u3042\u3044\u3046\u3048"]');
    }
}
