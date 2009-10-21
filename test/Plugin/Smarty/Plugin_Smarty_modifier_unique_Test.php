<?php
// vim: foldmethod=marker
/**
 *  Plugin_Smarty_modifier_unique_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/modifier.unique.php';

//{{{    Ethna_Plugin_Smarty_modifier_unique_Test
/**
 *  Test Case For modifier.unique.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_modifier_unique_Test extends Ethna_UnitTestBase
{
    // {{{  test_smarty_modifier_unique
    function test_smarty_modifier_unique()
    {
        //  配列でない場合
        $result = smarty_modifier_unique('a');
        $this->assertTrue('a', $result);

        $result = smarty_modifier_unique(NULL);
        $this->assertNULL($result);

        //  第2引数なしの場合
        $input = array(1, 2, 1, 1, 3, 2, 4);
        $result = smarty_modifier_unique($input);
        $this->assertTrue(is_numeric(array_search(1, $result)));
        $this->assertTrue(is_numeric(array_search(2, $result)));
        $this->assertTrue(is_numeric(array_search(3, $result)));
        $this->assertTrue(is_numeric(array_search(4, $result)));
        $this->assertFalse(is_numeric(array_search(5, $result)));

        //  第2引数ありの場合
        $input = array(
                     array("foo" => 1, "bar" => 4),
                     array("foo" => 1, "bar" => 4),
                     array("foo" => 1, "bar" => 4),
                     array("foo" => 2, "bar" => 5),
                     array("foo" => 3, "bar" => 6),
                     array("foo" => 2, "bar" => 5),
                 );
        $result = smarty_modifier_unique($input, 'bar');
        $this->assertTrue(is_numeric(array_search(4, $result)));
        $this->assertTrue(is_numeric(array_search(5, $result)));
        $this->assertTrue(is_numeric(array_search(6, $result)));
        $this->assertFalse(is_numeric(array_search(1, $result)));
        $this->assertFalse(is_numeric(array_search(2, $result)));
        $this->assertFalse(is_numeric(array_search(3, $result)));
    }
    // }}}
}
// }}}

