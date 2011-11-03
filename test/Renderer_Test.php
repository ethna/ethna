<?php
/**
 *  Renderer_Test.php
 */

/**
 *  Test case for Ethna_Renderer
 *
 *  @access public
 */
class Ethna_Renderer_Test extends Ethna_UnitTestBase
{
    private $before_config;

    function setUp()
    {
        $this->original_ctl = $GLOBALS['_Ethna_controller'];

        $ctl = new Ethna_Renderer_Test_Controller();
        $config = $ctl->getConfig();
        $renderer_config = array(
            'mock' => array(
                'config1' => 'the_config',
                'config2' => 2,
            ),
        );
        $config->set('renderer', $renderer_config);

        // TODO : This property will be protected in the future?

        $this->renderer_ctl = $ctl;
        $this->renderer = $ctl->getRenderer();
    }

    public function tearDown()
    {
        $GLOBALS['_Ethna_controller'] = $this->original_ctl;
    }

    public function test_Name()
    {
        $this->assertEqual('mock', $this->renderer->getName());

        $r = new Ethna_Renderer(new Ethna_Controller());
        $this->assertEqual('ethna', $r->getName());
    }

    public function test_getEngine()
    {
        $this->assertNull($this->renderer->getEngine());
    }

    // This test result depends if the const BASE (the application base directory) defined.
    // If BASE defined, Ethna_Renderer::getTemplateDir() returns an absolute path else a relative path.
    /*
    public function test_getTemplateDir()
    {
        // template_dir is a string concat with controller's template dir and locale setting
        //$this->assertEqual('template/ja_JP', $this->renderer->getTemplateDir());
    }
     */

    public function test_prop()
    {
        $r = $this->renderer;

        $this->assertNull($r->getProp('miyazaki'));
        $r->setProp('miyazaki', 'aoi');
        $this->assertEqual('aoi', $r->getProp('miyazaki'));
        $r->removeProp('miyazaki');
        $this->assertNull($r->getProp('miyazaki'));

        $prop = array(
            'miyazaki' => 'aoi',
            'tabe' => 'mikako',
        );
        $r->setPropArray($prop);
        $this->assertEqual('aoi', $r->getProp('miyazaki'));
        $this->assertEqual('mikako', $r->getProp('tabe'));

        $add_prop = array(
            'miyazaki' => 'goro',
            'kitano' => 'kie',
        );
        $r->setPropArray($add_prop);
        $this->assertEqual('goro', $r->getProp('miyazaki'));
        $this->assertEqual('mikako', $r->getProp('tabe'));
        $this->assertEqual('kie', $r->getProp('kitano'));

        $r->removeProp('miyazaki');
        $r->removeProp('tabe');
        $r->removeProp('kitano');
    }

    public function test_propRef()
    {
        $r = $this->renderer;
        $this->assertNull($r->getProp('miyazaki'));
        $this->assertNull($r->getProp('tabe'));

        $prop = array(
            'miyazaki' => 'aoi',
            'tabe' => 'mikako',
        );
        $r->setPropArrayByRef($prop);
        $this->assertEqual('aoi', $r->getProp('miyazaki'));
        $this->assertEqual('mikako', $r->getProp('tabe'));

        $new_prop = 'goro';
        $r->setPropByRef('miyazaki', $new_prop);
        $this->assertEqual('goro', $r->getProp('miyazaki'));

        //$r->removeProp('miyazaki');
        $r->removeProp('tabe');
    }

    public function test_RendererConfig()
    {
        $config = $this->renderer->getConfig();
        $this->assertEqual('the_config', $config['config1']);
        $this->assertEqual(2, $config['config2']);
        $this->assertEqual(true, $config['config3']);
    }

    public function test_template()
    {
        $r = $this->renderer;

        $template_name = 'test.php';
        $r->setTemplate($template_name);
        $this->assertEqual($template_name, $r->getTemplate());
    }

    public function test_loadEngine()
    {
        // not implemented yet
    }

    public function test_perform()
    {
        $r = $this->renderer;

        $prop = array(
            'miyazaki' => 'aoi',
            'tabe' => 'mikako',
        );
        $r->setPropArrayByRef($prop);
        $this->assertEqual('aoi', $r->getProp('miyazaki'));
        $this->assertEqual('mikako', $r->getProp('tabe'));

        $e = $r->perform();
        $this->assertIsA($e, 'Ethna_Error');
        $this->assertEqual('template is not defined', $e->getMessage());

        $template_name = 'test.php';
        $r->setTemplate($template_name);
        $e = $r->perform();
        $this->assertIsA($e, 'Ethna_Error');
        $this->assertPattern('/^template is not found: (.+)$/', $e->getMessage());

        $e = $r->perform($template_name);
        $this->assertIsA($e, 'Ethna_Error');
        $this->assertPattern('/^template is not found: (.+)$/', $e->getMessage());

        $template_dir = dirname(__FILE__) . '/data/template';
        $r->setTemplateDir($template_dir);
        $this->assertEqual($template_dir . '/', $r->getTemplateDir());
        $this->assertTrue($r->templateExists($template_name));
        ob_start();
        $r->perform();
        $content = ob_get_clean();

        $expected = "miyazaki: aoi.\ntabe: mikako.\n";
        $this->assertEqual($expected, $content);

        // capture
        $this->assertEqual($expected, $r->perform(null, true));

        // display
        ob_start();
        $r->display();
        $content = ob_get_clean();
        $this->assertEqual($expected, $content);
    }
}

class Ethna_Renderer_Test_Controller
    extends Ethna_Controller
{
    public $class = array(
        // use for test
        'renderer' => 'Ethna_Renderer_Mock',
    );
}

class Ethna_Renderer_Mock extends Ethna_Renderer
{
    protected $config_default = array(
        'config1' => 'config',
        'config2' => 1,
        'config3' => true,
    );

    public function getName()
    {
        return 'mock';
    }

    protected $engine_path = 'Renderer_Test.php';
}
