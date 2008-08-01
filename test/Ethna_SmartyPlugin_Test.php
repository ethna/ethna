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

    // {{{ test_smarty_modifier_select
    function test_smarty_modifier_select()
    {
        $r = smarty_modifier_select('a', 'b');
        $this->assertNull($r);

        $r = smarty_modifier_select('a', 'a');
        $this->assertEqual($r, 'selected="selected"');
    }
    // }}}

    // {{{  test_smarty_modifier_wordwrap_i18n
    function test_smarty_modifier_wordwrap_i18n()
    {
        unset($GLOBALS['_Ethna_controller']);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\n"
                  . "えaおaか\n"
                  . "きaaaく\n"
                  . 'けこ';

        $ctl =& new Ethna_Controller();
        $actual = smarty_modifier_wordwrap_i18n($input_str, 8);
        $this->assertEqual($expected, $actual);
        unset($GLOBALS['_Ethna_controller']);

        //     SJIS
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('SJIS');

        $sjis_input = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
        $sjis_expected = mb_convert_encoding($expected, 'SJIS', 'UTF-8');  
        $sjis_actual = smarty_modifier_wordwrap_i18n($sjis_input, 8);
        $this->assertEqual($sjis_expected, $sjis_actual);
        unset($GLOBALS['_Ethna_controller']);

        //     EUC-JP 
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');

        $eucjp_input = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
        $eucjp_expected = mb_convert_encoding($expected, 'EUC-JP', 'UTF-8');  
        $eucjp_actual = smarty_modifier_wordwrap_i18n($eucjp_input, 8);
        $this->assertEqual($eucjp_expected, $eucjp_actual);
    }

    function test_smarty_modifier_wordwrap_i18n_indent()
    {
        //
        //    indent を指定した場合、はじめの行は
        //    インデントされないので注意
        //

        unset($GLOBALS['_Ethna_controller']);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\n"
                  . "    えaおaか\n"
                  . "    きaaaく\n"
                  . '    けこ';
        
        $ctl =& new Ethna_Controller();
        $actual = smarty_modifier_wordwrap_i18n($input_str, 8, "\n", 4);
        $this->assertEqual($expected, $actual);
        unset($GLOBALS['_Ethna_controller']);

        //     SJIS
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('SJIS');

        $sjis_input = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
        $sjis_expected = mb_convert_encoding($expected, 'SJIS', 'UTF-8');  
        $sjis_actual = smarty_modifier_wordwrap_i18n($sjis_input, 8, "\n", 4);
        $this->assertEqual($sjis_expected, $sjis_actual);
        unset($GLOBALS['_Ethna_controller']);

        //     EUC-JP 
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');

        $eucjp_input = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
        $eucjp_expected = mb_convert_encoding($expected, 'EUC-JP', 'UTF-8');  
        $eucjp_actual = smarty_modifier_wordwrap_i18n($eucjp_input, 8, "\n", 4);
        $this->assertEqual($eucjp_expected, $eucjp_actual);
    }
 
    function test_smarty_modifier_wordwrap_i18n_break()
    {
        unset($GLOBALS['_Ethna_controller']);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\r\n"
                  . "    えaおaか\r\n"
                  . "    きaaaく\r\n"
                  . '    けこ';
        
        $ctl =& new Ethna_Controller();
        $actual = smarty_modifier_wordwrap_i18n($input_str, 8, "\r\n", 4);
        $this->assertEqual($expected, $actual);
        unset($GLOBALS['_Ethna_controller']);

        //     SJIS
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('SJIS');

        $sjis_input = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
        $sjis_expected = mb_convert_encoding($expected, 'SJIS', 'UTF-8');  
        $sjis_actual = smarty_modifier_wordwrap_i18n($sjis_input, 8, "\r\n", 4);
        $this->assertEqual($sjis_expected, $sjis_actual);
        unset($GLOBALS['_Ethna_controller']);

        //     EUC-JP 
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');

        $eucjp_input = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
        $eucjp_expected = mb_convert_encoding($expected, 'EUC-JP', 'UTF-8');  
        $eucjp_actual = smarty_modifier_wordwrap_i18n($eucjp_input, 8, "\r\n", 4);
        $this->assertEqual($eucjp_expected, $eucjp_actual);
    }
    // }}}
}
// }}}

?>
