<?php
/** 
 * 
 * 
 */

class Ethna_Plugin_Test extends Ethna_UnitTestBase
{
    // plugin object
    var $p;

    function setUp()
    {
        $this->p =  $this->ctl->getPlugin();
    }

    function tearDown()
    {
    }

    function test_import()
    {
        $this->assertFalse(class_exists('Ethna_Plugin_Cachemanager'));
        $this->assertFalse(class_exists('Ethna_Plugin_Cachemanager_Localfile'));
        Ethna_Plugin::import("Cachemanager", "Localfile");
        $this->assertTrue(class_exists('Ethna_Plugin_Cachemanager'));
        $this->assertTrue(class_exists('Ethna_Plugin_Cachemanager_Localfile'));
    }

    function test_plugin_utility()
    {
        $p =  $this->p;

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

        $types = $p->searchAllPluginType();
        $this->assertEqual(
            $types,
            array(
                'Cachemanager',
                'Csrf',
                'Generator',
                'Handle',
                'Logwriter',
                'Smarty',
                'Validator',
            )
        );


    }

    function test_getPlugin()
    {
        $p =  $this->p;

        //$this->assertFalse($p->obj_registry);
        $this->assertTrue($p->getPlugin('Cachemanager', 'Localfile') instanceof Ethna_Plugin_Cachemanager_Localfile);
        $this->assertTrue($p->obj_registry['Cachemanager']['Localfile'] instanceof Ethna_Plugin_Cachemanager_Localfile);
    }


}
