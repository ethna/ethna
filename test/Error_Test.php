<?php
/**
 *  Error_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

//{{{    Ethna_Error_Test
/**
 *  Test Case For Ethna_Error
 *
 *  @access public
 */
class Ethna_Error_Test extends Ethna_UnitTestBase
{
    var $error;

    function setUp()
    {
        $this->error = Ethna::raiseError('general error');
    }

    function tearDown()
    {
        $error = NULL;
    }

    //{{{ getCode
    function test_getcode()
    {
        $this->assertEqual(E_GENERAL, $this->error->getCode());
    }
    //}}}

    //{{{ getLevel
    function test_getlevel()
    {
        $this->assertEqual(E_USER_ERROR, $this->error->getLevel());
    }
    //}}}

    //{{{ getMessage
    function test_getmessage()
    {
        $this->assertEqual('general error', $this->error->getMessage());
    }
    //}}}

    //{{{ setUserInfo, getUserInfo
    function test_userinfo()
    {
        $this->error->addUserInfo('foobarbaz');
        $this->error->addUserInfo('hoge');
        $this->assertEqual('foobarbaz', $this->error->getUserInfo(0));
        $this->assertEqual('hoge', $this->error->getUserInfo(1));

        $info = $this->error->getUserInfo();
        $this->assertEqual('foobarbaz', $info[0]);
        $this->assertEqual('hoge', $info[1]);
    }
    //}}}
}
// }}}

