<?php
// vim: foldmethod=marker
/**
 *  Ethna_View_Info.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_Info
/**
 *  __ethna_info__ビューの実装
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_Info extends Ethna_ViewClass
{
    /**#@+
     *  @access public
     */

    /** @var boolean  レイアウトテンプレートの使用フラグ       */
    var $use_layout = false;

    /**#@-*/

    /**
     *  遷移前処理
     *
     *  @access public
     */
    function preforward()
    {
        $ctl = Ethna_Controller::getInstance();
        $em = new Ethna_InfoManager($this->backend);

        // cores
        $this->af->setApp('app_id', $ctl->getAppId());
        $this->af->setApp('ethna_version', ETHNA_VERSION);

        // actions
        $this->af->setApp('action_list', $em->getActionList());

        // views 
        $this->af->setApp('forward_list', $em->getForwardList());

        // configuration
        $this->af->setApp('configuration', $em->getConfiguration());

        // plugins
        $this->af->setApp('plugin_list', $em->getPluginList());
    }
}
// }}}
