<?php
// vim: foldmethod=marker
/**
 *  Type.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Type
/**
 *  タイプチェックプラグイン
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Type extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    public $accept_array = false;

    /**
     *  フォーム値の型チェックを行う
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        $type = $params['type'];
        if ($type == VAR_TYPE_FILE || $this->isEmpty($var, $type)) {
            return $true;
        }

        foreach (array_keys(to_array($var)) as $key) {
            switch ($type) {
                case VAR_TYPE_INT:
                    if (!preg_match('/^-?\d+$/', $var)) {
                        if (isset($params['error'])) {
                            $msg = $params['error'];
                        } else {
                            $msg = _et('Please input integer value to {form}.');
                        }
                        return Ethna::raiseNotice($msg, E_FORM_WRONGTYPE_INT);
                    }
                    break;

                case VAR_TYPE_FLOAT:
                    if (!preg_match('/^-?\d+$/', $var) && !preg_match('/^-?\d+\.\d+$/', $var)) {
                        if (isset($params['error'])) {
                            $msg = $params['error'];
                        } else {
                            $msg = _et('Please input float value to {form}.');
                        }
                        return Ethna::raiseNotice($msg, E_FORM_WRONGTYPE_FLOAT);
                    }
                    break;

                case VAR_TYPE_BOOLEAN:
                    if ($var != "1" && $var != "0") {
                        if (isset($params['error'])) {
                            $msg = $params['error'];
                        } else {
                            $msg = _et('You can input 0 or 1 to {form}.');
                        }
                        return Ethna::raiseNotice($msg, E_FORM_WRONGTYPE_BOOLEAN);
                    }
                    break;

                case VAR_TYPE_DATETIME:
                    $r = strtotime($var);
                    if ($r == -1 || $r === false) {
                        if (isset($params['error'])) {
                            $msg = $params['error'];
                        } else {
                            $msg = _et('Please input valid datetime to {form}.');
                        }
                        return Ethna::raiseNotice($msg, E_FORM_WRONGTYPE_DATETIME);
                    }
                    break;
            }
        }

        return $true;
    }
}
// }}}

