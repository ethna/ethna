<?php
// vim: foldmethod=marker
/**
 *  Plugin_Smarty_modifier_wordwrap_i18n_Test.php
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
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaく';
        $expected = "あいうa\n"
                  . "えaおaか\n"
                  . "きaaaく";
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);

        //    UTF-8
        $input_str = 'あいうaえaaaaaaaaaaaaaaaaaaaおかahaかきaaaく';
        $expected = "あいうa\n"
                  . "えaaaaaa\n"
                  . "aaaaaaaa\n"
                  . "aaaaaお\n"
                  . "かahaか\n"
                  . "きaaaく";
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);
    }

    function test_smarty_modifier_wordwrap_i18n_indent()
    {
        //
        //    indent を指定した場合、はじめの行は
        //    インデントされないので注意
        //

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\n"
                  . "    えaおaか\n"
                  . "    きaaaく\n"
                  . '    けこ';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\n", 4);
    }

    function test_smarty_modifier_wordwrap_i18n_break()
    {
        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\r\n"
                  . "    えaおaか\r\n"
                  . "    きaaaく\r\n"
                  . '    けこ';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    function test_smarty_modifier_wordwrap_i18n_space()
    {
        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ     ';
        $expected = "あいうa\r\n"
                  . "    えaおaか\r\n"
                  . "    きaaaく\r\n"
                  . "    けこ    \r\n"
                  . '     ';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    function test_smarty_modifier_wordwrap_i18n_kana()
    {
        //    UTF-8
        $input_str = 'あいうｲｴｵaえaおaかきaaaくけこ';
        $expected = "あいうｲｴ\r\n"
                  . "    ｵaえaおa\r\n"
                  . "    かきaaa\r\n"
                  . '    くけこ';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    function test_smarty_modifier_wordwrap_i18n_alphabet()
    {
        $input_str = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $expected = 'abcdefgh<br />'
                  . 'ijklmnop<br />'
                  . 'qrstuvwx<br />'
                  . 'yzabcdef<br />'
                  . 'ghijklmn<br />'
                  . 'opqrstuv<br />'
                  . 'wxyz';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, '<br />');

        $input_str = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuv';
        $expected = 'abcdefgh<br />'
                  . 'ijklmnop<br />'
                  . 'qrstuvwx<br />'
                  . 'yzabcdef<br />'
                  . 'ghijklmn<br />'
                  . 'opqrstuv';
        $this->_test_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, '<br />');
    }

    function _test_smarty_modifier_wordwrap_i18n($expected, $input_str, $width, $break = "\n", $indent = 0)
    {
        unset($GLOBALS['_Ethna_controller']);

        $ctl =& new Ethna_Controller();
        $actual = smarty_modifier_wordwrap_i18n($input_str, $width, $break, $indent);
        $this->assertEqual($expected, $actual);
        unset($GLOBALS['_Ethna_controller']);

        //     SJIS
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('SJIS');

        $sjis_input = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
        $sjis_expected = mb_convert_encoding($expected, 'SJIS', 'UTF-8');
        $sjis_actual = smarty_modifier_wordwrap_i18n($sjis_input, $width, $break, $indent);
        $this->assertEqual($sjis_expected, $sjis_actual);
        unset($GLOBALS['_Ethna_controller']);

        //     EUC-JP
        $ctl =& new Ethna_Controller();
        $ctl->setClientEncoding('EUC-JP');

        $eucjp_input = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
        $eucjp_expected = mb_convert_encoding($expected, 'EUC-JP', 'UTF-8');
        $eucjp_actual = smarty_modifier_wordwrap_i18n($eucjp_input, $width, $break, $indent);
        $this->assertEqual($eucjp_expected, $eucjp_actual);
    }
    // }}}
}
// }}}

