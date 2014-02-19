<?php
// vim: foldmethod=marker
/**
 *  Csrf.php
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Csrf
/**
 *  CSRF対策基底クラス
 *
 *  CSRF対策をトークンを用いて対策するためのコード
 *
 *  @author     Keita Arai <cocoiti@comio.info>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Csrf extends Ethna_Plugin_Abstract
{
    /**#@+
     *  @access private
     */

    /** @var    string  共有トークン名 */
    public $token_name = 'ethna_csrf';

    /**#@-*/


    /**
     *  Csrfのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     */
    /**
     *  トークンをViewとローカルファイルにセットする
     *
     *  @access public
     *  @return string  トークンのKey
     */
    public function set()
    {

    }

    /**
     *  トークンIDを取得する
     *
     *  @access public
     *  @return string トークンIDを返す。
     */
    public function get()
    {

    }

    /**
     *  トークンIDを削除する
     *
     *  @access public
     *  @return string トークンIDを返す。
     */
    public function remove()
    {

    }

    /**
     *  トークン名を取得する
     *
     *  @access public
     *  @return string トークン名を返す。
     */
    public function getTokenName()
    {
        return $this->token_name;
    }

    /**
     *  トークンIDを検証する
     *
     *  @access public
     *  @return mixed  正常の場合はtrue, 不正の場合はfalse
     */
    public function isValid()
    {
        $token = $this->_get_token();

        $local_token = $this->get();

        if (is_null($local_token)) {
            return false;
        }

        if ($token === $local_token) {
            return true;
        }

        return false;
    }

    /**
     *  キーを生成する
     *
     *  @access public
     *  @return string  keyname
     */
    public function _generateKey()
    {
        return Ethna_Util::getRandom(32);
    }

    /**
     *  リクエストからトークンIDとリクエストIDを抜き出す
     *
     *  @access public
     *  @return mixed  正常の場合はトークン名, 不正の場合はfalse
     */
    public function _get_token()
    {
        $token_name = $this->getTokenName();
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0) {
            return isset($_POST[$token_name]) ? $_POST[$token_name] : null;
        } else {
            return isset($_GET[$token_name]) ? $_GET[$token_name] : null;
        }
    }
}
// }}}
