<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Smarty_Function_ExplodeTest extends PHPUnit_Framework_TestCase
{
    public function test_smarty_modifier_explode()
    {
        //  配列でない場合
        $result = smarty_modifier_explode(1, ",");
        $this->assertTrue(array(1) == $result);

        $result = smarty_modifier_explode(NULL, ",");
        $this->assertTrue(array("") == $result);

        $input = "1,2,3,4,5";
        $result = smarty_modifier_explode($input, ",");
        $this->assertTrue(array(1,2,3,4,5) == $result);

        $result = smarty_modifier_explode($input, ":");
        $this->assertTrue(array("1,2,3,4,5") == $result);

        $result = smarty_modifier_explode($input, "");
        $this->assertTrue(false == $result);

    }

}

