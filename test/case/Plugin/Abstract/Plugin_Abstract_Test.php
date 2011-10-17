<?php
/**
 *  Plugin_Abstract_Test.php
 */

require_once ETHNA_BASE . '/class/Plugin/Abstract.php';

/**
 *  Ethna_Plugin_Abstract クラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Abstract_Test extends Ethna_UnitTestBase
{
    protected $plugin;
    protected $lw;

    function setUp()
    {
        $this->plugin = $this->ctl->getPlugin();

        $this->lw = $this->plugin->getPlugin('Logwriter', 'Echo');

    }

    function testDetectTypeAndName()
    {
        $this->assertEqual('logwriter', $this->lw->getType());
        $this->assertEqual('echo', $this->lw->getName());
    }
}

