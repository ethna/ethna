<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_LoggerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->controller = new Ethna_Controller_Dummy();
        $this->form = new Ethna_ActionForm_Dummy($this->controller);
        $this->controller->setActionForm($this->form);
        $this->ae = new Ethna_ActionError();
        
        // ConfigクラスをEthna_Logger_Test_Configに設定
        $this->controller->class['config'] = 'Ethna_Logger_Test_Config';
        $this->controller->getConfig();
    }

    public function tearDown()
    {
        // do nothing.
    }

    public function _resetLoggerSetting($config)
    {
        unset($this->controller->getClassFactory()->object['logger']);
        $config_obj = $this->controller->getClassFactory()->object['config'];
        $config_obj->config = $config;
    }

    /**
     *  old style log setting.
     */
    public function test_parseSetting_Compatible()
    {
        $config = array(
            'log_facility'      => 'echo',
            'log_level'         => 'warning',
            'log_option'        => 'pid,function,pos',
        );
        $this->_resetLoggerSetting($config);
        $this->logger = $this->controller->getLogger();

        // facility
        $facility = $this->logger->getLogFacility();
        $this->assertEquals($facility, 'echo'); // not array, but string (for B.C.)

        // level
        $level = $this->getNonpublicProperty($this->logger, 'level');
        $level_echo = $level['echo'];
        $this->assertEquals($level_echo, LOG_WARNING);

        // option
        $option = $this->getNonpublicProperty($this->logger, 'option');
        $option_echo = $option['echo'];
        $this->assertEquals($option_echo['pid'], true);
        $this->assertEquals($option_echo['function'], true);
        $this->assertEquals($option_echo['pos'], true);
    }

    /**
     *  structured style log setting.
     */
    public function test_parseSetting_Structured()
    {
        $config = array(
            'log' => array(
                'echo'  => array(
                    'level'         => 'warning',
                ),
                'file'  => array(
                    'level'         => 'notice',
                    'file'          => '/var/log/Ethna.log',
                    'mode'          => 0666,
                ),
                'alertmail'  => array(
                    'level'         => 'err',
                    'mailaddress'   => 'alert@ml.example.jp',
                ),
            ),
            'log_option'            => 'pid,function,pos',
        );
        $this->_resetLoggerSetting($config);
        $this->logger = $this->controller->getLogger();

        // facility
        $facility = $this->logger->getLogFacility();
        $this->assertEquals($facility, array('echo', 'file', 'alertmail'));

        // level
        $level = $this->getNonpublicProperty($this->logger, 'level');
        $level_echo = $level['echo'];
        $this->assertEquals($level_echo, LOG_WARNING);
        $level_file = $level['file'];
        $this->assertEquals($level_file, LOG_NOTICE);
        $level_alertmail = $level['alertmail'];
        $this->assertEquals($level_alertmail, LOG_ERR);

        // option
        $option = $this->getNonpublicProperty($this->logger, 'option');

        $option_echo = $option['echo'];
        $this->assertEquals($option_echo['pid'], true);
        $this->assertEquals($option_echo['function'], true);
        $this->assertEquals($option_echo['pos'], true);

        $option_file = $option['file'];
        $this->assertEquals($option_file['pid'], true);
        $this->assertEquals($option_file['function'], true);
        $this->assertEquals($option_file['pos'], true);
        $this->assertEquals($option_file['file'], '/var/log/Ethna.log');
        $this->assertEquals($option_file['mode'], 0666);

        $option_alertmail = $option['alertmail'];
        $this->assertEquals($option_alertmail['pid'], true);
        $this->assertEquals($option_alertmail['function'], true);
        $this->assertEquals($option_alertmail['pos'], true);
        $this->assertEquals($option_alertmail['mailaddress'], 'alert@ml.example.jp');
    }

    /**
     *  @todo   log level filter, begin(), log(), end()
     */
    //function test_etcetc()
    //{
    //    // not implemented yet.
    //}

    public function getNonpublicProperty($object, $property_name)
    {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $ref = new ReflectionProperty(get_class($object), $property_name);
            $ref->setAccessible(true);
            return $ref->getValue($object);
        } else {
            $arr = (array)$object;
            $key = $property_name;

            $ref = new ReflectionProperty(get_class($object), $property_name);
            if ($ref->isProtected()) {
                $key = "\0*\0".$key;
            } elseif ($ref->isPrivate()) {
                $key = "\0".get_class($object)."\0".$key;
            }

            return $arr[$key];
        }
    }

}

class Ethna_Logger_Test_Config extends Ethna_Config
{
    public function __construct_Config()
    {
        // do nothing.
    }
}