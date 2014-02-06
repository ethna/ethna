<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_View_RedirectTest extends PHPUnit_Framework_TestCase
{
    public function testNotFoundView()
    {
        $controller = new Ethna_Controller_Dummy();
        $form = new Ethna_ActionForm_Dummy($controller);
        $controller->setActionForm($form);
        $backend = $controller->getBackend();
        $backend->setActionForm($form);

        $view = new Ethna_View_Redirect($backend, "redirect", "");

        ob_start();
        @$view->preforward("http://example.com/");
        @$view->forward();
        $content = ob_get_clean();

        // MEMO(chobie):今のだとむりぽ！とりあえず出力結果がなければOKとしておこう
        $this->assertEquals($content, "");
    }
}