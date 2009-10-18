<?php
/**
 *  Ethna_Plugin_Smarty_modifier_explode_Test.php
 *
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/modifier.explode.php';

//{{{    Ethna_Plugin_Smarty_modifier_explode_Test
/**
 *  Test Case For modifier.explode.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_modifier_explode_Test extends Ethna_UnitTestBase
{
    // {{{  test_smarty_modifier_explode
    function test_smarty_modifier_explode()
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
    // }}}
}
// }}}

