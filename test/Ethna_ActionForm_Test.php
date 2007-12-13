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
    
        //   minimun input(ascii)
        'string_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'min' => 5,
         ),

         //  maximum input(ascii)
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
         
         //  minimum input(multibyte)
        'string_min_input_multi' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'min' => 5,
         ),

         //  maximum input(multibyte)
          'string_max_input_multi' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'max' => 5,
         ),

         //  regexp input(mutlibyte)
         'string_regexp_input_multi' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'mbregexp' => '/^う+$/',
            'mbregexp_encoding' => 'UTF-8',
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

// {{{ Test Default ActionForm Classes
// {{{ Test_Default_ActionForm_Integer
/**
 *  integer type filling default value Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_Integer extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   integer type.
        //

        //   integer type error
        'integer_type_error_input' => array(
            'required' => false,
            'type' => VAR_TYPE_INT,
            'default' => 99,
         ),
 
        //   required input
        'integer_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'default' => 100,
         ),
    
        //   minimun input
        'integer_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'min' => 100,
            'default' => 101,
         ),

         //  maximum input
         'integer_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'max' => 100,
            'default' => 102,
         ),
 
         //  regexp input
         'integer_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_INT,
            'regexp' => '/^\d+$/',
            'default' => 103,
         ),

    ); 
 
}
// }}}

// {{{ Test_Default_ActionForm_WithPlugin_Integer
/**
 *  integer type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_Integer extends Test_Default_ActionForm_Integer
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_Default_ActionForm_Float
/**
 *  default float value type Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_Float extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   float type.
        //

        //   float type error
        'float_type_error_input' => array(
            'required' => false,
            'type' => VAR_TYPE_FLOAT,
            'default' => 99.1,
         ),
 
        //   required input
        'float_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'default' => 100.1,
         ),
    
        //   minimun input
        'float_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'min' => 100.1,
            'default' => 101.1,
         ),

         //  maximum input
         'float_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'max' => 100.1,
            'default' => 102.1,
         ),
 
         //  regexp input
         'float_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FLOAT,
            'regexp' => '/^\d+\.\d+$/',
            'default' => 103.1,
         ),

    ); 
 
}
// }}}

// {{{ Test_Default_ActionForm_WithPlugin_Float
/**
 *  float type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_Float extends Test_Default_ActionForm_Float
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_Default_ActionForm_String
/**
 *  default string value type Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_String extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   string type.
        //

        //   required input
        'string_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'default' => 'a',
         ),
    
        //   minimun input
        'string_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'min' => 5,
            'default' => 'b',
         ),

         //  maximum input
         'string_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'max' => 5,
            'default' => 'c',
         ),
 
         //  regexp input
         'string_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_STRING,
            'regexp' => '/^b+$/',
            'default' => 'd',
         ),
    ); 
 
}
// }}}

// {{{ Test_Default_ActionForm_WithPlugin_String
/**
 *  string type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_String extends Test_Default_ActionForm_String
{
    var $use_validator_plugin = true;
}
// }}}

// {{{ Test_Default_ActionForm_Datetime
/**
 *  default datetime value type Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_Datetime extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   datetime type.
        //

        //   required input
        'datetime_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'default' => '2000-01-01',
         ),
    
        //   minimun input
        'datetime_min_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'min' => '2010-10-01',
            'default' => '2000-01-02',
         ),

         //  maximum input
         'datetime_max_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'max' => '2000-01-05',
            'default' => '2000-01-03',
         ),
 
         //  regexp input
         'datetime_regexp_input' => array(
            'required' => true,
            'type' => VAR_TYPE_DATETIME,
            'regexp' => '/^\d{4}-\d{2}-\d{2}$/',
            'default' => '2000-01-04',
         ),
    ); 
 
}
// }}}

// {{{ Test_Default_ActionForm_Boolean
/**
 *  default boolean value type Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_Boolean extends Ethna_ActionForm
{
    var $form = array(
        
        //
        //   boolean type.
        //

        //   required input
        'boolean_required_input' => array(
            'required' => true,
            'type' => VAR_TYPE_BOOLEAN,
            'default' => true,
         ),
    ); 
 
}
// }}}

// {{{ Test_Default_ActionForm_WithPlugin_Boolean
/**
 *  boolean type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_Boolean extends Test_Default_ActionForm_Boolean
{
    var $use_validator_plugin = true;
}
// }}}


// {{{ Test_Default_ActionForm_WithPlugin_Datetime
/**
 *  datetime type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_Datetime extends Test_Default_ActionForm_Datetime
{
    var $use_validator_plugin = true;
}
// }}}


// {{{ Test_Default_ActionForm_File
/**
 *  default file value type Test ActionForm
 *
 *  @access private
 */
class Test_Default_ActionForm_File extends Ethna_ActionForm
{
    var $form = array(
         //
         //  file input
         //  this must be ignored!
         //
         'file_input' => array(
            'required' => true,
            'type' => VAR_TYPE_FILE,
            'default' => 'hoge',   //   this must be ignored.  
         ),
    );

}
// }}}

// {{{ Test_Default_ActionForm_WithPlugin_File
/**
 *  float type filling default value Test ActionForm
 *  with use_validator_plugin flag is on.
 *
 *  @access private
 */
class Test_Default_ActionForm_WithPlugin_File extends Test_Default_ActionForm_Float
{
    var $use_validator_plugin = true;
}
// }}}
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
                        'string_min_input_multi' => 'ああああああ',
                        'string_max_input_multi' => 'あああ',
                        'string_regexp_input' => 'bbb',
                        'string_regexp_input_multi' => 'ううう',
                    );
 
        $this->_run('string', $normal_input, false);

        //    error test value
        $error_input = array(
                        'string_required_input' => null,
                        'string_min_input' => 'a',
                        'string_max_input' => 'bbbbbbbbbbbbbb',
                        'string_min_input_multi' => 'あ',
                        'string_max_input_multi' => 'ああああああああああああ',
                        'string_regexp_input' => 'aaa',
                        'string_regexp_input_multi' => 'ええええ',
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

    // {{{    test_Validate_Integer_Default
    function test_Validate_Integer_Default()
    {
        //    test value
        $input = array(
                        'integer_type_error_input' => 'aaaa',
                        'integer_required_input' => null,
                        'integer_min_input' => -10000,
                        'integer_max_input' => 10000,
                        'integer_regexp_input' => 'aaa',
                    );
        $expected = array(
                        'integer_type_error_input' => 99,
                        'integer_required_input' => 100,
                        'integer_min_input' => 101,
                        'integer_max_input' => 102,
                        'integer_regexp_input' => 103,
                    );

        $this->_run_default_value_test('integer', $input, $expected);
    }
    // }}}

    // {{{    test_Validate_Float_Default
    function test_Validate_Float_Default()
    {
        //    test value
        $input = array(
                        'float_type_error_input' => 'aaaa',
                        'float_required_input' => null,
                        'float_min_input' => -10000,
                        'float_max_input' => 10000,
                        'float_regexp_input' => 'aaa',
                    );
        $expected = array(
                        'float_type_error_input' => 99.1,
                        'float_required_input' => 100.1,
                        'float_min_input' => 101.1,
                        'float_max_input' => 102.1,
                        'float_regexp_input' => 103.1,
                    );

        $this->_run_default_value_test('float', $input, $expected);
    }
    // }}}

    // {{{    test_Validate_String_Default
    function test_Validate_String_Default()
    {
        //    test value
        $input = array(
                        'string_required_input' => null,
                        'string_min_input' => 'a',
                        'string_max_input' => 'bbbbbbbbbbbbbb',
                        'string_regexp_input' => 'aaa',
                    );
        $expected = array(
                        'string_required_input' => 'a',
                        'string_min_input' => 'b',
                        'string_max_input' => 'c',
                        'string_regexp_input' => 'd',
                    );

        $this->_run_default_value_test('string', $input, $expected);
    }
    // }}}

    // {{{    test_Validate_DateTime_Default
    function test_Validate_Datetime_Default()
    {
        //    test value
        $input = array(
                        'datetime_required_input' => null,
                        'datetime_min_input' => '2007-01-01',
                        'datetime_max_input' => '2007-01-01',
                        'datetime_regexp_input' => '2007a-11-ga',
                    );
        $expected = array(
                        'datetime_required_input' => '2000-01-01',
                        'datetime_min_input' => '2000-01-02',
                        'datetime_max_input' => '2000-01-03',
                        'datetime_regexp_input' => '2000-01-04',
                    );


        $this->_run_default_value_test('datetime', $input, $expected);
    }
    // }}}

    // {{{    test_Validate_Boolean_Default
    function test_Validate_Boolean_Default()
    {
        //    test value
        $input = array(
                        'boolean_required_input' => null,
                    );
        $expected = array(
                        'boolean_required_input' => true,
                    );

        $this->_run_default_value_test('boolean', $input, $expected);
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

    function _run_default_value_test($type_name, $input, $expected)
    {
        //    test with no plugin 
        $this->_setInputValue($expected);
        $af = $this->_get_validated_af_default($type_name);
        $ae = $this->_get_action_error();

        //    finally assert!
        $this->_assert_default($expected, $af, $ae);

        //    test with plugin 
        $af = $this->_get_validated_af_default($type_name, true);
        $ae = $this->_get_action_error();

        //    finally assert!(with plugin)
        $this->_assert_default($expected, $af, $ae);

        //    unset input value
        $this-> _unsetInputValue($expected);
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
            $result = true;
            if ($test_error) {
                $result = $this->assertTrue($ae->isError($key));
            } else {
                $result = $this->assertFalse($ae->isError($key));
            }

            if (!$result) {
                $this->dump("Input Key: $key "
                          . "Input : $value "
                          . "error_test: " . var_export($test_error, true)
                );
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
}
// }}}

?>
