<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Mbregexp_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{    Ethna_ActionForm_Validator_Mbregexp_Test
/**
 *  Test Case For Ethna_ActionForm(Mbregexp Validator)
 *
 *  @access public
 */
class Ethna_ActionForm_Validator_Mbregexp_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
        $this->af->use_validator_plugin = false;
        $this->af->clearFormVars();
        $this->af->setDef(null, array());
        $this->ae->clear();
    }

    // {{{ Validator Mbregexp. 
    function test_Validate_Regexp()
    {
        $form_def = array(
                        'type' => VAR_TYPE_STRING,
                        'form_type' => FORM_TYPE_TEXT,
                        'required' => true,
                        'mbregexp' => '^[あ-ん]+$',
                    );        
        $this->af->setDef('input', $form_def);
        
        $this->af->set('input', 'a5A4Pgw9');
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 'あいうえおかきくけこ');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', 1459); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        //    encoding に指定された文字コード以外の文字列
        $euc_input = mb_convert_encoding('あいうえお', 'EUC-JP', 'UTF-8');
        $this->af->set('input', $euc_input);
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}
}
// }}}

