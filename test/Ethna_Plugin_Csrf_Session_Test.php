<?php
/**
 *  Ethna_Plugin_Validator_Required_Test.php
 */

/**
 *  Ethna_Plugin_Validator_Requiredクラスのテストケース
 *
 *  @access public
 */
class Ethna_Plugin_Csrf_Session_Test extends UnitTestCase
{
    function testCheckCsrfSession()
    {
		$ctl =& Ethna_Controller::getInstance();
		$plugin =& $ctl->getPlugin();
		$csrf =& $plugin->getPlugin('Csrf', 'Session');
        $csrf->session =& new Ethna_Session_Dummy($ctl->appid, '',  $ctl->getLogger());
        $this->assertTrue($csrf->set());
        $csrfid = $csrf->get();
        $_SERVER['REQUEST_METHOD'] = "post";
        $_POST[$csrf->getName()] = "";
		$this->assertFalse($csrf->Valid());

        $_POST[$csrf->getName()] = $csrfid;
		$this->assertTrue($csrf->Valid());

        $_SERVER['REQUEST_METHOD'] = "get";
        $_GET[$csrf->getName()] = "";
		$this->assertFalse($csrf->Valid());

        $_GET[$csrf->getName()] = $csrfid;
		$this->assertTrue($csrf->Valid());
	}
}


/**
 *  SessionClassの_Dummy
 *
 *  @access public
 */
// {{{ Ethna_Session
/**
 *  セッションクラスのダミー
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Session_Dummy extends Ethna_Session
{
    var $dummy_session = array();


    /**
     *  セッションを復帰する
     *
     *  @access public
     */
    function restore()
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
    function isValid()
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
    function start($lifetime = 0, $anonymous = false)
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
    function destroy()
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
    function get($name)
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
    function set($name, $value)
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
    function remove($name)
    {
        if (!$this->session_start) {
            return false;
        }

        unset($this->dummy_session[$name]);

        return true;
    }
}
// }}}

?>
