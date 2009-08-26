<?php
// vim: foldmethod=marker
/**
 *  Filter.php
 *
 *  @author     Kazuhiro Hosoi <hosoi@gree.co.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Filter
/**
 *  プラグインフィルタ基底クラス
 *
 *  Plugin実装により，Ethna_Filterの後継として，
 *  Ethna_Plugin_Filterに追加しました．基本的にEthna_Filterと同じです．
 *  
 *  Mojaviの真似です（きっぱり）。アクション実行前に各種処理を行うことが
 *  出来ます。
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Filter extends Ethna_Plugin_Abstract
{
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Controller    controllerオブジェクト */
    //var $controller;

    /** @var    object  Ethna_Controller    controllerオブジェクト($controllerの省略形) */
    //var $ctl;

    /** @var    object  Ethna_Config        設定オブジェクト */
    //var $config;

    /** @var    object  Ethna_Logger        ログオブジェクト */
    //var $logger;

    /**#@-*/


    /**
     *  Filterのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     */
    /*
    function Ethna_Plugin_Filter(&$controller)
    {
        // オブジェクトの設定
        $this->controller =& $controller;
        $this->ctl =& $this->controller;

        $this->config =& $controller->getConfig();
        $this->logger =& $this->controller->getLogger();
    }
    */

    /**
     *  実行前フィルタ
     *
     *  @access public
     *  @return Ethna_Error:実行中止 any:正常終了
     */
    function preFilter()
    {
    }

    /**
     *  アクション実行前フィルタ
     *
     *  @access public
     *  @param  string  $action_name    実行されるアクション名
     *  @return string  null:正常終了 (string):実行するアクション名を変更
     */
    function preActionFilter($action_name)
    {
        return null;
    }

    /**
     *  アクション実行後フィルタ
     *
     *  @access public
     *  @param  string  $action_name    実行されたアクション名
     *  @param  string  $forward_name   実行されたアクションからの戻り値
     *  @return string  null:正常終了 (string):遷移名を変更
     */
    function postActionFilter($action_name, $forward_name)
    {
        return null;
    }

    /**
     *  実行後フィルタ
     *
     *  @access public
     *  @return Ethna_Error:実行中止 any:正常終了
     */
    function postFilter()
    {
    }
}
// }}}
