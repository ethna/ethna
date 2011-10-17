<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Type_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Type_Test
/**
 *  Test Case For Ethna_ActionForm(Type Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Type_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ Validator Type Integer. 
    function test_Validate_Type_Integer()
    {
        $form_def = array(
                        'type' => VAR_TYPE_INT,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 5);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', 6.5);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Type Float. 
    function test_Validate_Type_Float()
    {
        $form_def = array(
                        'type' => VAR_TYPE_FLOAT,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 4.999999); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 4);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Type Datetime. 
    function test_Validate_Type_DateTime()
    {
        $form_def = array(
                        'type' => VAR_TYPE_DATETIME,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', '1999-12-31'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', ';-!#');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
    }
    // }}}

    // {{{ Validator Type String. 
    function test_Validate_Min_String()
    {
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                    );        
        $this->af->setDef('input', $form_def);
        
        //   in ascii.
        $this->af->set('input', 'abcd'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', 'あいうえお');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    null の値はTypeではチェックしない
        $this->af->set('input', null);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //    空文字の値はTypeではチェックしない
        $this->af->set('input', '');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();
    }
    // }}}
}
// }}}

