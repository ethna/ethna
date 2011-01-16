<?php
// vim: foldmethod=marker
/**
 *  403.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_403
/**
 *  403ページ(アクセスを禁止することを知らせるページ)
 *  を出力するビューの実装
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_403 extends Ethna_ViewClass
{
    /**#@+
     *  @access private
     */

    /**#@-*/

    /**
     *  403 ページを出力するための前処理を行う
     *
     *  @access public
     *  @param  array  $param  出力に必要なユーザー定義パラメータ
     */
    public function preforward($param = array())
    {
        $this->error(403);
    }

}
// }}}
