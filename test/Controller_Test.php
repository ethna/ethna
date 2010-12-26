<?php
// vim: foldmethod=marker
/**
 *  Controller_Test.php
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
        $this->test_ctl = new Ethna_Controller();
    }

    function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    // {{{ checkAppId
    function test_checkAppId()
    {
        //  予約語(app, ethna)は当然駄目
        //  これについては大文字、小文字を区別しない
        $r = $this->test_ctl->checkAppId('ethna');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('EthNa');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('ETHNA');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('app');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('ApP');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('APP');
        $this->assertTrue(Ethna::isError($r));

        //  数字で始まっては駄目
        $r = $this->test_ctl->checkAppId('1');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('0abcd');
        $this->assertTrue(Ethna::isError($r));

        //  始めがアンダースコアも駄目
        $r = $this->test_ctl->checkAppId('_');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('_abcd');
        $this->assertTrue(Ethna::isError($r));

        //  一文字でも英数字以外が混じれば駄目
        $r = $this->test_ctl->checkAppId('ab;@e');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('@bcde');
        $this->assertTrue(Ethna::isError($r));

        $r = $this->test_ctl->checkAppId('abcd:');
        $this->assertTrue(Ethna::isError($r));

        //  全部英数字であればOK
        $r = $this->test_ctl->checkAppId('abcd');
        $this->assertFalse(Ethna::isError($r));
    }
    // }}}

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

