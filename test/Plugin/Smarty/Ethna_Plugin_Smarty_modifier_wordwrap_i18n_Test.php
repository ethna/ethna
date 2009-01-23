<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Smarty_modifier_wordwrap_i18n_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/modifier.wordwrap_i18n.php';

//{{{    Ethna_Plugin_Smarty_modifier_wordwrap_i18n_Test
/**
 *  Test Case For modifier.wordwrap_i18n.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_modifier_wordwrap_i18n_Test extends Ethna_UnitTestBase
{
    function tearDown()
    {
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('UTF-8');
    }

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
