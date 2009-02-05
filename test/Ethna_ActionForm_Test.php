<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

// {{{ Test ActionForm Classes
// {{{ Test_ActionForm_Integer
/**
 *  integer type value Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_Integer extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   integer type.
        //

        //   integer type error
        'integer_type_error_input' => array(
            'required' => false,
            'type' => VAR_TYPE_INT,
         ),
 
        //   required input
        'integer_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
         ),
    
        //   minimun input
        'integer_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'min' => 100,
         ),

         //  maximum input
         'integer_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'max' => 100,
         ),
 
         //  regexp input
         'integer_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'regexp' => '/^\d+$/',
         ),

    ); 
 
}
// }}}

// {{{ Test_ActionForm_WithPlugin_Integer
/**
 *  integer type value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_Integer extends Test_ActionForm_Integer
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_ActionForm_Float
/**
 *  float value type Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_Float extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   float type.
        //

        //   float type error
        'float_type_error_input' => array(
            'required' => false,
            'type' => VAR_TYPE_FLOAT,
         ),
 
        //   required input
        'float_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
         ),
    
        //   minimun input
        'float_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'min' => 100.1,
         ),

         //  maximum input
         'float_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'max' => 100.1,
         ),
 
         //  regexp input
         'float_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'regexp' => '/^\d+\.\d+$/',
         ),

    ); 
 
}
// }}}

// {{{ Test_ActionForm_WithPlugin_Float
/**
 *  float type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_Float extends Test_ActionForm_Float
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_ActionForm_String
/**
 *  string value type Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_String extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   string type.
        //

        //   required input
        'string_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
         ),
    
        //   minimun input
        'string_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'min' => 5,
         ),

         //  maximum input
         'string_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'max' => 5,
         ),
 
         //  regexp input
         'string_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'regexp' => '/^b+$/',
         ),
    ); 
 
}
// }}}

// {{{ Test_ActionForm_WithPlugin_String
/**
 *  string type value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_String extends Test_ActionForm_String
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_ActionForm_Datetime
/**
 *  datetime value type Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_Datetime extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   datetime type.
        //

        //   required input
        'datetime_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
         ),
    
        //   minimun input
        'datetime_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'min' => '2010-10-01',
         ),

         //  maximum input
         'datetime_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'max' => '2000-01-05',
         ),
 
         //  regexp input
         'datetime_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'regexp' => '/^\d{4}-\d{2}-\d{2}$/',
         ),
    ); 
}
// }}}

// {{{ Test_ActionForm_Boolean
/**
 *  boolean value type Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_Boolean extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   boolean type.
        //

        //   required input
        'boolean_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_BOOLEAN,
         ),
    ); 
 
}
// }}}

// {{{ Test_ActionForm_WithPlugin_Boolean
/**
 *  boolean type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_Boolean extends Test_ActionForm_Boolean
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_ActionForm_WithPlugin_Datetime
/**
 *  datetime type Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_Datetime extends Test_ActionForm_Datetime
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_ActionForm_File
/**
 *  file value type Test ActionForm
 *
 *  @access private
 */
class Test_ActionForm_File extends Ethna_ActionForm
{
    var $form = array(
         //
         //  file input
         //
         'file_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FILE,
         ),
    );

}
// }}}

// {{{ Test_ActionForm_WithPlugin_File
/**
 *  Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_ActionForm_WithPlugin_File extends Test_ActionForm_File
{
    var $use_validator_plugin = true;
}
// }}}
// }}}

// {{{    Ethna_Test_ActionForm
/**
 *  Test ActionForm 
 *
 *  @access public
 */
class Ethna_Test_ActionForm extends Ethna_ActionForm
{
    var $form = array(
        'test' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test',
        ),

        'no_name' => array(
            'type' => VAR_TYPE_STRING,
            'form_type' => FORM_TYPE_TEXT,
        ),

        'test_array' => array(
            'type' => array(VAR_TYPE_STRING),
            'form_type' => FORM_TYPE_TEXT,
            'name' => 'test array',
        ),

    );
}
// }}}

// {{{    Ethna_ActionForm_Test
/**
 *  Test Case For Ethna_ActionForm
 *
 *  @access public
 */
class Ethna_ActionForm_Test extends Ethna_UnitTestBase
{
    var $ctl;

    // {{{ setUp, tearDown
    function setUp()
    {
        //    dummy AUTO GLOBAL VALUE
        $_SERVER['REQUEST_METHOD'] = 'POST';

        //    initialize controller and clear error.
        $this->ctl =& Ethna_Controller::getInstance();
        $ae =& $this->ctl->getActionError();
        $ae->clear();
    }

    function tearDown()
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($this->ctl);
    }
    // }}}

    // {{{    test_Validate_Integer
    function test_Validate_Integer()
    {
        //    normal test value
        $normal_input = array(
                        'integer_required_input' => 100,
                        'integer_min_input' => 1000,
                        'integer_max_input' => 10,
                        'integer_regexp_input' => 50000,
                      );

        $this->_run('integer', $normal_input);

        //    error test value
        $error_input = array(
                        'integer_type_error_input' => 'aaaa',
                        'integer_required_input' => null,
                        'integer_min_input' => -10000,
                        'integer_max_input' => 10000,
                        'integer_regexp_input' => 'aaa',
                    );

        $this->_run('integer', $error_input, true);
    }
    // }}}

    // {{{    test_Validate_Float
    function test_Validate_Float()
    {
        //    normal test value
        $normal_input = array(
                        'float_required_input' => 10.1,
                        'float_min_input' => 10000.1,
                        'float_max_input' => 10.1,
                        'float_regexp_input' => 50.1,
                      );

        $this->_run('float', $normal_input);

        //    error test value
        $error_input = array(
                        'float_type_error_input' => 'aaaa',
                        'float_required_input' => null,
                        'float_min_input' => -10000,
                        'float_max_input' => 10000,
                        'float_regexp_input' => 'aaa',
                    );
 
        $this->_run('float', $error_input, true);
    }
    // }}}

    // {{{    test_Validate_String
    function test_Validate_String()
    {
        //    normal test value
        $normal_input = array(
                        'string_required_input' => 'aaaa',
                        'string_min_input' => 'aaaaaaa',
                        'string_max_input' => 'bbb',
                        'string_regexp_input' => 'bbb',
                    );
 
        $this->_run('string', $normal_input);

        //    error test value
        $error_input = array(
                        'string_required_input' => null,
                        'string_min_input' => 'a',
                        'string_max_input' => 'bbbbbbbbbbbbbb',
                        'string_regexp_input' => 'aaa',
                    );
 
        $this->_run('string', $error_input, true);
    }
    // }}}

    // {{{    test_Validate_DateTime
    function test_Validate_Datetime()
    {
        $normal_input = array(
                        'datetime_required_input' => '2007-04-01',
                        'datetime_min_input' => '2030-01-01',
                        'datetime_max_input' => '1999-01-01',
                        'datetime_regexp_input' => '2007-11-30',
                    );

        $this->_run('datetime', $normal_input);

        //    error test value
        $error_input = array(
                        'datetime_required_input' => null,
                        'datetime_min_input' => '2007-01-01',
                        'datetime_max_input' => '2007-01-01',
                        'datetime_regexp_input' => '2007a-11-ga',
                    );

        $this->_run('datetime', $error_input, true);
    }
    // }}}

    // {{{    test_Validate_Boolean
    function test_Validate_Boolean()
    {
        $normal_input = array(
                        'boolean_required_input' => true,
                    );

        $this->_run('boolean', $normal_input);

        //    error test value
        $error_input = array(
                        'boolean_required_input' => null,
                    );

        $this->_run('boolean', $error_input, true);
    }
    // }}}

    // {{{ private utility functions
    function _run($type_name, $input, $test_error = false)
    {
        //    test with no plugin 
        $this->_setInputValue($input);
        $af = $this->_get_validated_af_normal($type_name);
        $ae = $this->_get_action_error();

        //    finally assert!
        $this->_assert($input, $ae, $test_error);

        //    test with plugin 
        $af = $this->_get_validated_af_normal($type_name, true);
        $ae = $this->_get_action_error();

        //    finally assert!(with plugin)
        $this->_assert($input, $ae, $test_error);

        //    unset input value
        $this-> _unsetInputValue($input);
    }

    function _setInputValue($input)
    {
        foreach ($input as $key => $value) {
            $_POST[$key] = $value;
        }
    }

    function _unsetInputValue($input)
    {
        foreach ($input as $key => $value) {
            unset($_POST[$key]);
        }
    }

    function _get_validated_af_normal($type_name, $is_plugin = false)
    {
         $class_name_prefix = ($is_plugin)
                           ? 'Test_ActionForm_WithPlugin_'
                           : 'Test_ActionForm_';
         $class_name = $class_name_prefix . ucfirst($type_name);
         return $this->_get_validated_af_real($class_name, $is_plugin);
    }

    function _get_validated_af_default($type_name, $is_plugin = false)
    {
        $class_name_prefix = ($is_plugin)
                           ? 'Test_Default_ActionForm_WithPlugin_'
                           : 'Test_Default_ActionForm_';
        $class_name = $class_name_prefix . ucfirst($type_name);

        return $this->_get_validated_af_real($class_name, $is_plugin);
   }

    function _get_validated_af_real($class_name, $is_plugin = false)
    {
        $dummy_af = new $class_name($this->ctl);
        $dummy_af->setFormVars();

        //
        //    run validate
        //    we ignore return value, because it includes
        //    input value error in other test case ...
        //
        $dummy_af->validate();
        return $dummy_af;
    }
    
    function _get_action_error()
    {
        $ae  =& $this->ctl->getActionError();
        return $ae;
    }

    function _assert($input, $ae, $test_error = false)
    {
        foreach($input as $key => $value)
        {
            if ($test_error) {
                $this->assertTrue($ae->isError($key));
            } else {
                $this->assertFalse($ae->isError($key));
            }
        } 
    }

    function _assert_default($expected, $af, $ae)
    {
        foreach($expected as $key => $value)
        {
            //    error must not exists
            //    in case of filling default value.
            $this->assertEqual($af->get($key), $value);

            //    we must get default value.
            $this->assertFalse($ae->isError($key));
        } 
    }
    // }}}

    // {{{ getHiddenVars 
    function test_getHiddenVars()
    {
        $this->local_af = new Ethna_Test_ActionForm($this->ctl); 

        //    フォーム定義が配列で、submit された値が非配列の場合
        //    かつ、フォーム定義が配列なので、結局出力するhiddden
        //    タグも配列用のものとなる. 警告も勿論でない
        $this->local_af->set('test_array', 1);

        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    配列出力のテスト
        $this->local_af->set('test_array', array(1, 2));
        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test_array[0]\" value=\"1\" />\n"
                  . "<input type=\"hidden\" name=\"test_array[1]\" value=\"2\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    スカラーのテスト
        $this->local_af->set('test', 1);
        $hidden = $this->local_af->getHiddenVars();
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
        $this->local_af->clearFormVars();

        //    フォーム定義がスカラーで、submitされた値が配列の場合
        //    この場合は明らかに使い方が間違っている上、２重に値が
        //    出力されても意味がないので、警告(E_NOTICE)扱いにする
        //    この場合、hiddenタグは出力されない
        $this->local_af->set('test', array(1,2));
        $hidden = $this->local_af->getHiddenVars();
        $this->assertEqual($hidden, '');  //  値が入っていない扱いなので空文字が返る
        $this->local_af->clearFormVars();

        //    include_list テスト
        $this->local_af->set('test', 1);
        $this->local_af->set('no_name', 'name');
        $this->local_af->set('test_array', array(1,2));
        $include_list = array('test');
        $hidden = $this->local_af->getHiddenVars($include_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
       
        //    exclude_list テスト
        $exclude_list = array('test_array', 'no_name');
        $hidden = $this->local_af->getHiddenVars(NULL, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);

        //    include_list, exclude_list の組み合わせ
        $include_list = array('test', 'no_name');
        $exclude_list = array('no_name');
        $hidden = $this->local_af->getHiddenVars($include_list, $exclude_list);
        $expected = "<input type=\"hidden\" name=\"test\" value=\"1\" />\n";
        $this->assertEqual($hidden, $expected);
    }
    // }}}
}
// }}}

?>
