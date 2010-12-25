<?php
// vim: foldmethod=marker
/**
 *  Strmaxcompat.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Strmaxcompat
/**
 *  最大値チェックプラグイン
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
class Ethna_Plugin_Validator_Strmaxcompat extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    var $accept_array = false;

    /**
     *  最大値のチェックを行う
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     *  @return true: 成功  Ethna_Error: エラー
     */
    function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['strmaxcompat']) == false || $this->isEmpty($var, $type)) {
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
            $max_param = $params['strmaxcompat'];
            if (strlen($var) > $max_param) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else {
                    $msg = _et('Please input less than %d full-size (%d half-size) characters to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_MAX_STRING,
                            array(intval($max_param/2), $max_param));
            }
        }

        return $true;
    }
}
// }}}

