<?php
/**
 *  Ethna_Util_Test.php
 */

/**
 *  Ethna_Utilクラスのテストケース(1)
 *
 *  @access public
 */
class Ethna_Util_Test extends Ethna_UnitTestBase
{
    function testIsRootDir()
    {
        $this->assertTrue(DIRECTORY_SEPARATOR);

        $util = new Ethna_Util;
        if (OS_WINDOWS) {
            $this->assertTrue($util->isRootDir("C:\\"));
            $this->assertFalse($util->isRootDir("C:\\Program Files\\hoge\\fuga.txt"));
            $this->assertFalse($util->isRootDir("C:\\Program Files\\hoge"));
            $this->assertFalse($util->isRootDir("C:\\hoge\\"));
            $this->assertFalse($util->isRootDir("C:\\hoge.txt"));
        } else {
            $this->assertFalse($util->isRootDir("/home/ethna/test.txt"));
            $this->assertFalse($util->isRootDir("/home/ethna/"));
            $this->assertFalse($util->isRootDir("/home/ethna"));
            $this->assertFalse($util->isRootDir("/test.txt"));
        }
    }


    function testCheckMailAddress()
    {
        $fail_words = array(
            'hogehuga.net',
            'untarakantara',
            'example@example',
            'example@.com',
            'example@example@example.com',
        );

        foreach ($fail_words as $word) {
            $this->assertFalse(Ethna_Util::checkMailAddress($word));
        }
        
        $util = new Ethna_Util;
        $result = $util->checkMailAddress('hogefuga.net');
        $this->assertFalse($result);

        $result = $util->checkMailAddress('hoge@fuga.net');
        $this->assertTrue($result);
    }

    function testIsAbsolute()
    {
        $absolute_paths = array(
            '/root',
            '/home/user/giza',
        );

        $invalid_params = array(
            '',
            false,
            true,
            '0x1',
        );

        foreach ($absolute_paths as $path) {
            $this->assertTrue(Ethna_Util::isAbsolute($path));
        }
        
        foreach ($invalid_params as $path) {
            $this->assertFalse(Ethna_Util::isAbsolute($path));
        }
     }
}
?>
