<?php
/**
 *  UrlHandler_Simple_Test.php
 */

require_once 'Ethna/class/UrlHandler/Simple.php';

/**
 *  Ethna_UrlHandlerクラスのテストケース
 *
 *  @author Keisuke SATO <riaf@me.com>
 */
class Ethna_UrlHandler_Simple_Test extends Ethna_UnitTestBase
{
    var $url_handler;

    function setUp()
    {
        $this->url_handler = new Ethna_UrlHandler_Simple_TestClass($this);
    }

    // {{{ $_simple_map
    var $_simple_map = array(
        'test_foo_bar' => '/foo/bar',
    );
    // }}}

    // {{{ $_complex_map
    var $_complex_map = array(
        'test_foo_bar' => array(
            'path' => array(
                '/foo/bar',
                '/foo/bar/{param1}',
                '/foo/bar/{param1}/{param2}'
            ),
        ),
    );
    // }}}

    // {{{ test_requestToAction_simple
    function test_requestToAction_simple()
    {
        // pathinfo から action 取得
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',      // not empty
            '__url_info__'      => '/foo/bar',  // null or not empty
            'param3'            => 'value3',
        );

        $this->url_handler->setActionMap($this->_simple_map);
        $injected = $this->url_handler->requestToAction($http_vars);

        // action を受け取る
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual(count($diff), 1);
        $this->assertEqual($diff['action_test_foo_bar'], true);

        // action を受け取る以外の変化がないことを確認
        $diff = array_diff($http_vars, $injected);
        $this->assertEqual(count($diff), 0);

        // action を受け取る
        $this->url_handler->setActionMap($this->_complex_map);
        $injected = $this->url_handler->requestToAction($http_vars);

        $diff = array_diff($injected, $http_vars);
        $this->assertEqual(count($diff), 1);
        $this->assertEqual($diff['action_test_foo_bar'], true);

    }
    // }}}

    // {{{ test_requestToAction_nopathinfo
    function test_requestToAction_nopathinfo()
    {
        // pathinfo なし
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',
            '__url_info__'      => null,
        );

        $this->url_handler->setActionMap($this->_complex_map);
        $injected = $this->url_handler->requestToAction($http_vars);

        // 変化なし
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual(count($diff), 0);
    }
    // }}}

    // {{{ test_requestToAction_withparams1
    function test_requestToAction_withparams1()
    {
        // pathinfo から action とパラメータを受け取る
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',
            '__url_info__'      => '/foo/bar/aaa',
        );

        // 一致する action_map がない: エラーとして array() を返す
        $this->url_handler->setActionMap($this->_simple_map);
        $injected = $this->url_handler->requestToAction($http_vars);
        $this->assertEqual(count($injected), 0);


        // action とパラメータ param1 を受け取る
        $this->url_handler->setActionMap($this->_complex_map);
        $injected = $this->url_handler->requestToAction($http_vars);

        $diff = array_diff($injected, $http_vars);
        $this->assertEqual(count($diff), 2);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertEqual($diff['param1'], 'aaa');
    }
    // }}}

    // {{{ test_requestToAction_withparams2
    function test_requestToAction_withparams2()
    {
        // pathinfo から action と複数のパラメータを受け取る
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',
            '__url_info__'      => '/foo/bar/aaa/bbb',
        );

        $this->url_handler->setActionMap($this->_complex_map);
        $injected = $this->url_handler->requestToAction($http_vars);

        $diff = array_diff($injected, $http_vars);
        $this->assertEqual(count($diff), 3);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertEqual($diff['param1'], 'aaa');
        $this->assertEqual($diff['param2'], 'bbb');
    }
    // }}}

    // {{{ test_requestToAction_withparams3
    function test_requestToAction_withparams3()
    {
        // 定義された以上のパラメータがある場合
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',
            '__url_info__'      => '/foo/bar/aaa/bbb/ccc',
        );

        $this->url_handler->setActionMap($this->_complex_map);
        $injected = $this->url_handler->requestToAction($http_vars);
        $this->assertEqual(count($injected), 0);
    }
    // }}}

    // {{{ test_requestToAction_misc
    function test_requestToAction_misc()
    {
        // 微妙な pathinfo のチェック
        $http_vars = array(
            '__url_handler__'   => 'entrypoint',
        );
        $this->url_handler->setActionMap($this->_complex_map);

        // 余分な slash が前後についている
        $http_vars['__url_info__'] = '///foo///bar///value1///';
        $injected = $this->url_handler->requestToAction($http_vars);
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertEqual($diff['param1'], 'value1');
        $this->assertFalse(isset($diff['param2']));

        // path が '/./' を含む
        $http_vars['__url_info__'] = '/foo/bar/./value1';
        $injected = $this->url_handler->requestToAction($http_vars);
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertEqual($diff['param1'], '.');
        $this->assertEqual($diff['param2'], 'value1');

        // path が '/../' を含む
        $http_vars['__url_info__'] = '/foo/bar/../baz';
        $injected = $this->url_handler->requestToAction($http_vars);
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertEqual($diff['param1'], '..');
        $this->assertEqual($diff['param2'], 'baz');

        // 長いリクエスト
        $http_vars['__url_info__'] = '/foo/bar/' . str_repeat('a', 10000);
        $injected = $this->url_handler->requestToAction($http_vars);
        $diff = array_diff($injected, $http_vars);
        $this->assertEqual($diff['action_test_foo_bar'], true);
        $this->assertTrue(isset($diff['param1']));
        $this->assertFalse(isset($diff['param2']));
    }
    // }}}

    // {{{ test_actionToRequest_simple
    function test_actionToRequest_simple()
    {
        $action = 'test_foo_bar';
        $param = array(
            'hoge' => 'fuga',
        );

        $this->url_handler->setActionMap($this->_simple_map);
        $ret = $this->url_handler->actionToRequest($action, $param);
        $this->assertFalse(is_null($ret));
        list($path, $path_key) = $ret;

        $this->assertEqual($path, '/foo/bar');
        $this->assertTrue($path_key == array());
    }
    // }}}

    // {{{ test_actionToRequest_unknownaction
    function test_actionToRequest_unknownaction()
    {
        $action = 'test_unknown_action';
        $param = array(
            'hoge' => 'fuga',
        );

        $this->url_handler->setActionMap($this->_simple_map);
        $ret = $this->url_handler->actionToRequest($action, $param);
        $this->assertTrue(is_null($ret));
    }
    // }}}

    // {{{ test_actionToRequest_param1
    function test_actionToRequest_param1()
    {
        $action = 'test_foo_bar';
        $param = array(
            'hoge' => 'fuga',
            'param1' => 'value1',
        );

        $this->url_handler->setActionMap($this->_complex_map);
        list($path, $path_key) = $this->url_handler->actionToRequest($action, $param);
        $this->assertEqual($path, '/foo/bar/value1');
        $this->assertTrue($path_key == array('param1'));
    }
    // }}}

    // {{{ test_actionToRequest_param2
    function test_actionToRequest_param2()
    {
        $action = 'test_foo_bar';
        $param = array(
            'hoge' => 'fuga',
            'param1' => 'value1',
            'param2' => 'value2',
        );

        $this->url_handler->setActionMap($this->_complex_map);
        list($path, $path_key) = $this->url_handler->actionToRequest($action, $param);
        $this->assertEqual($path, '/foo/bar/value1/value2');
        $this->assertEqual($path_key, array('param1', 'param2'));
    }
    // }}}
}

class Ethna_UrlHandler_Simple_TestClass extends Ethna_UrlHandler_Simple
{
    public function setActionMap($am)
    {
        $this->action_map = $am;
    }
}

