<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Custom_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Custom_Test
/**
 *  Test Case For Ethna_ActionForm(Custom Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Custom_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ checkMailAddress
    function test_checkMailAddress()
    {
        //    'required' => true とすると
        //    Ethna_Plugin_Validator_Required の時点で
        //    エラーになる入力があるためここではfalseに
        //    設定
        $form_string = array(
                           'type' => VAR_TYPE_STRING,
                           'required' => false,
                           'custom' => 'checkMailaddress',
                       );
        $this->af->setDef('input', $form_string);

        $this->af->set('input', 'hoge@fuga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', '-hoge@fuga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', '.hoge@fuga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', '+hoge@fuga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        // @がない
        $this->af->set('input', 'hogefuga.et');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        // @の前に文字がない
        $this->af->set('input', '@hogefuga.et');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        // @の後に文字がない
        $this->af->set('input', 'hogefuga.net@');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        // 先頭文字が許されていない
        $this->af->set('input', '%hoge@fuga.net');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        // 末尾文字が許されていない
        $this->af->set('input', 'hoge@fuga.net.');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ checkBoolean
    function test_checkBoolean()
    {
        //    'required' => true とすると
        //    Ethna_Plugin_Validator_Required の時点で
        //    エラーになる入力があるためここではfalseに
        //    設定
        $form_boolean = array(
                            'type' => VAR_TYPE_BOOLEAN,
                            'required' => false,
                            'custom' => 'checkBoolean',
                        );
        $this->af->setDef('input', $form_boolean);

        //   HTML フォームから入ってくる値は
        //   文字列型である。
        //   @see http://www.php.net/manual/en/types.comparisons.php  
        $this->af->set('input', '0');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', '1');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        //   空文字列は false と見做すのが仕様
        $this->af->set('input', '');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        // 0,1, 空文字列以外の値は全てエラー
        $this->af->set('input', 3);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', "true");
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', "false");
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ checkURL
    function test_checkURL()
    {
        //    'required' => true とすると
        //    Ethna_Plugin_Validator_Required の時点で
        //    エラーになる入力があるためここではfalseに
        //    設定
        $form_url = array(
                        'type' => VAR_TYPE_STRING,
                        'required' => false,
                        'custom' => 'checkURL',
                    );
        $this->af->setDef('input', $form_url);

        $this->af->set('input', 'http://uga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', 'https://uga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', 'ftp://uga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', 'http://');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        //    空文字列はエラーにしないのが仕様
        $this->af->set('input', '');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        // '/'が足りない
        $this->af->set('input', 'http:/');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        // 接頭辞がない
        $this->af->set('input', 'hoge@fuga.net');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ checkVendorChar
    function test_checkVendorChar()
    {
        //    'required' => true とすると
        //    Ethna_Plugin_Validator_Required の時点で
        //    エラーになる入力があるためここではfalseに
        //    設定
        $form_string = array(
                           'type' => VAR_TYPE_STRING,
                           'required' => false,
                           'custom' => 'checkVendorChar',
                       );
        $this->af->setDef('input', $form_string);

        $this->af->set('input', 'http://uga.net');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0x00));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0x79));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0x80));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0x8e));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0x8f));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xae));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xf8));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xfd));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear(); 

        /* IBM拡張文字 / NEC選定IBM拡張文字 */
        //$c == 0xad || ($c >= 0xf9 && $c <= 0xfc)
        $this->af->set('input', chr(0xad));
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xf9));
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xfa));
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear(); 

        $this->af->set('input', chr(0xfc));
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

}
// }}}

