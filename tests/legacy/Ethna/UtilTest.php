<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_UtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function CheckMailAddress()
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

        $result = Ethna_Util::checkMailAddress('hogefuga.net');
        $this->assertFalse($result);

        $result = Ethna_Util::checkMailAddress('hoge@fuga.net');
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function isAbsolute()
    {
        if (ETHNA_OS_WINDOWS) {
            $absolute_paths = array(
                'D:\root',
                'C:\home\user\giza',
            );
        } else {
            $absolute_paths = array(
                '/root',
                '/home/user/giza',
            );
        }

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

    /**
     * @test
     */
    public function isRootDir()
    {
        // MEMO(chobie): なしてわざわざチェックしているのか
        $this->assertTrue((bool)DIRECTORY_SEPARATOR);

        if (ETHNA_OS_WINDOWS) {
            $this->assertTrue(Ethna_Util::isRootDir("C:\\"));
            $this->assertFalse(Ethna_Util::isRootDir("C:\\Program Files\\hoge\\fuga.txt"));
            $this->assertFalse(Ethna_Util::isRootDir("C:\\Program Files\\hoge"));
            $this->assertFalse(Ethna_Util::isRootDir("C:\\hoge\\"));
            $this->assertFalse(Ethna_Util::isRootDir("C:\\hoge.txt"));
        } else {
            $this->assertFalse(Ethna_Util::isRootDir("/home/ethna/test.txt"));
            $this->assertFalse(Ethna_Util::isRootDir("/home/ethna/"));
            $this->assertFalse(Ethna_Util::isRootDir("/home/ethna"));
            $this->assertFalse(Ethna_Util::isRootDir("/test.txt"));
        }
    }

    /**
     * @test
     */
    public function getRandom()
    {
        //    いかなる状態であっても
        //    値が得られなければならない
        $r = Ethna_Util::getRandom();
        $this->assertEquals(64, strlen($r));
    }

    public function testGetEra()
    {
        unset($GLOBALS['_Ethna_controller']);

        // NOTE(chobie): Ethna_Utilは内部的にControllerを使う
        $controller = new Ethna_Controller_Dummy();

        //  昭和63年
        $last_showa_t = mktime(0,0,0,12,31,1988);
        $r = Ethna_Util::getEra($last_showa_t);
        $this->assertEquals('昭和', $r[0]);
        $this->assertEquals(63, $r[1]);

        //  平成元年
        $first_heisei_t = mktime(0,0,0,1,1,1989);
        $r = Ethna_Util::getEra($first_heisei_t);
        $this->assertEquals('平成', $r[0]);
        $this->assertEquals(1, $r[1]);
    }

}