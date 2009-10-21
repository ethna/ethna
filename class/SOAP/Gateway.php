<?php
// vim: foldmethod=marker
/**
 *  Gateway.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_SOAP_Gateway
/**
 *  SOAPゲートウェイの基底クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_SOAP_Gateway
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    var $controller;

    /**#@-*/

    /**
     *  Ethna_SOAP_Gatewayクラスのコンストラクタ
     *
     *  @access public
     */
    function Ethna_SOAP_Gateway()
    {
        $this->controller =& Ethna_Controller::getInstance();
    }

    /**
     *  SOAPアクションを実行する
     *
     *  @access public
     */
    function dispatch()
    {
        $this->controller->trigger();
    }

    /**
     *  アプリケーション設定値一覧を取得する
     *
     *  @access public
     *  @return array   アプリケーション設定値一覧
     */
    function &getApp()
    {
        $action_form =& $this->controller->getActionForm();
        return $action_form->app_vars;
    }

    /**
     *  エラーコードを取得する
     *
     *  @access public
     *  @return int     エラーコード(nullならエラー無し)
     */
    function getErrorCode()
    {
        $action_error =& $this->controller->getActionError();
        if ($action_error->count() == 0) {
            return null;
        }
        
        // 最初の1つを返す
        $error_list = $action_error->getErrorList();
        $error =& $error_list[0];

        return $error->getCode();
    }

    /**
     *  エラーメッセージを取得する
     *
     *  @access public
     *  @return string  エラーメッセージ(nullならエラー無し)
     */
    function getErrorMessage()
    {
        $action_error =& $this->controller->getActionError();
        if ($action_error->count() == 0) {
            return null;
        }

        // 最初の1つを返す
        $message_list = $action_error->getMessageList();
        $message = $message_list[0];

        return $message;
    }
}
// }}}
