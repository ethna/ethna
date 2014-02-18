<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Mock_Controller extends Ethna_Controller
{
    public $directory= array(
        // Memo(chobie): 設計上先に設定ないと死ぬんだよなぁ。
        "plugin" => __ETHNA_PLUGIN_DIR,
    );
}