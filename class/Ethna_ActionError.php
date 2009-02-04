<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionError.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_ActionError
/**
 *  アプリケーションエラー管理クラス
 *
 *  @access     public
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @package    Ethna
 *  @todo   配列フォームを扱えるようにする
 */
class Ethna_ActionError
{
    /**#@+
     *  @access private
     */

    /** @var    array   エラーオブジェクトの一覧 */
    var $error_list = array();

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト */
    var $action_form = null;

    /** @var    object  Ethna_Logger        ログオブジェクト */
    var $logger = null;
    /**#@-*/

    /**
     *  Ethna_ActionErrorクラスのコンストラクタ
     *
     *  @access public
     */
    function Ethna_ActionError()
    {
    }

    /**
     *  エラーオブジェクトを生成/追加する
     *
     *  @access public
     *  @param  string  $name       エラーの発生したフォーム項目名(不要ならnull)
     *  @param  string  $message    エラーメッセージ
     *  @param  int     $code       エラーコード
     *  @return Ethna_Error エラーオブジェクト
     */
    function &add($name, $message, $code = null)
    {
        if (func_num_args() > 3) {
            $userinfo = array_slice(func_get_args(), 3);
            $error =& Ethna::raiseNotice($message, $code, $userinfo);
        } else {
            $error =& Ethna::raiseNotice($message, $code);
        }
        $this->addObject($name, $error);
        return $error;
    }

    /**
     *  Ethna_Errorオブジェクトを追加する
     *
     *  @access public
     *  @param  string              $name   エラーに対応するフォーム項目名(不要ならnull)
     *  @param  object  Ethna_Error $error  エラーオブジェクト
     */
    function addObject($name, &$error)
    {
        $elt = array();
        $elt['name'] = $name;
        $elt['object'] =& $error;
        $this->error_list[] = $elt;

        // ログ出力(補足)
        $af =& $this->_getActionForm();
        $logger =& $this->_getLogger();
        $logger->log(LOG_NOTICE, '{form} -> [%s]', $this->action_form->getName($name));
    }

    /**
     *  エラーオブジェクトの数を返す
     *
     *  @access public
     *  @return int     エラーオブジェクトの数
     */
    function count()
    {
        return count($this->error_list);
    }

    /**
     *  エラーオブジェクトの数を返す(count()メソッドのエイリアス)
     *
     *  @access public
     *  @return int     エラーオブジェクトの数
     */
    function length()
    {
        return count($this->error_list);
    }

    /**
     *  登録されたエラーオブジェクトを全て削除する
     *
     *  @access public
     */
    function clear()
    {
        $this->error_list = array();
    }

    /**
     *  指定されたフォーム項目にエラーが発生しているかどうかを返す
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return bool    true:エラーが発生している false:エラーが発生していない
     */
    function isError($name)
    {
        foreach ($this->error_list as $error) {
            if (strcasecmp($error['name'], $name) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *  指定されたフォーム項目に対応するエラーメッセージを返す
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return string  エラーメッセージ(エラーが無い場合はnull)
     */
    function getMessage($name)
    {
        foreach ($this->error_list as $error) {
            if (strcasecmp($error['name'], $name) == 0) {
                return $this->_getMessage($error);
            }
        }
        return null;
    }

    /**
     *  エラーオブジェクトを配列にして返す
     *
     *  @access public
     *  @return array   エラーオブジェクトの配列
     */
    function getErrorList()
    {
        return $this->error_list;
    }

    /**
     *  エラーメッセージを配列にして返す
     *
     *  @access public
     *  @return array   エラーメッセージの配列
     */
    function getMessageList()
    {
        $message_list = array();

        foreach ($this->error_list as $error) {
            $message_list[] = $this->_getMessage($error);
        }
        return $message_list;
    }

    /**
     *  アプリケーションエラーメッセージを取得する
     *
     *  @access private
     *  @param  array   エラーエントリ
     *  @return string  エラーメッセージ
     */
    function _getMessage(&$error)
    {
        $af =& $this->_getActionForm();
        $form_name = $af->getName($error['name']);
        return str_replace("{form}", $form_name, $error['object']->getMessage());
    }

    /**
     *  Ethna_ActionFormオブジェクトを取得する
     *
     *  @access private
     *  @return object  Ethna_ActionForm
     */
    function &_getActionForm()
    {
        if (isset($this->action_form) == false) {
            $controller =& Ethna_Controller::getInstance();
            $this->action_form =& $controller->getActionForm();
        }
        return $this->action_form;
    }

    /**
     *  Ethna_Loggerオブジェクトを取得する
     *
     *  @access private
     *  @return object  Ethna_Logger
     */
    function &_getLogger()
    {
        if (is_null($this->logger)) {
            $controller =& Ethna_Controller::getInstance();
            $this->logger =& $controller->getLogger();
        }
        return $this->logger;
    }
}
// }}}
?>
