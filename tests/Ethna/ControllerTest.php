<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ControllerTest extends PHPUnit_Framework_TestCase
{
    /** @var  Ethna_Controller $controller */
    protected $controller;

    public function setup()
    {
        $this->controller = new Ethna_Controller_Dummy();
    }

    public function tearDown()
    {
        unset($GLOBALS['_Ethna_controller']);
    }

    /**
     * @test
     * @dataProvider providesAppIdTestCases
     */
    public function checkAppId($expected, $appid, $message)
    {
        $this->assertEquals($expected,
            Ethna::isError($this->controller->checkAppId($appid)),
            $message
        );
    }

    /**
     * @test
     */
    public function getClientEncoding()
    {
        $this->assertEquals('UTF-8', $this->controller->getClientEncoding());
    }

    /**
     * @test
     */
    function setClientEncoding()
    {
        $this->controller->setClientEncoding('Shift_JIS');
        $this->assertEquals('Shift_JIS', $this->controller->getClientEncoding());
    }

    public function providesAppIdTestCases()
    {
        return array(
            array(true, "ethna", "予約語(app, ethna)は当然駄目"),
            array(true, "EthNa", "予約語(app, ethna)は当然駄目"),
            array(true, "ETHNA", "予約語(app, ethna)は当然駄目"),
            array(true, "app",   "予約語(app, ethna)は当然駄目"),
            array(true, "ApP",   "予約語(app, ethna)は当然駄目"),
            array(true, "APP",   "予約語(app, ethna)は当然駄目"),
            array(true, "1",     "数字で始まっては駄目"),
            array(true, "0abcd", "数字で始まっては駄目"),
            array(true, "_",     "始めがアンダースコアも駄目"),
            array(true, "_abcd", "始めがアンダースコアも駄目"),
            array(true, "ab;@e", "一文字でも英数字以外が混じれば駄目"),
            array(true, "@bcde", "一文字でも英数字以外が混じれば駄目"),
            array(true, "abcd:", "一文字でも英数字以外が混じれば駄目"),
            array(false, "abcd", "全部英数字であればOK(本当に？)"),
        );
    }
}