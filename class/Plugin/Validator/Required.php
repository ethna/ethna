<?php
// vim: foldmethod=marker
/**
 *  Required.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Required
/**
 *  必須フォームの検証プラグイン
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Required extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    public $accept_array = true;

    /**
     *  フォームに値が入力されているかを検証する
     *
     *  配列の場合は、入力されるべき key のリスト、
     *  あるいは key の数を指定できます
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        if (isset($params['required']) && $params['required'] == false) {
            return $true;
        }
        $form_def = $this->getFormDef($name);

        // 選択型のフォームかどうか
        switch ($form_def['form_type']) {
        case FORM_TYPE_SELECT:
        case FORM_TYPE_RADIO:
        case FORM_TYPE_CHECKBOX:
        case FORM_TYPE_FILE:
            $choice = true;
            break;
        default:
            $choice = false;
        }

        // スカラーの場合
        if (is_array($form_def['type']) == false) {
            if ($this->isEmpty($var, $this->getFormType($name))) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = _et('{form} was not selected.');
                } else {
                    $msg = _et('no input to {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            } else {
                return $true;
            }
        }
                
        // 配列の場合
        $valid_keys = array();
        if ($var != null) {
            foreach (array_keys($var) as $key) {
                if ($this->isEmpty($var[$key], $this->getFormType($name)) == false) {
                    $valid_keys[] = $key;
                }
            }
        }

        // 配列の required_key のチェック
        // 'required_key' => array(xx) に設定された配列の要素値がなければエラー。
        if (isset($params['key'])) {
            $invalid_keys = array_diff(to_array($params['key']), $valid_keys);
            if (count($invalid_keys) > 0) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = _et('Required item of {form} was not selected.');
                } else {
                    $msg = _et('Required item of {form} was not submitted.');
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        // 配列の required_num のチェック
        // 'required_num' => xx に設定された数より、validな値の数が少なければエラー。
        if (isset($params['num'])) {
            if (count($valid_keys) < intval($params['num'])) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = _et('Required numbers of {form} was not selected.');
                } else {
                    $msg = _et('Required numbers of {form} was not submitted.');
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        // とくに指定がないとき: フォームに与えられた全要素に
        // valid な値が入っていなければならない
        if (isset($params['key']) == false && isset($params['num']) == false) {
            if (count($valid_keys) == 0 || count($valid_keys) != count($var)) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = _et('Please select {form}.');
                } else {
                    $msg = _et('Please input {form}.');
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        return $true;
    }

}
// }}}
