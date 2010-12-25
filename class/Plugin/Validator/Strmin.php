<?php
// vim: foldmethod=marker
/**
 *  Strmin.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Strmin
/**
 *  最小値チェックプラグイン (シングルバイト文字列用)
 *
 *  NOTE: 
 *    - mbstring 不要
 *    - エラーメッセージは、全角半角を区別しません。
 * 
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Strmin extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    var $accept_array = false;

    /**
     *  最小値のチェックを行う (シングルバイト文字列用)
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['strmin']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        if ($type == VAR_TYPE_STRING) {
            $min_param = $params['strmin'];
            if (strlen($var) < $min_param) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else {
                    $msg = _et('Please input more than %d characters to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_MIN_STRING,
                        array($min_param));
            }
        }

        return $true;
    }
}
// }}}

