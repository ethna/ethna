<?php
// vim: foldmethod=marker
/**
 *  Validator.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// UPLOAD_ERR_* が未定義の場合 (PHP 4.3.0 以前)
if (defined('UPLOAD_ERR_OK') == false) {
    define('UPLOAD_ERR_OK', 0);
}

// {{{ Ethna_Plugin_Validator
/**
 *  バリデータプラグインの基底クラス
 *  
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator extends Ethna_Plugin_Abstract
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Backend   backendオブジェクト */
    //var $backend;

    /** @var    object  Ethna_Logger    ログオブジェクト */
    //var $logger;

    /** @var    object  Ethna_ActionForm    フォームオブジェクト */
    //var $action_form;

    /** @var    object  Ethna_ActionForm    フォームオブジェクト */
    //var $af;

    /** @var    bool    配列を受け取るバリデータかどうかのフラグ */
    var $accept_array = false;

    /**#@-*/

    /**
     *  コンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller コントローラオブジェクト
     */
    /*
    function Ethna_Plugin_Validator(&$controller)
    {
        $this->backend =& $controller->getBackend();
        $this->logger =& $controller->getLogger();
        $this->action_form =& $controller->getActionForm();
        $this->af =& $this->action_form;
    }
    */

    /**
     *  フォーム値検証のためにActionFormから呼び出されるメソッド
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    function &validate($name, $var, $params)
    {
        die('override!');
    }

    /**
     *  フォーム定義を取得する
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     */
    function getFormDef($name)
    {
        return $this->af->getDef($name);
    }

    /**
     *  フォームのtypeを取得する(配列の場合は値のみ)
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     */
    function getFormType($name)
    {
        $def = $this->af->getDef($name);
        if (isset($def['type'])) {
            if (is_array($def['type'])) {
                return $def['type'][0];
            } else {
                return $def['type'];
            }
        } else {
            return null;
        }
    }

    /**
     *  フォーム値が空かどうかを判定 (配列フォームの場合は各要素に対して呼び出す)
     *
     *  @access protected
     *  @param  mixed   $var       フォームの値 (配列フォームの場合は各要素)
     *  @param  int     $type      フォームのtype
     */
    function isEmpty($var, $type)
    {
        if ($type == VAR_TYPE_FILE) {
            if (isset($var['error']) == false || $var['error'] != UPLOAD_ERR_OK) {
                return true;
            }
            if (isset($var['tmp_name']) == false || is_uploaded_file($var['tmp_name']) == false) {
                return true;
            }
            if (isset($var['size']) == false || $var['size'] == 0) {
                return true;
            }
        } else {
            if (is_scalar($var) == false || strlen($var) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *  true を参照で返す
     *
     *  @access protected
     */
    function &ok()
    {
        $true = true;
        return $true;
    }

    /**
     *  エラーを返す
     *
     *  @access protected
     *  @param  string  $msg        エラーメッセージ
     *  @param  int     $code       エラーコード
     *  @param  mixed   $info       エラーメッセージにsprintfで渡すパラメータ
     */
    function &error($msg, $code, $info = null)
    {
        if ($info != null) {
            if (is_array($info)) {
                return Ethna::raiseNotice($msg, $code, $info);
            } else {
                return Ethna::raiseNotice($msg, $code, array($info));
            }
        } else {
            return Ethna::raiseNotice($msg, $code);
        }
    }
}
// }}}
?>
