<?php
// vim: foldmethod=marker
/**
 *  Ethna_Action_Info.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Form_Info
/**
 *  __ethna_info__フォームの実装
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Form_Info extends Ethna_ActionForm
{
    /**
     *  @access private
     *  @var    array   フォーム値定義
     */
    var $form = array(
    );
}
// }}}

// {{{ Ethna_Action_Info
/**
 *  __ethna_info__アクションの実装
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Action_Info extends Ethna_ActionClass
{
    /**
     *  __ethna_info__アクションの前処理
     *
     *  @access public
     *  @return string      Forward先(正常終了ならnull)
     */
    function prepare()
    {
        return null;
    }

    /**
     *  __ethna_info__アクションの実装
     *
     *  @access public
     *  @return string  遷移名
     */
    function perform()
    {
        return '__ethna_info__';
    }
}
// }}}
?>
