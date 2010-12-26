<?php
/**
 *  Plugin_Test.php
 *
 *  @package    Ethna
 *  @author     Sotaro Karasawa <sotaro.k /at/ gmail.com>
 */

require_once ETHNA_INSTALL_BASE . '/test/MockProject.php';

class Ethna_Plugin_Test extends Ethna_UnitTestBase
{
    // plugin object
    var $p;

    // mock project
    var $project;

    function setUp()
    {
        $this->p =  $this->ctl->getPlugin();

        $this->project = new Ethna_MockProject();
        $this->project->create();
    }

    function tearDown()
    {
        // unload from obj_registry because some plugin tests run after this.
        $mock_controller = $this->project->getController();
        $mock_controller->getPlugin()->_unloadPlugin('Cachemanager', 'Localfile');
        $mock_controller->getPlugin()->_unloadPlugin('Cachemanager', 'Memcache');

        $this->project->delete();
        unset($GLOBALS['_Ethna_controller']);

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

    }

    function test_getPlugin()
    {
        $p =  $this->p;

        //$this->assertFalse($p->obj_registry);
        $this->assertTrue($p->getPlugin('Cachemanager', 'Localfile') instanceof Ethna_Plugin_Cachemanager_Localfile);
        $this->assertTrue($p->obj_registry['Cachemanager']['Localfile'] instanceof Ethna_Plugin_Cachemanager_Localfile);
    }

    function test_preloadPlugin()
    {
        $action_name = 'pluginpreload';
        $action_skel = ETHNA_TEST_SKELDIR . 'skel.action.pluginpreload.php';
        $this->project->runCmd(
            'add-action',
            array(
                '-s',
                $action_skel,
                $action_name,
            )
        );
        $result = $this->project->runMain($action_name);
        $mock_controller = $this->project->getController();
        $mock_actionclass = $mock_controller->getBackend()->getActionClass();

        // preloader settings:
        //   var $plugins = array(
        //       'Cachemanager_Localfile',
        //       'm' => 'Cachemanager_Memcache',
        //   );
        $this->assertTrue($mock_actionclass->plugin->Cachemanager_Localfile instanceof Ethna_Plugin_Cachemanager_Localfile);
        $this->assertTrue($mock_actionclass->plugin->m instanceof Ethna_Plugin_Cachemanager_Memcache);

    }


}
