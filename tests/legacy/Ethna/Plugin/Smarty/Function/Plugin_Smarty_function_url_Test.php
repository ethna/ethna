<?php
// vim: foldmethod=marker
/**
 *  Plugin_Smarty_function_url_Test.php
 *
 *  @author     Milly <milly.ca@gmail.com>
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Plugin/Smarty/function.url.php';

//{{{    Ethna_Plugin_Smarty_function_url_Test
/**
 *  Test Case For function.url.php
 *
 *  @access public
 */
class Ethna_Plugin_Smarty_function_url_Test extends PHPUnit_Framework_TestCase
{
    var $url_handler;

    function setUp()
    {
        $this->ctl = $ctl = new Ethna_Plugin_Smarty_function_url_Test_Controller();
        $this->url_handler = $ctl->getUrlHandler();
        $this->url_handler->setActionMap($this->_simple_map);
    }

    // {{{ $_simple_map
    public $_simple_map = array(
        'entrypoint' => array(
            'test_foo_bar' => array(
                'path'          => 'foo/bar',
                'path_regexp'   => false,
                'path_ext'      => false,
            ),
        ),
        'emptypath' => array(
            'test_empty_path' => array(
                'path'          => '',
                'path_regexp'   => false,
                'path_ext'      => false,
            ),
        ),
    );
    // }}}

    // {{{ test_smarty_function_url_noentry
    function test_smarty_function_url_noentry()
    {
        $params = array('action' => 'test_noentry',
                        'anchor' => '',
                        'scheme' => '',
                        'param1' => 'hoge',
                        'param2' => 'huga',
                  );
        $dummy_smarty = null;
        $expected = "?param1=hoge&param2=huga&action_test_noentry=true";

        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);
    }
    // }}}

    // {{{ test_smarty_function_url_schema
    function test_smarty_function_url_schema()
    {
        $url = 'http://example.net/';
        $params = array('action' => 'test_noentry',
                        'anchor' => '',
                        'scheme' => '',
                        'param1' => 'hoge',
                        'param2' => 'huga',
                  );
        $dummy_smarty = null;
        $expected = "http://example.net/?param1=hoge&param2=huga&action_test_noentry=true";

        $this->ctl->setURL($url);
        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);

        // http -> https
        $url = 'http://example.net/';
        $params['scheme'] = 'https';
        $expected = "https://example.net/?param1=hoge&param2=huga&action_test_noentry=true";

        $this->ctl->setURL($url);
        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);
    }
    // }}}

    // {{{ test_smarty_function_url_anchor
    function test_smarty_function_url_anchor()
    {
        $params = array('action' => 'test_noentry',
                        'anchor' => 'baz',
                        'scheme' => '',
                        'param1' => 'hoge',
                        'param2' => 'huga',
                  );
        $dummy_smarty = null;
        $expected = "?param1=hoge&param2=huga&action_test_noentry=true#baz";

        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);
    }
    // }}}

    // {{{ test_smarty_function_url_path
    function test_smarty_function_url_path()
    {
        $params = array('action' => 'test_foo_bar',
                        'anchor' => '',
                        'scheme' => '',
                        'param1' => 'hoge',
                        'param2' => 'huga',
                  );
        $dummy_smarty = null;
        $expected = "entrypoint/foo/bar?param1=hoge&param2=huga";

        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);
    }
    // }}}

    // {{{ test_smarty_function_url_emptypath
    function test_smarty_function_url_emptypath()
    {
        $params = array('action' => 'test_empty_path',
                        'anchor' => '',
                        'scheme' => '',
                        'param1' => 'hoge',
                        'param2' => 'huga',
                  );
        $dummy_smarty = null;
        $expected = "?param1=hoge&param2=huga";

        $actual = smarty_function_url($params, $dummy_smarty);
        $this->assertEquals($expected, $actual);
    }
    // }}}
}
// }}}

// {{{ Ethna_Plugin_Smarty_function_url_Test_Controller
class Ethna_Plugin_Smarty_function_url_Test_Controller
    extends Ethna_Controller
{
    public $class = array(
        // use for test
        'url_handler' => 'Ethna_Plugin_Smarty_function_url_Test_UrlHandler',
    );
    public $directory= array(
        "plugin" => __ETHNA_PLUGIN_DIR,
    );

    public function setURL($url)
    {
        $this->url = $url;
    }
}
// }}}

// {{{ Ethna_Plugin_Smarty_function_url_Test_UrlHandler
class Ethna_Plugin_Smarty_function_url_Test_UrlHandler
    extends Ethna_UrlHandler
{
    public static function getInstance($name = null)
    {
        $instance = parent::getInstance(__CLASS__);
        return $instance;
    }

    function _getPath_Entrypoint($action, $params)
    {
        return array('/entrypoint', array());
    }

    function _getPath_Emptypath($action, $params)
    {
        return array('', array());
    }

    public function setActionMap($am)
    {
        $this->action_map = $am;
    }
}
// }}}
