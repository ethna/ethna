<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_ErrorTest extends PHPUnit_Framework_TestCase
{
    public  $error;

    public function setUp()
    {
        $this->error = Ethna::raiseError('general error');
    }

    public function tearDown()
    {
        $error = NULL;
    }

    public function test_getcode()
    {
        $this->assertEquals(E_GENERAL, $this->error->getCode());
    }

    public function test_getlevel()
    {
        $this->assertEquals(E_USER_ERROR, $this->error->getLevel());
    }

    public function test_getmessage()
    {
        $this->assertEquals('general error', $this->error->getMessage());
    }

    public function test_userinfo()
    {
        $this->error->addUserInfo('foobarbaz');
        $this->error->addUserInfo('hoge');
        $this->assertEquals('foobarbaz', $this->error->getUserInfo(0));
        $this->assertEquals('hoge', $this->error->getUserInfo(1));

        $info = $this->error->getUserInfo();
        $this->assertEquals('foobarbaz', $info[0]);
        $this->assertEquals('hoge', $info[1]);
    }
}

