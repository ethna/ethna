<?php
// vim: foldmethod=marker
/**
 *  Max.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Validator_Max
/**
 *  最大値チェックプラグイン
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Validator_Max extends Ethna_Plugin_Validator
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
     */
    public function validate($name, $var, $params)
    {
        $true = true;
        $type = $this->getFormType($name);
        if (isset($params['max']) == false || $this->isEmpty($var, $type)) {
            return $true;
        }

        switch ($type) {
            case VAR_TYPE_INT:
                if ($var > $params['max']) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input less than %d(int) to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MAX_INT, array($params['max']));
                }
                break;

            case VAR_TYPE_FLOAT:
                if ($var > $params['max']) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input less than %f(float) to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MAX_FLOAT, array($params['max']));
                }
                break;

            case VAR_TYPE_DATETIME:
                $t_max = strtotime($params['max']);
                $t_var = strtotime($var);
                if ($t_var > $t_max) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please input datetime value before %s to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MAX_DATETIME, array($params['max']));
                }
                break;

            case VAR_TYPE_FILE:
                $st = stat($var['tmp_name']);
                if ($st[7] > $params['max'] * 1024) {
                    if (isset($params['error'])) {
                        $msg = $params['error'];
                    } else {
                        $msg = _et('Please specify file whose size is less than %d KB to {form}.');
                    }
                    return Ethna::raiseNotice($msg, E_FORM_MAX_FILE, array($params['max']));
                }
                break;

            case VAR_TYPE_STRING:

                //
                //  マルチバイトエンコーディングと、そうでない場合で
                //  異なるプラグインを呼ぶ。
                //
                //  これは Ethna_Controller#client_encoding の値によ
                //  って動きが決まる
                //

                $ctl = Ethna_Controller::getInstance();
                $client_enc = $ctl->getClientEncoding();
                $plugin = $this->backend->getPlugin();

                //  select Plugin.
                if (mb_enabled() && strcasecmp('UTF-8', $client_enc) == 0) {
                    $plugin_name = 'Mbstrmax';
                    $params['mbstrmax'] = $params['max'];
                } elseif (strcasecmp('EUC-JP', $client_enc == 0)
                       || strcasecmp('eucJP-win', $client_enc == 0)) {
                    //  2.3.x compatibility
                    $plugin_name = 'Strmaxcompat';
                    $params['strmaxcompat'] = $params['max'];
                } else {
                    $plugin_name = 'Strmax';
                    $params['strmax'] = $params['max'];
                }
                unset($params['max']);

                $vld = $plugin->getPlugin('Validator', $plugin_name);
                return $vld->validate($name, $var, $params);

                break;
        }

        return $true;
    }
}
// }}}

