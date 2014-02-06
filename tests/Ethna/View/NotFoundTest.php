<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_View_NotFoundTest extends PHPUnit_Framework_TestCase
{
    public function testNotFoundView()
    {
        $controller = new Ethna_Controller_Dummy();
        $form = new Ethna_ActionForm_Dummy($controller);
        $controller->setActionForm($form);
        $backend = $controller->getBackend();
        $backend->setActionForm($form);

        $view = new Ethna_View_404($backend, "dummy", "");

        ob_start();
        @$view->preforward();
        @$view->forward();
        $content = ob_get_clean();

        $this->assertEquals("error404.tpl", $controller->getRenderer()->templates[0]);
    }
}