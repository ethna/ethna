<?php
/**
 *  Plugin_Cachemanager_Memcache_Test.php
 */

/**
 *  Ethna_Plugin_Cachemanager_Memcache クラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Cachemanager_Memcache_Test extends Ethna_UnitTestBase
{

    var $cm;

    function setUp()
    {
        $ctl = Ethna_Controller::getInstance();
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

    function testMemcacheConfig()
    {
        $config = $this->cm->getConfig();

        //$config = $this->cm->config;

        $this->assertEqual(11211, $config['port']);
        $this->assertEqual('localhost', $config['host']);
        $this->assertEqual(4, $config['retry']);
        $this->assertEqual(5, $config['timeout']);
    }
}
