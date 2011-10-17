<?php
// vim: foldmethod=marker
/**
 *  Plugin_Smarty_function_select_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/function.select.php';

//{{{    Ethna_Plugin_Smarty_function_select_Test
/**
 *  Test Case For function.select.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_function_select_Test extends Ethna_UnitTestBase
{
    // {{{ test_smarty_function_select
    function test_smarty_function_select()
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
        $this->assertEqual($expected, $actual); 

        $params['empty'] = '-- please select --';
        $expected = "<select name=\"name\">\n"
                  . "<option value=\"\">-- please select --</option>\n"
                  . "<option value=\"1\" >foo</option>\n"
                  . "<option value=\"value\" selected=\"selected\">bar</option>\n"
                  . "</select>\n";
        ob_start();
        smarty_function_select($params, $dummy_smarty);
        $actual = ob_get_clean();
        $this->assertEqual($expected, $actual); 
    }
    // }}}
}
// }}}

