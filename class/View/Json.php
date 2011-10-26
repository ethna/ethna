<?php
// vim: foldmethod=marker
/**
 *  Json.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_Json
/**
 *  JSON を出力するビューの実装
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_Json extends Ethna_ViewClass
{
    /**#@+
     *  @access private
     */
    public $has_default_header = false;

    /**#@-*/

    /**
     *  Jsonを出力する
     *
     *  @access public
     *  @param  array  $encode_param  出力するJSONにエンコードする値
     */
    public function preforward($encode_param = array(), $header = false)
    {
        $client_enc = $this->ctl->getClientEncoding();
        if (mb_enabled() && strcasecmp('UTF-8', $client_enc) != 0) {
            mb_convert_variables('UTF-8', $client_enc, $encode_param);
        }
        $encoded_param = json_encode($encode_param);

        if (!$header) {
            $this->header(array('Content-Type' => 'application/json; charset=UTF-8'));
        } else {
            $this->header($header);
        }
        echo $encoded_param;
    }

    public function forward()
    {
        // do nothing.
    }
}
// }}}
