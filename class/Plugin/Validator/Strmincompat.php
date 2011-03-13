<?php
// vim: foldmethod=marker
/**
 *  Strmincompat.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Strmincompat
/**
 *  最小値チェックプラグイン
 *  (マルチバイト文字列(EUC_JP)用. Ethna 2.3.x までの互換性保持用)
 *
 *  NOTE: 
 *    - EUC_JP 専用のプラグインです。
 *    - クライアントエンコーディングがEUC_JP以外の場合は、入力を無条件でEUC_JPに変換します 
 *      (但し mbstringが入っていない場合は除く) 
 *    - エラーメッセージは、全角半角を区別したものが出力されます。
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Strmincompat extends Ethna_Plugin_Validator
{
    /** @public    bool    配列を受け取るかフラグ */
    public $accept_array = false;

    /**
     *  最小値のチェックを行う
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['strmincompat']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        $ctl = $this->backend->getController();
        $client_enc = $ctl->getClientEncoding();
        if (mb_enabled()
        && (strcasecmp('EUC-JP', $client_enc) != 0
         && strcasecmp('eucJP-win', $client_enc) != 0)) {
            $var = mb_convert_encoding($var, 'EUC-JP', $client_enc);
        }

        if ($type == VAR_TYPE_STRING) {
            $min_param = $params['strmincompat'];
            if (strlen($var) < $min_param) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else {
                    $msg = _et('Please input more than %d full-size (%d half-size) characters to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_MIN_STRING,
                            array(intval($min_param/2), $min_param));
            }
        }

        return $true;
    }
}
// }}}

