<?php
// vim: foldmethod=marker
/**
 *  404.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_404
/**
 *  404ページ(リソースが存在しない場合のエラーページ)
 *  を出力するビューの実装
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_404 extends Ethna_ViewClass
{
    /**#@+
     *  @access private
     */

    /**#@-*/

    /**
     *  404 ページを出力するための前処理を行う
     *
     *  @access public
     *  @param  array  $param  出力に必要なユーザー定義パラメータ
     */
    public function preforward($param = array())
    {
        $this->error(404);
    }

}
// }}}
