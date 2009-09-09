<?php
/** 
 * 
 * 
 */

class Ethna_Plugin_Test extends Ethna_UnitTestBase
{
    function setUp()
    {
    }

    function tearDown()
    {
    }

    function test_plugin_utility()
    {
        $p =  $this->ctl->getPlugin();

        // getPluginNaming
        $type = 'Cachemanager';
        $name = 'Localfile';
        $this->assertEqual(array('Ethna_Plugin_Cachemanager', 'Cachemanager.php'), $p->getPluginNaming($type));
        $this->assertEqual(array('Ethna_Plugin_Cachemanager_Localfile', 'Localfile.php'), $p->getPluginNaming($type, $name));

        // _searchPluginSrcDir
        // テスト実行時 extlib, app dir には $type, $name のプラグインがないものとして
        $plugin_dir =  ETHNA_BASE . "/class/Plugin";
        $dir = realpath($p->_searchPluginSrcDir($type));
        $this->assertEqual($plugin_dir, $dir);
        $dir = realpath($p->_searchPluginSrcDir($type, $name));
        $this->assertEqual($plugin_dir . "/Cachemanager", $dir);

    }

    function test_import()
    {
        Ethna_Plugin::import("Cachemanager", "Localfile");
        $this->assertTrue(class_exists('Ethna_Plugin_Cachemanager'));
        $this->assertTrue(class_exists('Ethna_Plugin_Cachemanager_Localfile'));
    }

}
