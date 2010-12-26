<?php
// vim: foldmethod=marker
/**
 *  ClassFactory_Test.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/test/MocktestManager.php';

//{{{    Ethna_ClassFactory_Test
/**
 *  Test Case For Ethna_ClassFactory_Test 
 *
 *  @access public
 */
class Ethna_ClassFactory_Test extends Ethna_UnitTestBase
{
    var $cf;

    function setUp()
    {
        $ctl = new Ethna_Controller();
        $this->cf = $ctl->getClassFactory();
    }

    //    Ethna_Controller と Ethna_ClassFactory は
    //    循環参照している。PHP4では、循環参照しているオブジェクト同士を
    //    比較しようとすると延々再帰的にプロパティと値を比較しようとする
    //    ため Fatal Error を起こす。よって、PHP5以降でのみ以下はテストする
    //    @see http://www.php.net/manual/en/language.oop.object-comparison.php
    //    @see http://www.php.net/manual/en/language.oop5.object-comparison.php

    function test_getManager()
    {
        //    大文字小文字を区別されても、
        //    同じインスタンスを返さなければ
        //    ならない
        if (version_compare(phpversion(), '5', '>=')) {
            $manager = $this->cf->getManager('mocktest');
            $manager_alt = $this->cf->getManager('Mocktest');
            $this->assertTrue($manager === $manager_alt);
    
            //    weakパラメータが指定された場合は 
            //    強制的に違うオブジェクトを返さなければならない
            $manager = $this->cf->getManager('mocktest');
            $manager_alt = $this->cf->getManager('Mocktest', true);
            $this->assertFalse($manager === $manager_alt);
        }
    }
}
// }}}

