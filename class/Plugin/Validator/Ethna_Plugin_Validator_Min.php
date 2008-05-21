<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Validator_Min.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Min
/**
 *  最小値チェックプラグイン
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Min extends Ethna_Plugin_Validator
{
    /** @var    bool    配列を受け取るかフラグ */
    var $accept_array = false;

    /**
     *  最小値のチェックを行う
     *
     *  @access public
     *  @param  string  $name       フォームの名前
     *  @param  mixed   $var        フォームの値
     *  @param  array   $params     プラグインのパラメータ
     */
    function &validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['min']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        switch ($type) {
            case VAR_TYPE_INT:
                if ($var < $params['min']) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input more than %d(int) to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MIN_INT, array($params['min']));
                }
                break;

            case VAR_TYPE_FLOAT:
                if ($var < $params['min']) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input more than %f(float) to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MIN_FLOAT, array($params['min']));
                }
                break;

            case VAR_TYPE_DATETIME:
                $t_min = strtotime($params['min']);
                $t_var = strtotime($var);
                if ($t_var < $t_min) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input datetime value %s or later to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MIN_DATETIME, array($params['min']));
                }
                break;

            case VAR_TYPE_FILE:
                $st = stat($var['tmp_name']);
                if ($st[7] < $params['min'] * 1024) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please specify file whose size is more than %d KB.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MIN_FILE, array($params['min']));
                }
                break;

            case VAR_TYPE_STRING:
                if (strlen($var) < $params['min']) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input more than %d full-size (%d half-size) characters to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MIN_STRING,
                            array(intval($params['min']/2), $params['min']));
                }
                break;
        }

        return $true;
    }
}
// }}}
?>
