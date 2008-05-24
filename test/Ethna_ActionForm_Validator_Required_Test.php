<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm_Validator_Required_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Required_Test
/**
 *  Test Case For Ethna_ActionForm(Required Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Required_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->form = array();
        $this->ae->clear();
    }

    // {{{ Validator Required Integer. 
    function test_Validate_Required_Integer()
    {
        $form_def = array(
                        'type' => VAR_TYPE_INT,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', 5);
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Required Float. 
    function test_Validate_Required_Float()
    {
        $form_def = array(
                        'type' => VAR_TYPE_FLOAT,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', 4.999999); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Required Datetime. 
    function test_Validate_Required_DateTime()
    {
        $form_def = array(
                        'type' => VAR_TYPE_DATETIME,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', '1999-12-31'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Required String. 
    function test_Validate_Min_String()
    {
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();
 
        $this->af->set('input', 'ああああ'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Max File. 
    function test_Validate_Max_File()
    {
        //  skipped because we can't bypass 
        //  is_uploaded_file function.
    }
    // }}}

}
// }}}

?>
