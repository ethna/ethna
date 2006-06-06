<?php
// vim: foldmethod=marker
/**
 *  Ethna_ActionForm.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** 定型フィルタ: 半角入力 */
define('FILTER_HW', 'numeric_zentohan,alphabet_zentohan,ltrim,rtrim,ntrim');

/** 定型フィルタ: 全角入力 */
define('FILTER_FW', 'kana_hantozen,ntrim');


// {{{ Ethna_ActionForm
/**
 *  アクションフォームクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ActionForm
{
    /**#@+
     *  @access private
     */

    /** @var    array   フォーム値定義(デフォルト) */
    var $form_template = array();

    /** @var    array   フォーム値定義 */
    var $form = array();

    /** @var    array   フォーム値 */
    var $form_vars = array();

    /** @var    array   アプリケーション設定値 */
    var $app_vars = array();

    /** @var    array   アプリケーション設定値(自動エスケープなし) */
    var $app_ne_vars = array();

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト */
    var $action_error;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト(省略形) */
    var $ae;

    /** @var    object  Ethna_I18N  i18nオブジェクト */
    var $i18n;

    /** @var    object  Ethna_Logger    ログオブジェクト */
    var $logger;

    /** @var    array   フォーム定義要素 */
    var $def = array('name', 'required', 'max', 'min', 'regexp', 'custom', 'filter', 'form_type', 'type');

    /** @var    bool    追加検証強制フラグ */
    var $force_validate_plus = false;

    /**#@-*/

    /**
     *  Ethna_ActionFormクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    controllerオブジェクト
     */
    function Ethna_ActionForm(&$controller)
    {
        $this->action_error =& $controller->getActionError();
        $this->ae =& $this->action_error;
        $this->i18n =& $controller->getI18N();
        $this->logger =& $controller->getLogger();

        if (isset($_SERVER['REQUEST_METHOD']) == false) {
            return;
        }

        // フォーム値テンプレートの更新
        $this->form_template = $this->_setFormTemplate($this->form_template);

        // フォーム値定義の設定
        $this->_setFormDef();

        // 省略値補正
        foreach ($this->form as $name => $value) {
            foreach ($this->def as $k) {
                if (isset($value[$k]) == false) {
                    $this->form[$name][$k] = null;
                }
            }
        }
    }

    /**
     *  フォーム値のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @return mixed   フォーム値
     */
    function get($name)
    {
        if (isset($this->form_vars[$name])) {
            return $this->form_vars[$name];
        }
        return null;
    }

    /**
     *  フォーム値定義を取得する
     *
     *  @access public
     *  @param  string  $name   取得するフォーム名(省略可:nullなら全ての定義を取得する)
     *  @return array   フォーム値定義
     */
    function getDef($name = null)
    {
        if (is_null($name)) {
            return $this->form;
        }

        if (array_key_exists($name, $this->form) == false) {
            return null;
        } else {
            return $this->form[$name];
        }
    }

    /**
     *  フォーム項目表示名を取得する
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @return mixed   フォーム値の表示名
     */
    function getName($name)
    {
        if (isset($this->form[$name]) == false) {
            return null;
        }
        if (isset($this->form[$name]['name']) && $this->form[$name]['name'] != null) {
            return $this->form[$name]['name'];
        }

        // try message catalog
        return $this->i18n->get($name);
    }

    /**
     *  ユーザから送信されたフォーム値をフォーム値定義に従ってインポートする
     *
     *  @access public
     */
    function setFormVars()
    {
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
            $http_vars =& $_POST;
        } else {
            $http_vars =& $_GET;
        }

        foreach ($this->form as $name => $def) {
            $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
            if ($type == VAR_TYPE_FILE) {
                // ファイルの場合

                // 値の有無の検査
                if (isset($_FILES[$name]) == false || is_null($_FILES[$name])) {
                    $this->form_vars[$name] = null;
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($_FILES[$name]['tmp_name']) == false) {
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($_FILES[$name]['tmp_name'])) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                $files = null;
                if (is_array($def['type'])) {
                    $files = array();
                    // ファイルデータを再構成
                    foreach (array_keys($_FILES[$name]['name']) as $key) {
                        $files[$key] = array();
                        $files[$key]['name'] = $_FILES[$name]['name'][$key];
                        $files[$key]['type'] = $_FILES[$name]['type'][$key];
                        $files[$key]['size'] = $_FILES[$name]['size'][$key];
                        $files[$key]['tmp_name'] = $_FILES[$name]['tmp_name'][$key];
                        $files[$key]['error'] = $_FILES[$name]['error'][$key];
                    }
                } else {
                    $files = $_FILES[$name];
                }

                // 値のインポート
                $this->form_vars[$name] = $files;

            } else {
                // ファイル以外の場合

                // 値の有無の検査
                if (isset($http_vars[$name]) == false || is_null($http_vars[$name])) {
                    $this->form_vars[$name] = null;
                    if (isset($http_vars["{$name}_x"]) && isset($http_vars["{$name}_y"])) {
                        // 以前の仕様に合わせる
                        $this->form_vars[$name] = $http_vars["{$name}_x"];
                    }
                    continue;
                }

                // 配列構造の検査
                if (is_array($def['type'])) {
                    if (is_array($http_vars[$name]) == false) {
                        // 厳密には、この配列の各要素はスカラーであるべき
                        // TODO: 多次元の配列にも対応する
                        $this->handleError($name, E_FORM_WRONGTYPE_ARRAY);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                } else {
                    if (is_array($http_vars[$name])) {
                        $this->handleError($name, E_FORM_WRONGTYPE_SCALAR);
                        $this->form_vars[$name] = null;
                        continue;
                    }
                }

                // 値のインポート
                $this->form_vars[$name] = $http_vars[$name];
            }
        }
    }

    /**
     *  フォーム値へのアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   フォーム値の名称
     *  @param  string  $value  設定する値
     */
    function set($name, $value)
    {
        $this->form_vars[$name] = $value;
    }

    /**
     *  フォーム値定義を設定する
     *
     *  @access public
     *  @param  string  $name   設定するフォーム名(省略可:nullなら全ての定義を設定する)
     *  @param  array   $value  設定するフォーム値定義
     *  @return array   フォーム値定義
     */
    function setDef($name, $value)
    {
        if (is_null($name)) {
            $this->form = $value;
        }

        $this->form[$name] = $value;
    }

    /**
     *  フォーム値を配列にして返す
     *
     *  @access public
     *  @param  bool    $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->form_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  アプリケーション設定値のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return mixed   アプリケーション設定値
     */
    function getApp($name)
    {
        if (isset($this->app_vars[$name]) == false) {
            return null;
        }
        return $this->app_vars[$name];
    }

    /**
     *  アプリケーション設定値のアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @param  mixed   $value  値
     */
    function setApp($name, $value)
    {
        $this->app_vars[$name] = $value;
    }

    /**
     *  アプリケーション設定値を配列にして返す
     *
     *  @access public
     *  @param  boolean $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getAppArray($escape = true)
    {
        $retval = array();

        $this->_getArray($this->app_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)のアクセサ(R)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @return mixed   アプリケーション設定値
     */
    function getAppNE($name)
    {
        if (isset($this->app_ne_vars[$name]) == false) {
            return null;
        }
        return $this->app_ne_vars[$name];
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)のアクセサ(W)
     *
     *  @access public
     *  @param  string  $name   キー
     *  @param  mixed   $value  値
     */
    function setAppNE($name, $value)
    {
        $this->app_ne_vars[$name] = $value;
    }

    /**
     *  アプリケーション設定値(自動エスケープなし)を配列にして返す
     *
     *  @access public
     *  @param  boolean $escape HTMLエスケープフラグ(true:エスケープする)
     *  @return array   フォーム値を格納した配列
     */
    function &getAppNEArray($escape = false)
    {
        $retval = array();

        $this->_getArray($this->app_ne_vars, $retval, $escape);

        return $retval;
    }

    /**
     *  フォームを配列にして返す(内部処理)
     *
     *  @access private
     *  @param  array   &$vars      フォーム(等)の配列
     *  @param  array   &$retval    配列への変換結果
     *  @param  bool    $escape     HTMLエスケープフラグ(true:エスケープする)
     */
    function _getArray(&$vars, &$retval, $escape)
    {
        foreach (array_keys($vars) as $name) {
            if (is_array($vars[$name])) {
                $retval[$name] = array();
                $this->_getArray($vars[$name], $retval[$name], $escape);
            } else {
                $retval[$name] = $escape ? htmlspecialchars($vars[$name], ENT_QUOTES) : $vars[$name];
            }
        }
    }

    /**
     *  追加検証強制フラグを取得する
     *
     *  @access public
     *  @return bool    true:追加検証強制(通常検証でエラーが発生した場合でも_validatePlus()が呼び出される) false:追加検証非強制
     */
    function isForceValidatePlus()
    {
        return $this->force_validate_plus;
    }

    /**
     *  追加検証強制フラグを設定する
     *
     *  @access public
     *  @param  $force_validate_plus    追加検証強制フラグ
     */
    function setForceValidatePlus($force_validate_plus)
    {
        $this->force_validate_plus = $force_validate_plus;
    }

    /**
     *  フォーム値検証メソッド
     *
     *  @access public
     *  @return int     発生したエラーの数
     */
    function validate()
    {
        foreach ($this->form as $name => $def) {
            // 配列でラップする
            unset($form_vars);
            if (is_null($this->form_vars[$name])) {
                $form_vars = array();
            } else if (is_array($def['type'])) {
                $form_vars =& $this->form_vars[$name];
            } else {
                $form_vars = array(& $this->form_vars[$name]);
            }

            // ファイルの場合は配列で1つvalidならrequired条件をクリアする
            // TODO: この数を指定できるようにする
            $type = is_array($def['type']) ? $def['type'][0] : $def['type'];
            $valid_keys = array();
            $required_num = max(1, $type == VAR_TYPE_FILE ? 1 : count($form_vars));

            foreach (array_keys($form_vars) as $key) {
                // filter
                if ($type != VAR_TYPE_FILE) {
                    $form_vars[$key] =
                        $this->_filter($form_vars[$key], $def['filter']);
                }

                // 値が空かチェック
                if ($type == VAR_TYPE_FILE) {
                    if ($form_vars[$key]['size'] == 0
                        || is_uploaded_file($form_vars[$key]['tmp_name']) == false) {
                        continue;
                    }
                } else {
                    if (is_scalar($form_vars[$key]) == false
                        || strlen($form_vars[$key]) == 0) {
                        continue;
                    }
                }

                // valid_keys に追加
                $valid_keys[] = $key;

                // _validate
                $this->_validate($name, $form_vars[$key], $def);
            }

            // required の判定
            if ($def['required'] && (count($valid_keys) < $required_num)) {
                $this->handleError($name, E_FORM_REQUIRED);
                continue;
            }

            // カスタムメソッドの実行
            // TODO: 配列とスカラーでの仕様を明確にする
            if ($def['custom'] != null && is_array($def['type'])) {
                $this->_validateCustom($def['custom'], $name);
            }
        }

        if ($this->ae->count() == 0 || $this->isForceValidatePlus()) {
            // 追加検証メソッド
            $this->_validatePlus();
        }

        return $this->ae->count();
    }

    /**
     *  チェックメソッド(基底クラス)
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return array   チェック対象のフォーム値(エラーが無い場合はnull)
     */
    function check($name)
    {
        if (is_null($this->form_vars[$name]) || $this->form_vars[$name] === "") {
            return null;
        }

        // Ethna_Backendの設定
        $c =& Ethna_Controller::getInstance();
        $this->backend =& $c->getBackend();

        return to_array($this->form_vars[$name]);
    }

    /**
     *  チェックメソッド: 機種依存文字
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkVendorChar($name)
    {
        $null = null;
        $string = $this->form_vars[$name];

        for ($i = 0; $i < strlen($string); $i++) {
            /* JIS13区のみチェック */
            $c = ord($string{$i});
            if ($c < 0x80) {
                /* ASCII */
            } else if ($c == 0x8e) {
                /* 半角カナ */
                $i++;
            } else if ($c == 0x8f) {
                /* JIS X 0212 */
                $i += 2;
            } else if ($c == 0xad || ($c >= 0xf9 && $c <= 0xfc)) {
                /* IBM拡張文字 / NEC選定IBM拡張文字 */
                return $this->ae->add($name, '{form}に機種依存文字が入力されています', E_FORM_INVALIDCHAR);
            } else {
                $i++;
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: bool値
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkBoolean($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if ($v != "0" && $v != "1") {
                return $this->ae->add($name, '{form}を正しく入力してください', E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: メールアドレス
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkMailaddress($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (Ethna_Util::checkMailaddress($v) == false) {
                return $this->ae->add($name, '{form}を正しく入力してください', E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  チェックメソッド: URL
     *
     *  @access public
     *  @param  string  $name   フォーム項目名
     *  @return object  Ethna_Error エラーオブジェクト(エラーが無い場合はnull)
     */
    function &checkURL($name)
    {
        $null = null;
        $form_vars = $this->check($name);

        if ($form_vars == null) {
            return $null;
        }

        foreach ($form_vars as $v) {
            if ($v === "") {
                continue;
            }
            if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/', $v) == 0) {
                return $this->ae->add($name, '{form}を正しく入力してください', E_FORM_INVALIDCHAR);
            }
        }

        return $null;
    }

    /**
     *  フォーム値をhiddenタグとして返す
     *
     *  @access public
     *  @param  array   $include_list   配列が指定された場合、その配列に含まれるフォーム項目のみが対象となる
     *  @param  array   $exclude_list   配列が指定された場合、その配列に含まれないフォーム項目のみが対象となる
     *  @return string  hiddenタグとして記述されたHTML
     */
    function getHiddenVars($include_list = null, $exclude_list = null)
    {
        $hidden_vars = "";
        foreach ($this->form as $key => $value) {
            if (is_array($include_list) == true && in_array($key, $include_list) == false) {
                continue;
            }
            if (is_array($exclude_list) == true && in_array($key, $exclude_list) == true) {
                continue;
            }

            $form_value = $this->form_vars[$key];
            if (is_array($form_value) == false) {
                $form_value = array($form_value);
                $form_array = false;
            } else {
                $form_array = true;
            }
            foreach ($form_value as $k => $v) {
                if ($form_array) {
                    $form_name = "$key" . "[$k]";
                } else {
                    $form_name = $key;
                }
                $hidden_vars .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\" />\n",
                    $form_name, htmlspecialchars($v, ENT_QUOTES));
            }
        }
        return $hidden_vars;
    }

    /**
     *  フォーム値検証のエラー処理を行う
     *
     *  @access public
     *  @param  string      $name   フォーム項目名
     *  @param  int         $code   エラーコード
     */
    function handleError($name, $code)
    {
        $def = $this->getDef($name);

        // ユーザ定義エラーメッセージ
        $code_map = array(
            E_FORM_REQUIRED     => 'required_error',
            E_FORM_WRONGTYPE_SCALAR => 'type_error',
            E_FORM_WRONGTYPE_ARRAY  => 'type_error',
            E_FORM_WRONGTYPE_INT    => 'type_error',
            E_FORM_WRONGTYPE_FLOAT  => 'type_error',
            E_FORM_WRONGTYPE_DATETIME   => 'type_error',
            E_FORM_WRONGTYPE_BOOLEAN    => 'type_error',
            E_FORM_MIN_INT      => 'min_error',
            E_FORM_MIN_FLOAT    => 'min_error',
            E_FORM_MIN_DATETIME => 'min_error',
            E_FORM_MIN_FILE     => 'min_error',
            E_FORM_MIN_STRING   => 'min_error',
            E_FORM_MAX_INT      => 'max_error',
            E_FORM_MAX_FLOAT    => 'max_error',
            E_FORM_MAX_DATETIME => 'max_error',
            E_FORM_MAX_FILE     => 'max_error',
            E_FORM_MAX_STRING   => 'max_error',
            E_FORM_REGEXP       => 'regexp_error',
        );
        if (array_key_exists($code_map[$code], $def)) {
            $this->ae->add($name, $def[$code_map[$code]], $code);
            return;
        }

        if ($code == E_FORM_REQUIRED) {
            switch ($def['form_type']) {
            case FORM_TYPE_TEXT:
            case FORM_TYPE_PASSWORD:
            case FORM_TYPE_TEXTAREA:
            case FORM_TYPE_SUBMIT:
                $message = "{form}を入力して下さい";
                break;
            case FORM_TYPE_SELECT:
            case FORM_TYPE_RADIO:
            case FORM_TYPE_CHECKBOX:
            case FORM_TYPE_FILE:
                $message = "{form}を選択して下さい";
                break;
            default:
                $message = "{form}を入力して下さい";
                break;
            }
        } else if ($code == E_FORM_WRONGTYPE_SCALAR) {
            $message = "{form}にはスカラー値を入力して下さい";
        } else if ($code == E_FORM_WRONGTYPE_ARRAY) {
            $message = "{form}には配列を入力して下さい";
        } else if ($code == E_FORM_WRONGTYPE_INT) {
            $message = "{form}には数字(整数)を入力して下さい";
        } else if ($code == E_FORM_WRONGTYPE_FLOAT) {
            $message = "{form}には数字(小数)を入力して下さい";
        } else if ($code == E_FORM_WRONGTYPE_DATETIME) {
            $message = "{form}には日付を入力して下さい";
        } else if ($code == E_FORM_WRONGTYPE_BOOLEAN) {
            $message = "{form}には1または0のみ入力できます";
        } else if ($code == E_FORM_MIN_INT) {
            $this->ae->add($name, "{form}には%d以上の数字(整数)を入力して下さい", $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_FLOAT) {
            $this->ae->add($name, "{form}には%f以上の数字(小数)を入力して下さい", $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_DATETIME) {
            $this->ae->add($name, "{form}には%s以降の日付を入力して下さい", $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_FILE) {
            $this->ae->add($name, "{form}には%dKB以上のファイルを指定して下さい", $code, $def['min']);
            return;
        } else if ($code == E_FORM_MIN_STRING) {
            $this->ae->add($name, "{form}には全角%d文字以上(半角%d文字以上)入力して下さい", $code, intval($def['min']/2), $def['min']);
            return;
        } else if ($code == E_FORM_MAX_INT) {
            $this->ae->add($name, "{form}には%d以下の数字(整数)を入力して下さい", $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_FLOAT) {
            $this->ae->add($name, "{form}には%f以下の数字(小数)を入力して下さい", $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_DATETIME) {
            $this->ae->add($name, "{form}には%s以前の日付を入力して下さい", $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_FILE) {
            $this->ae->add($name, "{form}には%dKB以下のファイルを指定して下さい", $code, $def['max']);
            return;
        } else if ($code == E_FORM_MAX_STRING) {
            $this->ae->add($name, "{form}は全角%d文字以下(半角%d文字以下)で入力して下さい", $code, intval($def['max']/2), $def['max']);
            return;
        } else if ($code == E_FORM_REGEXP) {
            $message = "{form}を正しく入力してください";
        }

        $this->ae->add($name, $message, $code);
    }

    /**
     *  ユーザ定義検証メソッド(フォーム値間の連携チェック等)
     *
     *  @access protected
     */
    function _validatePlus()
    {
    }

    /**
     *  フォーム値検証メソッド(実体)
     *
     *  @access private
     *  @param  string  $name       フォーム項目名
     *  @param  mixed   $var        フォーム値(配列であれば個々の中身)
     *  @param  array   $def        フォーム値定義
     *  @param  bool    $test       エラーオブジェクト登録フラグ(true:登録しない)
     *  @return bool    true:正常終了 false:エラー
     */
    function _validate($name, $var, $def, $test = false)
    {
        $type = is_array($def['type']) ? $def['type'][0] : $def['type'];

        // type
        if ($type == VAR_TYPE_INT) {
            if (!preg_match('/^-?\d+$/', $var)) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_WRONGTYPE_INT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_FLOAT) {
            if (!preg_match('/^-?\d+$/', $var) && !preg_match('/^-?\d+\.\d+$/', $var)) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_WRONGTYPE_FLOAT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_DATETIME) {
            $r = strtotime($var);
            if ($r == -1 || $r === false) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_WRONGTYPE_DATETIME);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_BOOLEAN) {
            if ($var != "1" && $var != "0") {
                if ($test == false) {
                    $this->handleError($name, E_FORM_WRONGTYPE_BOOLEAN);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_STRING) {
            // nothing to do
        } else if ($type == VAR_TYPE_FILE) {
            // nothing to do
        }

        // min
        if ($type == VAR_TYPE_INT) {
            if (!is_null($def['min']) && $var < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MIN_INT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_FLOAT) {
            if (!is_null($def['min']) && $var < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MIN_FLOAT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_DATETIME) {
            if (!is_null($def['min'])) {
                $t_min = strtotime($def['min']);
                $t_var = strtotime($var);
                if ($t_var < $t_min) {
                    if ($test == false) {
                        $this->handleError($name, E_FORM_MIN_DATETIME);
                    }
                }
                return false;
            }
        } else if ($type == VAR_TYPE_FILE) {
            if (!is_null($def['min'])) {
                $st = @stat($var['tmp_name']);
                if ($st[7] < $def['min'] * 1024) {
                    if ($test == false) {
                        $this->handleError($name, E_FORM_MIN_FILE);
                    }
                    return false;
                }
            }
        } else {
            if (!is_null($def['min']) && strlen($var) < $def['min']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MIN_STRING);
                }
                return false;
            }
        }

        // max
        if ($type == VAR_TYPE_INT) {
            if (!is_null($def['max']) && $var > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MAX_INT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_FLOAT) {
            if (!is_null($def['max']) && $var > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MAX_FLOAT);
                }
                return false;
            }
        } else if ($type == VAR_TYPE_DATETIME) {
            if (!is_null($def['max'])) {
                $t_min = strtotime($def['max']);
                $t_var = strtotime($var);
                if ($t_var > $t_min) {
                    if ($test == false) {
                        $this->handleError($name, E_FORM_MAX_DATETIME);
                    }
                }
                return false;
            }
        } else if ($type == VAR_TYPE_FILE) {
            if (!is_null($def['max'])) {
                $st = @stat($var['tmp_name']);
                if ($st[7] > $def['max'] * 1024) {
                    if ($test == false) {
                        $this->handleError($name, E_FORM_MAX_FILE);
                    }
                    return false;
                }
            }
        } else {
            if (!is_null($def['max']) && strlen($var) > $def['max']) {
                if ($test == false) {
                    $this->handleError($name, E_FORM_MAX_STRING);
                }
                return false;
            }
        }

        // regexp
        if ($type != VAR_TYPE_FILE && $def['regexp'] != null && strlen($var) > 0
            && preg_match($def['regexp'], $var) == 0) {
            if ($test == false) {
                $this->handleError($name, E_FORM_REGEXP);
            }
            return false;
        }

        // custom (TODO: respect $test flag)
        if ($def['custom'] != null) {
            if (isset($this->form[$name]['type']) && is_array($this->form[$name]['type']) == false) {
                $this->_validateCustom($def['custom'], $name);
            } else {
                // 配列指定の場合は全要素一括でカスタムメソッドを実行するためスキップ
            }
        }

        return true;
    }

    /**
     *  カスタムチェックメソッドを実行する
     *
     *  @access protected
     *  @param  string  $method_list    カスタムメソッド名(カンマ区切り)
     *  @param  string  $name           フォーム項目名
     */
    function _validateCustom($method_list, $name)
    {
        $method_list = preg_split('/\s*,\s*/', $method_list, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($method_list) == false) {
            return;
        }
        foreach ($method_list as $method) {
            $this->$method($name);
        }
    }

    /**
     *  フォーム値に変換フィルタを適用する
     *
     *  @access private
     *  @param  mixed   $value  フォーム値
     *  @param  int     $filter フィルタ定義
     *  @return mixed   変換結果
     */
    function _filter($value, $filter)
    {
        if (is_null($filter)) {
            return $value;
        }

        foreach (preg_split('/\s*,\s*/', $filter) as $f) {
            $method = sprintf('_filter_%s', $f);
            if (method_exists($this, $method) == false) {
                $this->logger->log(LOG_WARNING, 'filter method is not defined [%s]', $method);
                continue;
            }
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     *  フォーム値変換フィルタ: 全角英数字->半角英数字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_alnum_zentohan($value)
    {
        return mb_convert_kana($value, "a");
    }

    /**
     *  フォーム値変換フィルタ: 全角数字->半角数字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_numeric_zentohan($value)
    {
        return mb_convert_kana($value, "n");
    }

    /**
     *  フォーム値変換フィルタ: 全角英字->半角英字
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_alphabet_zentohan($value)
    {
        return mb_convert_kana($value, "r");
    }

    /**
     *  フォーム値変換フィルタ: 左空白削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_ltrim($value)
    {
        return ltrim($value);
    }

    /**
     *  フォーム値変換フィルタ: 右空白削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_rtrim($value)
    {
        return rtrim($value);
    }

    /**
     *  フォーム値変換フィルタ: NULL(0x00)削除
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_ntrim($value)
    {
        return str_replace("\x00", "", $value);
    }

    /**
     *  フォーム値変換フィルタ: 半角カナ->全角カナ
     *
     *  @access protected
     *  @param  mixed   $value  フォーム値
     *  @return mixed   変換結果
     */
    function _filter_kana_hantozen($value)
    {
        return mb_convert_kana($value, "K");
    }

    /**
     *  フォーム値定義テンプレートを設定する
     *
     *  @access protected
     *  @param  array   $form_template  フォーム値テンプレート
     *  @return array   フォーム値テンプレート
     */
    function _setFormTemplate($form_template)
    {
        return $form_template;
    }

    /**
     *  フォーム値定義を設定する
     *
     *  @access protected
     */
    function _setFormDef()
    {
        foreach ($this->form as $key => $value) {
            if (array_key_exists($key, $this->form_template) && is_array($this->form_template)) {
                foreach ($this->form_template[$key] as $def_key => $def_value) {
                    if (array_key_exists($def_key, $value) == false) {
                        $this->form[$key][$def_key] = $def_value;
                    }
                }
            }
        }
    }
}
// }}}
?>
