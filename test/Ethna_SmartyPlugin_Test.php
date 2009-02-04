<?php
// vim: foldmethod=marker
/**
 *  Ethna_SmartyPlugin_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Ethna_SmartyPlugin.php';

//{{{    Ethna_SmartyPlugin_Test
/**
 *  Test Case For Ethna_SmartyPlugin.php
 *
 *  @access public
 */
class Ethna_SmartyPlugin_Test extends Ethna_UnitTestBase
{
    // {{{ test_smarty_modifier_select
    function test_smarty_modifier_select()
    {
        $r = smarty_modifier_select('a', 'b');
        $this->assertNull($r);

        $r = smarty_modifier_select('a', 'a');
        $this->assertEqual($r, 'selected="selected"');
    }
    // }}}
}
// }}}

?>
