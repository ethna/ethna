<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Cachemanager_Memcache_Test extends PHPUnit_Framework_TestCase
{

    protected $cm;

    public function setUp()
    {
        $ctl = new Ethna_Controller_Dummy();
        $plugin = $ctl->getPlugin();
        $config = $ctl->getConfig();
        $config->set('plugin',
            array('cachemanager' =>
                array('memcache' => array(
                    'host' => 'localhost',
                    'port' => 11211,
                    'use_pconnect' => false,
                    'retry' => 4,
                    'timeout' => 5,

                    //sample-2: multiple memcache servers (distributing w/ namespace and ids)
                    'info' => array(
                        'namespace1' => array(
                            0 => array(
                                'host' => 'cache1.example.com',
                                'port' => 11211,
                            ),
                            1 => array(
                                'host' => 'cache2.example.com',
                                'port' => 11211,
                            ),
                        ),
                    ),
                )
                )
            ));

        $this->cm = $plugin->getPlugin('Cachemanager', 'Memcache');
    }

    public function testMemcacheConfig()
    {
        $config = $this->cm->getConfig();

        //$config = $this->cm->config;

        $this->assertEquals(11211, $config['port']);
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals(4, $config['retry']);
        $this->assertEquals(5, $config['timeout']);
    }
}
