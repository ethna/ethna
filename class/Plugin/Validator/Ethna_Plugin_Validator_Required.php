<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Validator_Required.php
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
    var $accept_array = true;

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
    function &validate($name, $var, $params)
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
                    $msg = '{form}が選択されていません';
                } else {
                    $msg = '{form}が入力されていません';
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
                if ($this->isEmpty($var[$key], $form_def['type']) == false) {
                    $valid_keys[] = $key;
                }
            }
        }

        // required_key のチェック
        if (isset($params['key'])) {
            $invalid_keys = array_diff(to_array($params['key']), $valid_keys);
            if (count($invalid_keys) > 0) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = '{form}の必要な項目が選択されていません';
                } else {
                    $msg = '{form}の必要な項目が入力されていません';
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        // required_num のチェック
        if (isset($params['num'])) {
            if (count($valid_keys) < intval($params['num'])) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = '{form}が必要な数まで選択されていません';
                } else {
                    $msg = '{form}が必要な数まで入力されていません';
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        // とくに指定がないとき: フォームに与えられた全要素
        if (isset($params['key']) == false && isset($params['num']) == false) {
            if (count($valid_keys) == 0 || count($valid_keys) != count($var)) {
                if (isset($params['error'])) {
                    $msg = $params['error'];
                } else if ($choice) {
                    $msg = '{form}が選択されていません';
                } else {
                    $msg = '{form}が入力されていません';
                }
                return Ethna::raiseNotice($msg, E_FORM_REQUIRED);
            }
        }

        return $true;
    }

}
// }}}
?>
