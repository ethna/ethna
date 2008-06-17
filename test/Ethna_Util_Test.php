<?php
// vim: foldmethod=marker
/**
 *  Ethna_Util_Test.php
 */

/**
 *  Ethna_Utilクラスのテストケース
 *
 *  @access public
 */
class Ethna_Util_Test extends Ethna_UnitTestBase
{
    // {{{  testCheckMailAddress
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
    // }}}

    // {{{  testIsAbsolute
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
    // }}}

    // {{{  testGetRandom
    function testGetRandom()
    {
        //    いかなる状態であっても
        //    値が得られなければならない
        $r = Ethna_Util::getRandom();
        $this->assertNotNULL($r);
    }
    // }}}

    // {{{ testGetEra
    function testGetEra()
    {
        unset($GLOBALS['_Ethna_controller']);
        $tmp_ctl =& new Ethna_Controller();
        
        //  昭和63年
        $last_showa_t = mktime(0,0,0,12,31,1988);
        $r = Ethna_Util::getEra($last_showa_t);
        $this->assertEqual('昭和', $r[0]);
        $this->assertEqual(63, $r[1]);

        //  平成元年
        $first_heisei_t = mktime(0,0,0,1,1,1989);
        $r = Ethna_Util::getEra($first_heisei_t);
        $this->assertEqual('平成', $r[0]);
        $this->assertEqual(1, $r[1]);
    }
    // }}}
}

?>
