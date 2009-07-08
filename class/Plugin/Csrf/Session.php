<?php
// vim: foldmethod=marker
/**
 *  Session.php
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Csrf_Session
/**
 *  CSRF対策
 *
 *  CSRF対策をトークンを用いて対策するためのコード
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Csrf_Session extends Ethna_Plugin_Csrf
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Session    セッションオブジェクト */
    var $session;
    
    /**#@-*/


    /**
     *  Ethna_Plugin_Csrfのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     */
    function Ethna_Plugin_Csrf_Session(&$controller)
    {
        parent::Ethna_Plugin_Csrf($controller);

        // オブジェクトの設定
        $this->session =& $this->controller->getSession();
    }
    
    /**
     *  トークンをViewとローカルファイルにセットする
     *
     *  @access public
     *  @return boolean  成功か失敗か
     */
    function set()
    {
        if (! $this->session->isStart()) {
            $this->session->start();
        }

        $token = $this->session->get($this->token_name);
        if ($token !== null) {
            return true;
        }

        $key = $this->_generateKey();
        $this->session->set($this->token_name, $key); 

        return true;
    }

    /**
     *  トークンIDを取得する
     *
     *  @access public
     *  @return string トークンIDを返す。
     */
    function get()
    {
        if (! $this->session->isStart()) {
            $this->session->start();
        }

        return $this->session->get($this->token_name);
    }

    /**
     *  トークンIDを削除する
     *
     *  @access public
     */
    function remove()
    {
        if (! $this->session->isStart()) {
            $this->session->start();
        }
        $this->session->remove($this->token_name);
    }
}
// }}}
?>
