<?php
// vim: foldmethod=marker
/**
 *  Mbstrmax.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Mbstrmax
/**
 *  最大値チェックプラグイン (マルチバイト文字列用)
 *
 *  NOTE:
 *      - mbstring を有効にしておく必要があります。
 *      - エラーメッセージは、全角半角を区別しません。
 * 
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Mbstrmax extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    public $accept_array = false;

    /**
     *  最大値のチェックを行う
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     *  @return true: 成功  Ethna_Error: エラー
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['mbstrmax']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        if ($type == VAR_TYPE_STRING) {
            $max_param = $params['mbstrmax'];
            if (mb_strlen($var) > $max_param) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else {
                    $msg = _et('Please input less than %d characters to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_MAX_STRING,
                        array($max_param));
            }
        }

        return $true;
    }
}
// }}}

