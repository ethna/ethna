<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Smarty_Modifier_Wordwrap_I18nTest extends PHPUnit_Framework_TestCase
{
    // {{{  test_smarty_modifier_wordwrap_i18n
    public function test_smarty_modifier_wordwrap_i18n()
    {
        unset($GLOBALS['_Ethna_Controller_Dummy']);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\n"
            . "えaおaか\n"
            . "きaaaく\n"
            . 'けこ';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);

        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaく';
        $expected = "あいうa\n"
            . "えaおaか\n"
            . "きaaaく";
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);

        //    UTF-8
        $input_str = 'あいうaえaaaaaaaaaaaaaaaaaaaおかahaかきaaaく';
        $expected = "あいうa\n"
            . "えaaaaaa\n"
            . "aaaaaaaa\n"
            . "aaaaaお\n"
            . "かahaか\n"
            . "きaaaく";
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8);
    }

    public function test_smarty_modifier_wordwrap_i18n_indent()
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
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\n", 4);
    }

    public function test_smarty_modifier_wordwrap_i18n_break()
    {
        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ';
        $expected = "あいうa\r\n"
            . "    えaおaか\r\n"
            . "    きaaaく\r\n"
            . '    けこ';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    public function test_smarty_modifier_wordwrap_i18n_space()
    {
        //    UTF-8
        $input_str = 'あいうaえaおaかきaaaくけこ     ';
        $expected = "あいうa\r\n"
            . "    えaおaか\r\n"
            . "    きaaaく\r\n"
            . "    けこ    \r\n"
            . '     ';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    public function test_smarty_modifier_wordwrap_i18n_kana()
    {
        //    UTF-8
        $input_str = 'あいうｲｴｵaえaおaかきaaaくけこ';
        $expected = "あいうｲｴ\r\n"
            . "    ｵaえaおa\r\n"
            . "    かきaaa\r\n"
            . '    くけこ';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, "\r\n", 4);
    }

    public function test_smarty_modifier_wordwrap_i18n_alphabet()
    {
        $input_str = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $expected = 'abcdefgh<br />'
            . 'ijklmnop<br />'
            . 'qrstuvwx<br />'
            . 'yzabcdef<br />'
            . 'ghijklmn<br />'
            . 'opqrstuv<br />'
            . 'wxyz';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, '<br />');

        $input_str = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuv';
        $expected = 'abcdefgh<br />'
            . 'ijklmnop<br />'
            . 'qrstuvwx<br />'
            . 'yzabcdef<br />'
            . 'ghijklmn<br />'
            . 'opqrstuv';
        $this->_execute_smarty_modifier_wordwrap_i18n($expected, $input_str, 8, '<br />');
    }

    public function _execute_smarty_modifier_wordwrap_i18n($expected, $input_str, $width, $break = "\n", $indent = 0)
    {
        unset($GLOBALS['_Ethna_Controller_Dummy']);

        $ctl = new Ethna_Controller_Dummy();
        $actual = smarty_modifier_wordwrap_i18n($input_str, $width, $break, $indent);
        $this->assertEquals($expected, $actual);
        unset($GLOBALS['_Ethna_Controller_Dummy']);

        //     SJIS
        $ctl = new Ethna_Controller_Dummy();
        $ctl->setClientEncoding('SJIS');

        $sjis_input = mb_convert_encoding($input_str, 'SJIS', 'UTF-8');
        $sjis_expected = mb_convert_encoding($expected, 'SJIS', 'UTF-8');
        $sjis_actual = smarty_modifier_wordwrap_i18n($sjis_input, $width, $break, $indent);
        $this->assertEquals($sjis_expected, $sjis_actual);
        unset($GLOBALS['_Ethna_Controller_Dummy']);

        //     EUC-JP
        $ctl = new Ethna_Controller_Dummy();
        $ctl->setClientEncoding('EUC-JP');

        $eucjp_input = mb_convert_encoding($input_str, 'EUC-JP', 'UTF-8');
        $eucjp_expected = mb_convert_encoding($expected, 'EUC-JP', 'UTF-8');
        $eucjp_actual = smarty_modifier_wordwrap_i18n($eucjp_input, $width, $break, $indent);
        $this->assertEquals($eucjp_expected, $eucjp_actual);
    }
}

