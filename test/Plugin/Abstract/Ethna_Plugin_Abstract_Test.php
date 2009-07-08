<?php
/**
 *  Ethna_Plugin_Abstract_Test.php
 */

/**
 *  Ethna_Plugin_Abstract クラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Abstract_Test extends Ethna_UnitTestBase
{
    var $plugin;
    var $lw;
    var $abstract;

    function setUp()
    {
        $this->plugin =& $this->ctl->getPlugin();

        // for PHP 5, it's not enable to create instance of abstract class,
        // now this is temporary process.
        $this->abstract = $this->plugin->getPlugin('Abstract', null);

        $this->lw = $this->plugin->getPlugin('Logwriter', 'Echo');

    }

    function testDetectTypeAndName()
    {
        $this->assertEqual('Abstract', $this->abstract->getType());
        $this->assertEqual(null, $this->abstract->getName());

        $this->assertEqual('Logwriter', $this->lw->getType());
        $this->assertEqual('Echo', $this->lw->getName());
    }
}
?>
