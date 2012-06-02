<?php
// vim: foldmethod=marker
/**
 *  Redirect.php
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
    public function preforward($url = NULL)
    {
        if (is_null($url)) {
            $this->redirect($this->config->get('url'));
        }
        else {
            if ($this->isAbsoluteUrl($url)) {
                $this->redirect($url);
            }
            else {
                if (substr($this->config->get('url'), -1) === '/') {
                    $base = $this->config->get('url');
                }
                else {
                    $base = $this->config->get('url') . '/';
                }

                if (strpos($url, '/') === 0) {
                    $suff = substr($url, 1);
                } else {
                    $suff = $url;
                }

                $this->redirect($base . $suff);
            }
        }
    }

    public function isAbsoluteUrl($url)
    {
        if (preg_match("@^(https?|ftp)://.+@", $url)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *  遷移名に対応する画面を出力する
     *
     *  @access public
     */
    public function forward()
    {
         // do nothing.
    }
}
// }}}
