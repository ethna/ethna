<?php
// vim: foldmethod=marker
/**
 *  Ethna_View_Redirect.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_Redirect
/**
 *  別のURLへリダイレクトするためのビューの実装
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_Redirect extends Ethna_ViewClass
{
    /**#@+
     *  @access private
     */

    /**#@-*/

    /**
     *  別のURLへリダイレクトするための前処理を行う
     *
     *  @access public
     *  @param  string  $url  リダイレクト先のURL
     */
    function preforward($url = NULL)
    {
        if (is_null($url)) {
            Ethna::raiseWarning(
                "URL is not set! use array('redirect', $url); on ActionClass."
            );
        }
        $this->redirect($url);
    }

    /**
     *  遷移名に対応する画面を出力する
     *
     *  @access public
     */
    function forward()
    {
         // do nothing.
    }
}
// }}}
?>
