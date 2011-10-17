<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Filter_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_FilterTest_ActionForm
/**
 *  Test ActionForm For Filter 
 *
 *  @access public
 */
class Ethna_FilterTest_ActionForm extends Ethna_ActionForm
{
    var $form = array(
        'test' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test',
        ),
    );

    //    user defined filter
    function _filter_toupper($value)
    {
        return strtoupper($value); 
    }

    function _filter_tolower($value)
    {
        return strtolower($value); 
    }
}
// }}}

// {{{    Ethna_ActionForm_Filter_Test
/**
 *  Test Case For Ethna_ActionForm(Filter)
 *
 *  @access public
 */
class Ethna_ActionForm_Filter_Test extends Ethna_UnitTestBase
{
    var $local_af;

    function setUp()
    {
        $this->local_af = new Ethna_FilterTest_ActionForm($this->ctl); 
        $this->local_af->clearFormVars();
        $this->ae->clear();
    }

    // {{{ FILTER_FW Test
    function test_filter_fw()
    {
        //   半角カナ -> 全角カナ + ntrim 
        $this->local_af->form['test']['filter'] = FILTER_FW;
        $this->local_af->set('test', "\x00ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾝ\x00");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロン', $filtered_value);
        $this->ae->clear();
    }
    // }}}

    // {{{ FILTER_HW Test
    function test_filter_hw()
    {
        //   全角英数字 -> 半角英数字 + ntrim + rtrim + ltrim
        $this->local_af->form['test']['filter'] = FILTER_HW;
        $this->local_af->set('test', " \t\n\r\0\x0B ＡＢ\x00ＣＤＥＦＧ０１２３\x00４５６７８９\t\n\r\0\x0B ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('ABCDEFG0123456789', $filtered_value);
    }
    // }}}

    // {{{ FILTER alnum_zentohan 
    function test_filter_alnum_zentohan()
    {
        //  全角英数字->半角英数字
        $this->local_af->form['test']['filter'] = 'alnum_zentohan'; 
        $this->local_af->set('test', "ＡＢＣＤＥＦＧ０１２３４５６７８９");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('ABCDEFG0123456789', $filtered_value);
    }
    // }}}

    // {{{ FILTER numeric_zentohan 
    function test_filter_numeric_zentohan()
    {
        //  全角数字->半角数字
        $this->local_af->form['test']['filter'] = 'numeric_zentohan'; 
        $this->local_af->set('test', "０１２３４５６７８９");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('0123456789', $filtered_value);
    }
    // }}}

    // {{{ FILTER alphabet_zentohan 
    function test_filter_alphabet_zentohan()
    {
        //  全角英字->半角英字(大文字)
        $this->local_af->form['test']['filter'] = 'alnum_zentohan'; 
        $this->local_af->set('test', "ＡＢＣＤＥＦＧＨＩＪＫＬＭＮＯＰＱＲＳＴＵＶＷＸＹＺ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $filtered_value);
        $this->ae->clear();

        //  全角英字->半角英字(小文字)
        $this->local_af->form['test']['filter'] = 'alnum_zentohan'; 
        $this->local_af->set('test', "ａｂｃｄｅｆｇｈｉｊｋｌｍｎｏｐｑｒｓｔｕｖｗｘｙｚ");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('abcdefghijklmnopqrstuvwxyz', $filtered_value);
    }
    // }}}

    // {{{ FILTER ltrim
    function test_filter_ltrim()
    {
        //    ltrim は全角スペースを除けないので注意!!!
        //    Ethna はデフォルトの文字のみを除きます
        //    @see http://jp.php.net/ltrim
        $this->local_af->form['test']['filter'] = 'ltrim'; 
        $this->local_af->set('test', " \t\n\r\0\x0Bhoge");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('hoge', $filtered_value);
    }
    // }}}

    // {{{ FILTER rtrim
    function test_filter_rtrim()
    {
        //    rtrim は全角スペースを除けないので注意!!!
        //    Ethna はデフォルトの文字のみを除きます
        //    @see http://jp.php.net/rtrim
        $this->local_af->form['test']['filter'] = 'rtrim'; 
        $this->local_af->set('test', "hoge \t\n\r\0\x0B");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('hoge', $filtered_value);
    }
    // }}}

    // {{{ FILTER ntrim
    function test_filter_ntrim()
    {
        $this->local_af->form['test']['filter'] = 'ntrim'; 
        $this->local_af->set('test', "\x00hoge\x00\x00");
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('hoge', $filtered_value);
    }
    // }}}

    // {{{ FILTER kana_hantozen 
    function test_filter_kana_hantozen()
    {
        //  半角カナ->全角カナ
        $this->local_af->form['test']['filter'] = 'kana_hantozen'; 
        $this->local_af->set('test', 'ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾝ');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロン', $filtered_value);
    }
    // }}}

    // {{{ one custom filter 
    function test_filter_custom_toupper()
    {
        //  小文字を大文字へ
        $this->local_af->form['test']['filter'] = 'toupper'; 
        $this->local_af->set('test', 'abcdefghijklmnopqrstuvwxyz');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $filtered_value);
    }
    // }}}

    // {{{ multiple custom filter 
    function test_filter_custom_multiple()
    {
        //  小文字を大文字へ、そして元に戻す
        $this->local_af->form['test']['filter'] = 'toupper,tolower'; 
        $this->local_af->set('test', 'abcdefghijklmnopqrstuvwxyz');
        $this->local_af->validate();
        $filtered_value = $this->local_af->get('test');
        $this->assertEqual('abcdefghijklmnopqrstuvwxyz', $filtered_value);
    }
    // }}}
}
// }}}

