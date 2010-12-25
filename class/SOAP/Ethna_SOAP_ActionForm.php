<?php
// vim: foldmethod=marker
/**
 *  Ethna_SOAP_ActionForm.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_SOAP_ActionForm
/**
 *  SOAPフォームクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_SOAP_ActionForm extends Ethna_ActionForm
{
    /**#@+
     *  @access private
     */

    /** @var    array   引数定義 */
    var $arg = array();

    /** @var    array   戻り値定義 */
    var $retval = array();

    /**#@-*/

    /**
     *  Ethna_SOAP_ActionFormクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_ActionError   $action_error   アクションエラーオブジェクト
     */
    public function __construct($action_error)
    {
        $this->form = $this->arg;

        parent::__construct($action_error);
    }
}
// }}}
