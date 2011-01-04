<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Regexp_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Regexp_Test
/**
 *  Test Case For Ethna_ActionForm(Regexp Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Regexp_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ Validator Regexp. 
    function test_Validate_Regexp()
    {
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'regexp' => '/^[A-Za-z0-9]+$/',
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 'a5A4Pgw9');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', '-80pz;+');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 1459); 
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        //   regexp はマルチバイトには対応していない
        $this->af->set('input', 'あいうえお');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

}
// }}}

