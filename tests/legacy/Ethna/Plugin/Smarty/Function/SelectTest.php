<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Smarty_Function_SelectTest extends PHPUnit_Framework_TestCase
{
    public function test_smarty_function_select()
    {
        $params = array('list'  => array(
            '1' => array('name' => 'foo'),
            'value' => array('name' => 'bar'),
        ),
            'name'  => 'name',
            'value' => 'value',
            'empty' => false,
        );
        $dummy_smarty = null;
        $expected = "<select name=\"name\">\n"
            . "<option value=\"1\" >foo</option>\n"
            . "<option value=\"value\" selected=\"selected\">bar</option>\n"
            . "</select>\n";

        ob_start();
        smarty_function_select($params, $dummy_smarty);
        $actual = ob_get_clean();
        $this->assertEquals($expected, $actual);

        $params['empty'] = '-- please select --';
        $expected = "<select name=\"name\">\n"
            . "<option value=\"\">-- please select --</option>\n"
            . "<option value=\"1\" >foo</option>\n"
            . "<option value=\"value\" selected=\"selected\">bar</option>\n"
            . "</select>\n";
        ob_start();
        smarty_function_select($params, $dummy_smarty);
        $actual = ob_get_clean();
        $this->assertEquals($expected, $actual);
    }
    // }}}
}

