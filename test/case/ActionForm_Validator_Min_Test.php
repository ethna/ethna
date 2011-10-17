<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Min_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Min_Test
/**
 *  Test Case For Ethna_ActionForm(Min Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Min_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ Validator Min Integer. 
    function test_Validate_Min_Integer()
    {
        $form_def = array(
                        'type' => VAR_TYPE_INT,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => 5,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 5);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 4); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', 6);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Min Float. 
    function test_Validate_Min_Float()
    {
        $form_def = array(
                        'type' => VAR_TYPE_FLOAT,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => 5,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 4.999999); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 5.0);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 5);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 4);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Min Datetime. 
    function test_Validate_Min_DateTime()
    {
        $form_def = array(
                        'type' => VAR_TYPE_DATETIME,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => '2000-01-01',
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', '1999-12-31'); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', '2000-01-01');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', '2000-01-02');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
    }
    // }}}

    // {{{ Validator Min String. 
    // {{{ Validator Min String(UTF-8)
    function test_Validate_Min_String_UTF8()
    {
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => 5,
                    );        
        $this->af->setDef('input', $form_def);
        
        //   in ascii.
        $this->af->set('input', 'abcd'); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcde');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', 'あいうえお');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'あいうえ');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Min String(EUC-JP)
    function test_Validate_Min_String_EUCJP()
    {
        $this->ctl->setClientEncoding('EUC-JP');
 
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => 4,  //  全角2文字、半角4文字
                    );        
        $this->af->setDef('input', $form_def);
        
        //   in ascii.
        $this->af->set('input', 'abcd'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abc');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcde');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', mb_convert_encoding('あい', 'EUC-JP', 'UTF-8'));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', mb_convert_encoding('あ', 'EUC-JP', 'UTF-8'));
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', mb_convert_encoding('あいう', 'EUC-JP', 'UTF-8'));
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));

        //   reset client encoding
        $this->ctl->setClientEncoding('UTF-8');
    }
    // }}}

    // {{{ Validator Min String(ASCII)
    function test_Validate_Min_String_ASCII()
    {
        $this->ctl->setClientEncoding('ASCII');
 
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'min' => 4,  //  ascii 4文字 
                    );        
        $this->af->setDef('input', $form_def);
        
        //   in ascii.
        $this->af->set('input', 'abcd'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abc');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcde');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   reset client encoding
        $this->ctl->setClientEncoding('UTF-8');
    }
    // }}}
    // }}}

    // {{{ Validator Min File. 
    function test_Validate_Min_File()
    {
        //  skipped because we can't bypass 
        //  is_uploaded_file function.
    }
    // }}}

}
// }}}

