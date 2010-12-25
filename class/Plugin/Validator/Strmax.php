<?php
// vim: foldmethod=marker
/**
 *  Strmax.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Strmax
/**
 *  最大値チェックプラグイン (シングルバイト文字列用)
 *
 *  NOTE: 
 *    - mbstring 不要
 *    - エラーメッセージは、全角半角を区別しません。
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Strmax extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    var $accept_array = false;

    /**
     *  最大値のチェックを行う (シングルバイト文字列用)
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
        if (isset($params['strmax']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        if ($type == VAR_TYPE_STRING) {
            $max_param = $params['strmax'];
            if (strlen($var) > $max_param) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else {
                    $msg = _et('Please input less than %d characters to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_MAX_STRING,
                        array($params['strmax']));
            }
        }

        return $true;
    }
}
// }}}

