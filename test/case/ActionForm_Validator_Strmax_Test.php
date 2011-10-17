<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Strmax_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Strmax_Test
/**
 *  Test Case For Ethna_ActionForm(Max Validator(Single byte String))
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Strmax_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ Validator Max Single byte String. 
    function test_Validate_SingleByteMax_String()
    {
        $form_def = array(
                          'type'          => VAR_TYPE_STRING,
                          'form_type'     => FORM_TYPE_TEXT,
                          'required'      => true,
                          'strmax'      => '3',
                    );        
        $this->af->setDef('input', $form_def);
        
        //   in ascii.
        $this->af->set('input', 'abc'); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'abcd');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'ab');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   multibyte.
        $this->af->set('input', 'あいう');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        //  TODO: Error Message Test.
    }
    // }}}
}
// }}}

