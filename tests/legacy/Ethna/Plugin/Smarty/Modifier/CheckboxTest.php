<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Smarty_Modifier_CheckboxTest extends PHPUnit_Framework_TestCase
{
    public function test_smarty_modifier_checkbox()
    {
        //  文字列型で0と空文字列以外は確実に checked
        $expected = 'checked="checked"';
        $actual = smarty_modifier_checkbox("hoge");
        $this->assertEquals($expected, $actual);

        $actual = smarty_modifier_checkbox("yes");
        $this->assertEquals($expected, $actual);

        $actual = smarty_modifier_checkbox("n");
        $this->assertEquals($expected, $actual);

        $actual = smarty_modifier_checkbox(1);  // numeric other than zero.
        $this->assertEquals($expected, $actual);

        $actual = smarty_modifier_checkbox(4.001);  // float
        $this->assertEquals($expected, $actual);

        //   0 と空文字列の場合はNULLになる
        $actual = smarty_modifier_checkbox(0);  // numeric zero
        $this->assertNULL($actual);

        $actual = smarty_modifier_checkbox(0.0);  // float zero
        $this->assertNULL($actual);

        $actual = smarty_modifier_checkbox("0");
        $this->assertNULL($actual);

        $actual = smarty_modifier_checkbox("");
        $this->assertNULL($actual);

        //   null や false も 0 や空文字列と同じ扱い
        $actual = smarty_modifier_checkbox(NULL);
        $this->assertNULL($actual);

        $actual = smarty_modifier_checkbox(false);
        $this->assertNULL($actual);

        //  array, object, resource も checkedにはしない
        $actual = smarty_modifier_checkbox(array());
        $this->assertNULL($actual);

        $actual = smarty_modifier_checkbox(new stdClass());
        $this->assertNULL($actual);
    }
}

