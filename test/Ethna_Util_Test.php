<?php
/**
 *  Ethna_Util_Test.php
 */

/**
 *  Ethna_Utilクラスのテストケース(1)
 *
 *  @access public
 */
class Ethna_Util_Test extends UnitTestCase
{
    function testCheckMailAddress()
    {
        $util = new Ethna_Util;
        $result = $util->checkMailAddress('hogefuga.net');
        $this->assertFalse($result);
        $result = $util->checkMailAddress('hoge@fuga.net');
        $this->assertTrue($result);
    }
}
?>
