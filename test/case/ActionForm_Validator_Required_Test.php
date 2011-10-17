<?php
// vim: foldmethod=marker
/**
 *  ActionForm_Validator_Required_Test.php
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
        $this->af->setDef(null, array());
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

        $this->af->set('input', '0');
        $this->af->validate();
        $this->assertFalse($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', null); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
        $this->ae->clear();

        $this->af->set('input', ''); 
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
        $this->ae->clear();

        $this->af->set('input', ''); 
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
        $this->ae->clear();

        $this->af->set('input', ''); 
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
        $this->ae->clear();

        $this->af->set('input', ''); 
        $this->af->validate();
        $this->assertTrue($this->ae->isError('input'));
    }
    // }}}

    // {{{ Validator Required File. 
    function test_Validate_Required_File()
    {
        //  skipped because we can't bypass 
        //  is_uploaded_file function.
    }
    // }}}

    // {{{ Validator Required Integer ARRAY. 
    function test_Validate_Required_Integer_Array()
    {
        $test_form_type = array(
                              FORM_TYPE_TEXT,
                              FORM_TYPE_PASSWORD,
                              FORM_TYPE_TEXTAREA,
                              FORM_TYPE_SELECT,
                              FORM_TYPE_RADIO,
                              FORM_TYPE_CHECKBOX,
                              FORM_TYPE_BUTTON,
                              FORM_TYPE_HIDDEN,
                          );

        //
        //    FILE以外の全てのフォームタイプをテスト
        //
        foreach ($test_form_type as $form_type) {

            $form_def = array(
                            'type' => array(VAR_TYPE_INT),
                            'form_type' => $form_type,
                            'required' => true,
                        );        
            $this->af->setDef('input', $form_def);
            
            //   Formが全くsubmitすらされていない場合
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
     
            //   配列の場合, 何も指定がない場合は全部値が入力されていなければならない
            $this->af->set('input', array(5, null, null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array(5, 6, 7));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            //   空配列は当然エラー
            $this->af->set('input', array());
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_num が指定された場合
            //   指定された数だけvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_INT),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_num' => 2,
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(5, 6));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array(5, null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_key が指定された場合
            //   指定されたキーの要素にはvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_INT),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_key' => array(1),
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(null, 6));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array(6, null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
        }
    }
    // }}}

    // {{{ Validator Required Float ARRAY. 
    function test_Validate_Required_Float_Array()
    {
        $test_form_type = array(
                              FORM_TYPE_TEXT,
                              FORM_TYPE_PASSWORD,
                              FORM_TYPE_TEXTAREA,
                              FORM_TYPE_SELECT,
                              FORM_TYPE_RADIO,
                              FORM_TYPE_CHECKBOX,
                              FORM_TYPE_BUTTON,
                              FORM_TYPE_HIDDEN,
                          );

        //
        //    FILE以外の全てのフォームタイプをテスト
        //
        foreach ($test_form_type as $form_type) {

            $form_def = array(
                            'type' => array(VAR_TYPE_FLOAT),
                            'form_type' => $form_type,
                            'required' => true,
                        );        
            $this->af->setDef('input', $form_def);
            
            //   Formが全くsubmitすらされていない場合
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
     
            //   配列の場合, 何も指定がない場合は全部値が入力されていなければならない
            $this->af->set('input', array(5.0, null, null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array(5.1, 6.65, 91.099));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            //   空配列は当然エラー
            $this->af->set('input', array());
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_num が指定された場合
            //   指定された数だけvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_FLOAT),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_num' => 2,
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(5.12, 87.090));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array('abcd', 878.911));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_key が指定された場合
            //   指定されたキーの要素にはvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_FLOAT),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_key' => array(1),
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(null, 6.13));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array(6.019, 'abcd'));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
        }
    }
    // }}}

    // {{{ Validator Required Datetime ARRAY. 
    function test_Validate_Required_Datetime_Array()
    {
        $test_form_type = array(
                              FORM_TYPE_TEXT,
                              FORM_TYPE_PASSWORD,
                              FORM_TYPE_TEXTAREA,
                              FORM_TYPE_SELECT,
                              FORM_TYPE_RADIO,
                              FORM_TYPE_CHECKBOX,
                              FORM_TYPE_BUTTON,
                              FORM_TYPE_HIDDEN,
                          );

        //
        //    FILE以外の全てのフォームタイプをテスト
        //
        foreach ($test_form_type as $form_type) {

            $form_def = array(
                            'type' => array(VAR_TYPE_DATETIME),
                            'form_type' => $form_type,
                            'required' => true,
                        );        
            $this->af->setDef('input', $form_def);
            
            //   Formが全くsubmitすらされていない場合
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
     
            //   配列の場合, 何も指定がない場合は全部値が入力されていなければならない
            $this->af->set('input', array('2005-01-01', '2005-01-44', null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array('2005-01-01', '2005-01-02', '2005-01-03'));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            //   空配列は当然エラー
            $this->af->set('input', array());
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_num が指定された場合
            //   指定された数だけvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_DATETIME),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_num' => 2,
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array('2008-01-01', '2008-01-02'));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array('2008-01-02', 'abcd'));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_key が指定された場合
            //   指定されたキーの要素にはvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_DATETIME),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_key' => array(1),
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(null, '2009-12-31'));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array('2008-12-11', 'abcd'));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
        }
    }
    // }}}

    // {{{ Validator Required String ARRAY. 
    function test_Validate_Required_String_Array()
    {
        $test_form_type = array(
                              FORM_TYPE_TEXT,
                              FORM_TYPE_PASSWORD,
                              FORM_TYPE_TEXTAREA,
                              FORM_TYPE_SELECT,
                              FORM_TYPE_RADIO,
                              FORM_TYPE_CHECKBOX,
                              FORM_TYPE_BUTTON,
                              FORM_TYPE_HIDDEN,
                          );

        //
        //    FILE以外の全てのフォームタイプをテスト
        //
        foreach ($test_form_type as $form_type) {

            $form_def = array(
                            'type' => array(VAR_TYPE_STRING),
                            'form_type' => $form_type,
                            'required' => true,
                        );        
            $this->af->setDef('input', $form_def);
            
            //   Formが全くsubmitすらされていない場合
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
     
            //   配列の場合, 何も指定がない場合は全部値が入力されていなければならない
            $this->af->set('input', array("abcd", null, null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array("abcd", "cdef", "hogehoge"));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            //   空配列は当然エラー
            $this->af->set('input', array());
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_num が指定された場合
            //   指定された数だけvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_STRING),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_num' => 2,
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array("abcd", "cdef"));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array("abcd", null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
    
            //   required_key が指定された場合
            //   指定されたキーの要素にはvalidな値が入力されなければならない
            $form_def = array(
                            'type' => array(VAR_TYPE_STRING),
                            'form_type' => $form_type,
                            'required' => true,
                            'required_key' => array(1),
                        );        
            $this->af->setDef('input', $form_def);
    
            $this->af->set('input', array(null, "abcd"));
            $this->af->validate();
            $this->assertFalse($this->ae->isError('input'));
            $this->ae->clear();
    
            $this->af->set('input', array("abcd", null));
            $this->af->validate();
            $this->assertTrue($this->ae->isError('input'));
            $this->ae->clear();
        }
    }
    // }}}

}
// }}}

