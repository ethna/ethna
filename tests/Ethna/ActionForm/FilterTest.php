<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_FilterTest_ActionForm extends Ethna_ActionForm
{
    protected $form = array(
        'test' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test',
        ),
    );

    function _filter_toupper($value)
    {
        return strtoupper($value);
    }

    function _filter_tolower($value)
    {
        return strtolower($value);
    }

    public function setFilter($name, $value)
    {
        // NOTE(chobie): ぐぬぬ
        $this->form[$name]["filter"] = $value;
    }
}


class Ethna_ActionForm_FilterTest extends PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $local_af;
    protected $ae;

    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->ae = $this->controller->getActionError();
        $this->local_af = new Ethna_FilterTest_ActionForm($this->controller);
        $this->local_af->clearFormVars();
        $this->ae->clear();
    }

    public function test_filter_fw()
    {
        //   半角カナ -> 全角カナ + ntrim
        $this->local_af->setFilter('test', FILTER_FW);
        $this->local_af->set('test', "\x00ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾝ\x00");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロン', $filtered_value);
        $this->ae->clear();
    }

    public function test_filter_hw()
    {
        //   全角英数字 -> 半角英数字 + ntrim + rtrim + ltrim
        $this->local_af->setFilter('test', FILTER_HW);
        $this->local_af->set('test', " \t\n\r\0\x0B ＡＢ\x00ＣＤＥＦＧ０１２３\x00４５６７８９\t\n\r\0\x0B ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('ABCDEFG0123456789', $filtered_value);
    }

    public function test_filter_alnum_zentohan()
    {
        //  全角英数字->半角英数字
        $this->local_af->setFilter('test', 'alnum_zentohan');
        $this->local_af->set('test', "ＡＢＣＤＥＦＧ０１２３４５６７８９");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('ABCDEFG0123456789', $filtered_value);
    }

    public function test_filter_numeric_zentohan()
    {
        //  全角数字->半角数字
        $this->local_af->setFilter('test', 'numeric_zentohan');
        $this->local_af->set('test', "０１２３４５６７８９");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('0123456789', $filtered_value);
    }

    public function test_filter_alphabet_zentohan()
    {
        //  全角英字->半角英字(大文字)
        $this->local_af->setFilter('test', 'alnum_zentohan');
        $this->local_af->set('test', "ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $filtered_value);
        $this->ae->clear();

        //  全角英字->半角英字(小文字)
        $this->local_af->setFilter('test', 'alnum_zentohan');
        $this->local_af->set('test', "ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $filtered_value);
    }

    public function test_filter_ltrim()
    {
        //    ltrim は全角スペースを除けないので注意!!!
        //    Ethna はデフォルトの文字のみを除きます
        //    @see http://jp.php.net/ltrim
        $this->local_af->setFilter('test', 'ltrim');
        $this->local_af->set('test', " \t\n\r\0\x0Bhoge");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('hoge', $filtered_value);
    }

    public function test_filter_rtrim()
    {
        //    rtrim は全角スペースを除けないので注意!!!
        //    Ethna はデフォルトの文字のみを除きます
        //    @see http://jp.php.net/rtrim
        $this->local_af->setFilter('test', 'rtrim');
        $this->local_af->set('test', "hoge \t\n\r\0\x0B");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('hoge', $filtered_value);
    }

    public function test_filter_ntrim()
    {
        $this->local_af->setFilter('test', 'ntrim');
        $this->local_af->set('test', "\x00hoge\x00\x00");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('hoge', $filtered_value);
    }

    public function test_filter_kana_hantozen()
    {
        //  半角カナ->全角カナ
        $this->local_af->setFilter('test', 'kana_hantozen');
        $this->local_af->set('test', 'ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾝ');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロン', $filtered_value);
    }

    public function test_filter_custom_toupper()
    {
        //  小文字を大文字へ
        $this->local_af->setFilter('test', 'toupper');
        $this->local_af->set('test', 'abcdefghijklmnopqrstuvwxyz');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $filtered_value);
    }

    public function test_filter_custom_multiple()
    {
        //  小文字を大文字へ、そして元に戻す
        $this->local_af->setFilter('test', 'toupper,tolower');
        $this->local_af->set('test', 'abcdefghijklmnopqrstuvwxyz');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEquals('abcdefghijklmnopqrstuvwxyz', $filtered_value);
    }
}

