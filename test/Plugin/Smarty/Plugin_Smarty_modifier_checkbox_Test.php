<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Smarty_modifier_checkbox_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/modifier.checkbox.php';

//{{{    Ethna_Plugin_Smarty_modifier_checkbox_Test
/**
 *  Test Case For modifier.checkbox.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_modifier_checkbox_Test extends Ethna_UnitTestBase
{
    // {{{  test_smarty_modifier_checkbox
    function test_smarty_modifier_checkbox()
    {
        //  文字列型で0と空文字列以外は確実に checked
        $expected = 'checked="checked"';
        $actual = smarty_modifier_checkbox("hoge");
        $this->assertEqual($expected, $actual);

        $actual = smarty_modifier_checkbox("yes");
        $this->assertEqual($expected, $actual);

        $actual = smarty_modifier_checkbox("n");
        $this->assertEqual($expected, $actual);

        $actual = smarty_modifier_checkbox(1);  // numeric other than zero.
        $this->assertEqual($expected, $actual);

        $actual = smarty_modifier_checkbox(4.001);  // float
        $this->assertEqual($expected, $actual);

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
    // }}}
}
// }}}

