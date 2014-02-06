<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Controller_Dummy extends Ethna_Controller
{
    protected $directory= array(
        // Memo(chobie): 設計上先に設定ないと死ぬ
        "plugin" => __ETHNA_PLUGIN_DIR,
    );
    public $class = array(
        "renderer" => "Ethna_Renderer_DummyRenderer",
    );
}
