<?php
// vim: foldmethod=marker
/**
 *  Ethna_Controller_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

//{{{    Ethna_Controller_Test
/**
 *  Test Case For Ethna_Controller_Test 
 *
 *  @access public
 */
class Ethna_Controller_Test extends Ethna_UnitTestBase
{
    var $test_ctl;

    function setUp()
    {
        $this->test_ctl =& new Ethna_Controller();
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    // {{{ test_getClientEncoding
    function test_getClientEncoding()
    {
        $this->assertEqual('UTF-8', $this->test_ctl->getClientEncoding());
    }
    // }}} 

    // {{{ test_setClientEncoding
    function test_setClientEncoding()
    {
        $this->test_ctl->setClientEncoding('Shift_JIS');
        $this->assertEqual('Shift_JIS', $this->test_ctl->getClientEncoding());
    }
    // }}}

}
// }}}

?>
