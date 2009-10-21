<?php
/**
 *  ActionError_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

//{{{    Ethna_ActionError_Test
/**
 *  Test Case For Ethna_ActionError
 *
 *  @access public
 */
class Ethna_ActionError_Test extends Ethna_UnitTestBase
{
    var $ae;
    var $error_obj;
    var $error_form_name;
    var $error_form_name1;
    var $message;
    var $message1;

    function setUp()
    {
        $this->ae = new Ethna_ActionError();
        $this->error_form_name = "hoge";
        $this->message = "test error";    
        $this->error_form_name1 = "tititi";
        $this->message1 = "test error1";    

        $this->error_obj = new Ethna_Error(
                               $this->message1,
                               E_NOTICE,
                               E_GENERAL
                           );

        //    add dummy error object.
        $this->ae->add($this->error_form_name,
                       $this->message,
                       E_GENERAL
        );
        $this->ae->addObject($this->error_form_name1,
                             $this->error_obj
        );
    }

    function test_count()
    {
        $this->assertEqual($this->ae->count(), 2);
    }

    function test_length()
    {
        $this->assertEqual($this->ae->length(), 2);
    }

    function test_iserror()
    {
        $this->assertTrue(
            $this->ae->isError($this->error_form_name)
        );
        $this->assertTrue(
            $this->ae->isError($this->error_form_name1)
        );
    }

    function test_geterrorlist()
    {
        $this->assertTrue(
            is_array($this->ae->getErrorList())
        );
    }

    function test_getmessage()
    {
        $error_msg = $this->ae->getMessage(
                         $this->error_form_name
                     );
        $error_msg1 = $this->ae->getMessage(
                         $this->error_form_name1
                     );

        $this->assertEqual($this->message, $error_msg); 
        $this->assertEqual($this->message1, $error_msg1); 
    }

    function test_clear()
    {
        $this->ae->clear();
        $this->assertTrue(
            $this->ae->count() == 0
        );
    }

}

