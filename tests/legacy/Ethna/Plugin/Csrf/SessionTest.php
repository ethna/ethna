<?php
/*
 * Copyright (C) the Ethna contributors. All rights reserved.
 *
 * This file is part of the Ethna package, distributed under new BSD.
 * For full terms see the included LICENSE file.
 */

class Ethna_Plugin_Csrf_Session_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Ethna_Plugin_Csrf_Session $csrf
     */
    public $csrf;

    protected $csrfid;

    public function setUp()
    {
        $ctl = new CsrfTest_Ethna_Controller();
        $plugin = $ctl->getPlugin();
        $this->csrf = $plugin->getPlugin('Csrf', 'Session');
        $this->assertTrue($this->csrf->set());
        $this->csrfid = $this->csrf->get();
    }

    public function tearDown()
    {
        $_SERVER['REQUEST_METHOD'] = null;
        unset($GLOBALS['_Ethna_controller']);
    }

    public function testMakeInstance()
    {
        $this->assertTrue(is_object($this->csrf), 'getPlugin failed');
        //$this->csrf->session = new Ethna_Session_Dummy($ctl, $ctl->getAppId());
    }

    public function testGetName()
    {
        $this->assertTrue((bool)strlen($this->csrf->getTokenName()), 'token name not found');
    }

    public function testPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = "POST";
        $_POST[$this->csrf->getTokenName()] = "";
        $this->assertFalse($this->csrf->isValid());

        $_POST[$this->csrf->getTokenName()] = $this->csrfid;
        $this->assertTrue($this->csrf->isValid());
    }

    public function testGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = "GET";
        $_GET[$this->csrf->getTokenName()] = "";
        $this->assertFalse($this->csrf->isValid());

        $_GET[$this->csrf->getTokenName()] = $this->csrfid;
        $this->assertTrue($this->csrf->isValid());
    }

}

/**
 *  SessionClassの_Dummy
 *
 *  @access public
 */

/**
 *  セッションクラスのダミー
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Session_Dummy extends Ethna_Session
{
    public $dummy_session = array();


    /**
     *  セッションを復帰する
     *
     *  @access public
     */
    public function restore()
    {
        $this->session_start = true;
        return true;
    }

    /**
     *  セッションの正当性チェック
     *
     *  @access public
     *  @return bool    true:正当なセッション false:不当なセッション
     */
    public function isValid()
    {
        return true;
    }

    /**
     *  セッションを開始する
     *
     *  @access public
     *  @param  int     $lifetime   セッション有効期間(秒単位, 0ならセッションクッキー)
     *  @return bool    true:正常終了 false:エラー
     */
    public function start($lifetime = 0, $anonymous = false)
    {
        $_SESSION['REMOTE_ADDR'] = "DUMMY";
        $_SESSION['__anonymous__'] = $anonymous;
        $this->session_start = true;
        return true;
    }

    /**
     *  セッションを破棄する
     *
     *  @access public
     *  @return bool    true:正常終了 false:エラー
     */
    public function destroy()
    {
        return true;
    }

    /**
     *  セッション値へのアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return mixed   取得した値(null:セッションが開始されていない)
     */
    public function get($name)
    {
        if (!isset($this->dummy_session[$name])) {
            return null;
        }
        return $this->dummy_session[$name];
    }

    /**
     *  セッション値へのアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @param  string  $value  値
     *  @return bool    true:正常終了 false:エラー(セッションが開始されていない)
     */
    public function set($name, $value)
    {
        if (!$this->session_start) {
            // no way
            return false;
        }

        $this->dummy_session[$name] = $value;

        return true;
    }

    /**
     *  セッションの値を破棄する
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return bool    true:正常終了 false:エラー(セッションが開始されていない)
     */
    public function remove($name)
    {
        if (!$this->session_start) {
            return false;
        }

        unset($this->dummy_session[$name]);

        return true;
    }
}

/**
 *
 */
class CsrfTest_Ethna_Controller
    extends Ethna_Controller
{
    public $class = array(
        'session'       => 'Ethna_Session_Dummy',
    );

    public $directory = array(
        // Memo(chobie): 設計上先に設定ないと死ぬ
        "plugin" => __ETHNA_PLUGIN_DIR,
    );
}
