<?php
/**
 *  Class_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

class Dummy_Ethna_Error extends Ethna_Error
{
    //  nothing defined.
}

function dummy_error_callback_global($error)
{
    $GLOBALS['_dummy_error_callback_global'] = $error->getMessage();
}

//{{{    Ethna_Test
/**
 *  Test Case For Ethna class
 *
 *  @access public
 */
class Ethna_Class_Test extends Ethna_UnitTestBase
{
    var $dummy_error_value_class;

    function setUp()
    {
        $GLOBALS['_dummy_error_callback_global'] = NULL;
        $this->dummy_error_value_class = NULL;
        Ethna::clearErrorCallback();
    }

    function dummy_error_callback_obj(&$error)
    {
        $this->dummy_error_value_class = $error->getMessage();
    }

    //{{{  isError test
    function test_isError()
    {
        $error = new Ethna_Error();
        $this->assertTrue(Ethna::isError($error));

        $error = 'this is not object, but string.';
        $this->assertFalse(Ethna::isError($error));

        $error = new Dummy_Ethna_Error('Error Message', E_CACHE_GENERAL,
                                 ETHNA_ERROR_DUMMY, E_USER_ERROR,
                                 NULL, 'Ethna_Error'
                 );
        $this->assertFalse(Ethna::isError($error, E_FORM_REQUIRED));
        $this->assertTrue(Ethna::isError($error, E_CACHE_GENERAL));

        $error = new stdClass();
        $this->assertFalse(Ethna::isError($error));

        $error = NULL;
        $this->assertFalse(Ethna::isError($error));

        //   Ethna はPEARに依存しないので、
        //   PEAR_Error を渡してもfalse が返らなければならない
        $fp = @fopen('PEAR.php', 'r', true);
        if ($fp !== false) {
            require_once 'PEAR.php';
            $error = new PEAR_Error();
            $this->assertFalse(Ethna::isError($error));
        }
        fclose($fp);
    }
    // }}}

    //{{{  raiseError test
    function test_raiseError()
    {
        $error = Ethna::raiseError('Error!!!!!');
        $this->assertEqual('Error!!!!!', $error->getMessage());
        $this->assertEqual(E_USER_ERROR, $error->getLevel());
        $this->assertEqual(E_GENERAL, $error->getCode());     

        $error = Ethna::raiseError('Error', E_CACHE_GENERAL);
        $this->assertEqual(E_CACHE_GENERAL, $error->getCode());     
    }
    // }}}

    //{{{  raiseWarning test
    function test_raiseWarning()
    {
        $error = Ethna::raiseWarning('Error!!!!!');
        $this->assertEqual('Error!!!!!', $error->getMessage());
        $this->assertEqual(E_USER_WARNING, $error->getLevel());
        $this->assertEqual(E_GENERAL, $error->getCode());     

        $error = Ethna::raiseWarning('Error!!!!!', E_CACHE_GENERAL);
        $this->assertEqual(E_CACHE_GENERAL, $error->getCode());     
    }
    // }}}

    //{{{  raiseNotice test
    function test_raiseNotice()
    {
        $error = Ethna::raiseNotice('Error!!!!!');
        $this->assertEqual('Error!!!!!', $error->getMessage());
        $this->assertEqual(E_USER_NOTICE, $error->getLevel());
        $this->assertEqual(E_GENERAL, $error->getCode());     

        $error = Ethna::raiseNotice('Error!!!!!', E_CACHE_GENERAL);
        $this->assertEqual(E_CACHE_GENERAL, $error->getCode());     
    }
    // }}}

    //{{{  callback test
    function test_error_callback_obj()
    {
        $this->assertNULL($GLOBALS['_dummy_error_callback_global']);
        $this->assertNULL($this->dummy_error_value_class);

        //   array の場合は クラス名|オブジェクト + method
        Ethna::setErrorCallback(array(&$this, 'dummy_error_callback_obj'));
        Ethna::raiseError('dummy_error_obj!!!');
        $this->assertEqual('dummy_error_obj!!!', $this->dummy_error_value_class);
        $this->assertNULL($GLOBALS['_dummy_error_callback_global']);
    }

    function test_error_callback_global()
    {
        $this->assertNULL($GLOBALS['_dummy_error_callback_global']);
        $this->assertNULL($this->dummy_error_value_class);

        //   string の場合はグローバル関数 
        Ethna::setErrorCallback('dummy_error_callback_global');
        Ethna::raiseError('dummy_error_global!!!');
        $this->assertEqual('dummy_error_global!!!', $GLOBALS['_dummy_error_callback_global']);
    }

    function test_error_callback_mixed()
    {
        //   string の場合はグローバル関数 
        Ethna::setErrorCallback('dummy_error_callback_global');
        Ethna::setErrorCallback(array(&$this, 'dummy_error_callback_obj'));
        Ethna::raiseError('dummy_error_global!!!');
        $this->assertEqual('dummy_error_global!!!', $GLOBALS['_dummy_error_callback_global']);
        $this->assertEqual('dummy_error_global!!!', $this->dummy_error_value_class);
    }
    // }}}
}
// }}}

