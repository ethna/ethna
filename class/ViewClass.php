<?php
// vim: foldmethod=marker
/**
 *  ViewClass.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_ViewClass
/**
 *  viewクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_ViewClass
{
    /**#@+
     *  @access private
     */

    /** @var Ethna_Controller $ctl    Controllerオブジェクト */
    public $ctl;

    /** @var Ethna_Backend $backend       backendオブジェクト */
    public $backend;

    /** @var Ethna_Config $config        設定オブジェクト    */
    public $config;

    /** @var Ethna_I18N $i18n          i18nオブジェクト */
    public $i18n;

    /** @var Ethna_Logger $logger    ログオブジェクト */
    public $logger;

    /** @var Ethna_Plugin $plugin   プラグインオブジェクト */
    public $plugin;

    /** @var Ethna_ActionError $action_error   アクションエラーオブジェクト */
    public $action_error;

    /** @var Ethna_ActionError $ae   アクションエラーオブジェクト(省略形) */
    public $ae;

    /** @var Ethna_ActionForm $action_form    アクションフォームオブジェクト */
    public $action_form;

    /** @var Ethna_ActionForm $af    アクションフォームオブジェクト(省略形) */
    public $af;

    /** @var array   アクションフォームオブジェクト(helper) */
    public $helper_action_form = array();

    /** @var  array   helperでhtmlのattributeにはしないパラメータの一覧 */
    public $helper_parameter_keys = array(
        'default',
        'option',
        'separator'
    );

    /** @var Ethna_Session $session       セッションオブジェクト */
    public $session;

    /** @var string  遷移名 */
    public $forward_name;

    /** @var string  遷移先テンプレートファイル名 */
    public $forward_path;

    /** @var  boolean  配列フォームを呼んだカウンタをリセットするか否か */
    public $reset_counter = false;

    /**#@-*/

    /**#@+
     *  @access public
     */

    /** @var  boolean  デフォルトのヘッダ出力を使用するか否か  */
    /**                ヘッダ出力を改造する場合はfalseにする   */
    public $has_default_header = true;

    /** @var  array    default header */
    public $default_header = array(
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    );

    // {{{ Ethna_ViewClass
    /**
     *  Ethna_ViewClassのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   $backend    backendオブジェクト
     *  @param  string  $forward_name   ビューに関連付けられている遷移名
     *  @param  string  $forward_path   ビューに関連付けられているテンプレートファイル名
     */
    public function __construct($backend, $forward_name, $forward_path)
    {
        $c = $backend->getController();
        $this->ctl = $c;
        $this->backend = $backend;
        $this->config = $this->backend->getConfig();
        $this->i18n = $this->backend->getI18N();
        $this->logger = $this->backend->getLogger();
        $this->plugin = $this->backend->getPlugin();

        $this->action_error = $this->backend->getActionError();
        $this->ae = $this->action_error;

        $this->action_form = $this->backend->getActionForm();
        $this->af = $this->action_form;

        $this->session = $this->backend->getSession();

        $this->forward_name = $forward_name;
        $this->forward_path = $forward_path;

        foreach (array_keys($this->helper_action_form) as $action) {
            $this->addActionFormHelper($action);
        }
    }
    // }}}

    // {{{ preforward
    /**
     *  画面表示前処理
     *
     *  テンプレートに設定する値でコンテキストに依存しないものは
     *  ここで設定する(例:セレクトボックス等)
     *
     *  @access public
     *  @param  mixed  $params  アクションクラスから返された引数 
     *                          array('forward_name', $param) の形でアクション
     *                          から値を返すことで、$params に値が渡されます。
     */
    public function preforward()
    {
    }
    // }}}

    // {{{ forward
    /**
     *  遷移名に対応する画面を出力する
     *
     *  特殊な画面を表示する場合を除いて特にオーバーライドする必要は無い
     *  (preforward()のみオーバーライドすれば良い)
     *
     *  @access public
     */
    public function forward()
    {
        $renderer = $this->_getRenderer();
        $this->_setDefault($renderer);

        if ($this->has_default_header) {
            $this->default_header['Content-Type'] = 'text/html; charset=' . $this->ctl->getClientEncoding();
            $this->header($this->default_header);
        }

        $e = $renderer->perform($this->forward_path);

        if (Ethna::isError($e)) {
            echo '<h1>Rendering error:</h1>';
            echo '<h2>Message: ' . $e->getMessage() . '</h2>';
        }
    }
    // }}}

    // {{{ header
    /**
     *  HTTPヘッダを送信します。
     *
     *  @param  mixed   ヘッダを設定する値
     *                  配列指定の場合、header => value の形式
     *                  整数指定の場合は、HTTPステータスコード
     *                  文字列で指定する場合は、ヘッダ出力をそのまま指定
     *  @access public
     */
    public function header($status)
    {
        if (headers_sent()) {
            return false;
        }

        if (is_array($status)) {
            foreach ($status as $key => $status) {
                header ($key . ": " . $status);
            }
        } else if (is_int($status)) {
            $codes = array(
                100 => "Continue",
                101 => "Switching Protocols",
                200 => "OK",
                201 => "Created",
                202 => "Accepted",
                203 => "Non-Authoritative Information",
                204 => "No Content",
                205 => "Reset Content",
                206 => "Partial Content",
                300 => "Multiple Choices",
                301 => "Moved Permanently",
                302 => "Found",
                303 => "See Other",
                304 => "Not Modified",
                305 => "Use Proxy",
                307 => "Temporary Redirect",
                400 => "Bad Request",
                401 => "Unauthorized",
                402 => "Payment Required",
                403 => "Forbidden",
                404 => "Not Found",
                405 => "Method Not Allowed",
                406 => "Not Acceptable",
                407 => "Proxy Authentication Required",
                408 => "Request Time-out",
                409 => "Conflict",
                410 => "Gone",
                411 => "Length Required",
                412 => "Precondition Failed",
                413 => "Request Entity Too Large",
                414 => "Request-URI Too Large",
                415 => "Unsupported Media Type",
                416 => "Requested range not satisfiable",
                417 => "Expectation Failed",
                500 => "Internal Server Error",
                501 => "Not Implemented",
                502 => "Bad Gateway",
                503 => "Service Unavailable",
                504 => "Gateway Time-out"
            );

            if (array_key_exists($status, $codes)) {
                header("HTTP/1.1 {$status} {$codes[$status]}");
            }
        } else {
            // check valid header
            if (preg_match("/^.+\:\s.+$/", $status)) {
                header($status);
            }
        }
    }
    // }}}

    // {{{ redirect
    /**
     *  リダイレクト処理
     *   - デフォルトのヘッダを送信しない
     *   - レイアウトテンプレートの使用をしない
     *
     *  @param  string  リダイレクト先(URL)
     *  @param  int     HTTPステータスコード (3xx)
     *  @access public
     */
    public function redirect($url, $staus_code = 302)
    {
        $this->has_default_header = false;
        $this->use_layout = false;

        $this->header($staus_code);
        $this->header(array('Location' => $url));
    }
    // }}}

    // {{{ getCurrentForwardName()
    /**
     *  getCurrentForwardName
     *
     *  @access public
     */
    public function getCurrentForwardName()
    {
        return $this->forward_name;
    }
    // }}}

    // {{{ templateExists
    /**
     *  テンプレートファイルが存在するか否かを返します。
     *
     * @param   string  $filename  チェック対象のテンプレートファイル
     * @access  public
     * @return  boolean 指定したテンプレートファイルが存在すればtrue
     *                  存在しなければfalse
     */
    public function templateExists($filename)
    {
        $renderer = $this->_getRenderer();
        if ($renderer->templateExists($filename)) {
            return true;
        }
        else {
            return false;
        }
    }
    // }}}

    // {{{ error 
    /**
     *  エラーページ出力用のHTTPステータスコードを指定します。
     *
     *  @param  int  HTTPステータスコード
     *  @access public
     */
    public function error($code)
    {
        $this->header($code);

        // template 以下に error404.tpl とかがあれば， 
        // preforward で $this->error(404); とかすればいい
        $this->forward_path = "error{$code}.tpl";
    }
    // }}}

    // {{{ addActionFormHelper
    /**
     *  helperアクションフォームオブジェクトを設定する
     *
     *  @param  string $action アクション名
     *  @param  boolean $dynamic_helper 動的フォームヘルパを呼ぶか否か
     *  @access public
     */
    public function addActionFormHelper($action, $dynamic_helper = false)
    {
        //
        //  既に追加されている場合は処理をしない
        //
        if (isset($this->helper_action_form[$action])
            && is_object($this->helper_action_form[$action])) {
            return;
        }

        //    現在のアクションと等しければ、対応する
        //    アクションフォームを設定
        $ctl = Ethna_Controller::getInstance();
        if ($action === $ctl->getCurrentActionName()) {
            $this->helper_action_form[$action] = $this->af;
        } else {
            //    アクションが異なる場合
            $form_name = $ctl->getActionFormName($action);
            if ($form_name === null) {
                $this->logger->log(LOG_WARNING,
                    'action form for the action [%s] not found.', $action);
                return;
            }
            $this->helper_action_form[$action] = new $form_name($ctl);
        }

        //   動的フォームを設定するためのヘルパメソッドを呼ぶ
        if ($dynamic_helper) {
            $af = $this->helper_action_form[$action];
            $af->setFormDef_ViewHelper();
        }
    }
    // }}}

    // {{{ clearActionFormHelper
    /**
     *  helperアクションフォームオブジェクトを削除する
     *
     *  @access public
     */
    public function clearActionFormHelper($action)
    {
        unset($this->helper_action_form[$action]);
    }
    // }}}

    // {{{ _getHelperActionForm
    /**
     *  アクションフォームオブジェクト(helper)を取得する
     *  $action === null で $name が指定されているときは、$nameの定義を
     *  含むものを探す
     *
     *  @access protected
     *  @param  string  action  取得するアクション名
     *  @param  string  name    定義されていることを期待するフォーム名
     *  @return object  Ethna_ActionFormまたは継承オブジェクト
     */
    public function _getHelperActionForm($action = null, $name = null)
    {
        // $action が指定されている場合
        if ($action !== null) {
            if (isset($this->helper_action_form[$action])
                && is_object($this->helper_action_form[$action])) {
                return $this->helper_action_form[$action];
            } else {
                $this->logger->log(LOG_WARNING,
                    'helper action form for action [%s] not found',
                    $action);
                return null;
            }
        }

        // 最初に $this->af を調べる
        $def = $this->af->getDef($name);
        if ($def !== null) {
            return $this->af;
        }

        // $this->helper_action_form を順に調べる
        foreach (array_keys($this->helper_action_form) as $action) {
            if (is_object($this->helper_action_form[$action]) === false) {
                continue;
            }
            $af = $this->helper_action_form[$action];
            $def = $af->getDef($name);
            if (is_null($def) === false) {
                return $af;
            }
        }

        // 見付からなかった
        $this->logger->log(LOG_WARNING,
            'action form defining form [%s] not found', $name);
        return null;
    }
    // }}}

    // {{{ resetFormCounter
    /**
     *  フォームヘルパ用、内部フォームカウンタをリセットする
     *
     *  @access public
     */
    public function resetFormCounter()
    {
        $this->reset_counter = true;
    }
    // }}}

    // {{{ getFormName
    /**
     *  指定されたフォーム項目に対応するフォーム名(w/ レンダリング)を取得する
     *
     *  @access public
     */
    public function getFormName($name, $action, $params)
    {
        $af = $this->_getHelperActionForm($action, $name);
        if ($af === null) {
            return $name;
        }

        $def = $af->getDef($name);
        if ($def === null || isset($def['name']) === false) {
            return $name;
        }

        return $def['name'];
    }
    // }}}

    // {{{ getFormSubmit
    /**
     *  submitボタンを取得する(送信先アクションで受け取るよう
     *  定義されていないときに、たんにsubmitボタンを作るのに使う)
     *
     *  @access public
     */
    public function getFormSubmit($params)
    {
        if (isset($params['type']) === false) {
            $params['type'] = 'submit';
        }
        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ getFormInput
    /**
     *  指定されたフォーム項目に対応するフォームタグを取得する
     *
     *  @access public
     *  @todo   JavaScript対応
     */
    public function getFormInput($name, $action, $params)
    {
        $af = $this->_getHelperActionForm($action, $name);
        if ($af === null) {
            return '';
        }

        $def = $af->getDef($name);
        if ($def === null) {
            return '';
        }

        if (isset($def['form_type']) === false) {
            $def['form_type'] = FORM_TYPE_TEXT;
        }

        // 配列フォームが何回呼ばれたかを保存するカウンタ
        if (isset($def['type']) && is_array($def['type'])) {
            static $form_counter = array();
            if ($this->reset_counter) {
                $form_counter = array();
                $this->reset_counter = false;
            }

            if (isset($form_counter[$action]) === false) {
                $form_counter[$action] = array();
            }
            if (isset($form_counter[$action][$name]) === false) {
                $form_counter[$action][$name] = 0;
            }
            $def['_form_counter'] = $form_counter[$action][$name]++;
        }

        switch ($def['form_type']) {
        case FORM_TYPE_BUTTON:
            $input = $this->_getFormInput_Button($name, $def, $params);
            break;

        case FORM_TYPE_CHECKBOX:
            $def['option'] = $this->_getSelectorOptions($af, $def, $params);
            $input = $this->_getFormInput_Checkbox($name, $def, $params);
            break;

        case FORM_TYPE_FILE:
            $input = $this->_getFormInput_File($name, $def, $params);
            break;

        case FORM_TYPE_HIDDEN:
            $input = $this->_getFormInput_Hidden($name, $def, $params);
            break;

        case FORM_TYPE_PASSWORD:
            $input = $this->_getFormInput_Password($name, $def, $params);
            break;

        case FORM_TYPE_RADIO:
            $def['option'] = $this->_getSelectorOptions($af, $def, $params);
            $input = $this->_getFormInput_Radio($name, $def, $params);
            break;

        case FORM_TYPE_SELECT:
            $def['option'] = $this->_getSelectorOptions($af, $def, $params);
            $input = $this->_getFormInput_Select($name, $def, $params);
            break;

        case FORM_TYPE_SUBMIT:
            $input = $this->_getFormInput_Submit($name, $def, $params);
            break;

        case FORM_TYPE_TEXTAREA:
            $input = $this->_getFormInput_Textarea($name, $def, $params);
            break;

        case FORM_TYPE_TEXT:
        default:
            $input = $this->_getFormInput_Text($name, $def, $params);
            break;
        }

        return $input;
    }
    // }}}

    // {{{ getFormBlock
    /**
     *  フォームタグを取得する(type="form")
     *
     *  @access protected
     */
    public function getFormBlock($content, $params)
    {
        // method
        if (isset($params['method']) === false) {
            $params['method'] = 'post';
        }

        return $this->_getFormInput_Html('form', $params, $content, false);
    }
    // }}}

    // {{{ _getSelectorOptions
    /**
     *  select, radio, checkbox の選択肢を取得する
     *
     *  @access protected
     */
    public function _getSelectorOptions($af, $def, $params)
    {
        // $params, $def の順で調べる
        $source = null;
        if (isset($params['option'])) {
            $source = $params['option'];
        } else if (isset($def['option'])) {
            $source = $def['option'];
        }

        // 未定義 or 定義済みの場合はそのまま
        if ($source === null) {
            return null;
        } else if (is_array($source)) {
            return $source;
        }
        
        // 選択肢を取得
        $options = null;
        $split = array_map("trim", explode(',', $source));
        if (count($split) === 1) {
            // アクションフォームから取得
            $method_or_property = $split[0];
            if (method_exists($af, $method_or_property)) {
                $options = $af->$method_or_property();
            } else {
                $options = $af->$method_or_property;
            }
        } else {
            // マネージャから取得
            $mgr = $this->backend->getManager($split[0]);
            $attr_list = $mgr->getAttrList($split[1]);
            if (is_array($attr_list)) {
                foreach ($attr_list as $key => $val) {
                    $options[$key] = $val['name'];
                }
            }
        }

        if (is_array($options) === false) {
            $this->logger->log(LOG_WARNING,
                'selector option is not valid. [actionform=%s, option=%s]',
                get_class($af), $source);
            return null;
        }

        return $options;
    }
    // }}}

    // {{{ _getFormInput_Button
    /**
     *  フォームタグを取得する(type="button")
     *
     *  @access protected
     */
    public function _getFormInput_Button($name, $def, $params)
    {
        $params['type'] = 'button';
        
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }
        if (isset($params['value']) === false) {
            if (isset($def['name'])) {
                $params['value'] = $def['name'];
            }
        }
        if (isset($params['value']) && is_array($params['value'])) {
            $params['value'] = $params['value'][0];
        }

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Checkbox
    /**
     *  チェックボックスタグを取得する(type="check")
     *
     *  @access protected
     */
    public function _getFormInput_Checkbox($name, $def, $params)
    {
        $params['type'] = 'checkbox';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // オプションの一覧(alist)を取得
        if (isset($def['option']) && is_array($def['option'])) {
            $options = $def['option'];
        } else {
            $options = array();
        }

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
        } else if (isset($def['default'])) {
            $current_value = $def['default'];
        } else {
            $current_value = array();
        }
        $current_value = array_map('strval', to_array($current_value));

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
        } else {
            $separator = "\n";
        }

        $ret = array();
        $i = 1;
        foreach ($options as $key => $value) {
            $params['value'] = $key;
            $params['id'] = $name . '_' . $i++;

            // checked
            if (in_array((string) $key, $current_value, true)) {
                $params['checked'] = 'checked';
            } else {
                unset($params['checked']);
            }

            // <input type="checkbox" />
            $input_tag = $this->_getFormInput_Html('input', $params);

            // <label for="id">..</label>
            $ret[] = $this->_getFormInput_Html('label', array('for' => $params['id']),
                                               $input_tag . $value, false);
        }

        return implode($separator, $ret);
    }
    // }}}

    // {{{ _getFormInput_File
    /**
     *  フォームタグを取得する(type="file")
     *
     *  @access protected
     */
    public function _getFormInput_File($name, $def, $params)
    {
        $params['type'] = 'file';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }
        $params['value'] = '';

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Hidden
    /**
     *  フォームタグを取得する(type="hidden")
     *
     *  @access protected
     */
    public function _getFormInput_Hidden($name, $def, $params)
    {
        $params['type'] = 'hidden';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // value
        $value = '';
        if (isset($params['value'])) {
            $value = $params['value'];
        } else if (isset($params['default'])) {
            $value = $params['default'];
        } else if (isset($def['default'])) {
            $value = $def['default'];
        }
        if (is_array($value)) {
            if ($def['_form_counter'] < count($value)) {
                $params['value'] = $value[$def['_form_counter']];
            } else {
                $params['value'] = '';
            }
        } else {
            $params['value'] = $value;
        }

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Password
    /**
     *  フォームタグを取得する(type="password")
     *
     *  @access protected
     */
    public function _getFormInput_Password($name, $def, $params)
    {
        $params['type'] = 'password';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // value
        $value = '';
        if (isset($params['value'])) {
            $value = $params['value'];
        } else if (isset($params['default'])) {
            $value = $params['default'];
        } else if (isset($def['default'])) {
            $value = $def['default'];
        }
        if (is_array($value)) {
            if ($def['_form_counter'] < count($value)) {
                $params['value'] = $value[$def['_form_counter']];
            } else {
                $params['value'] = '';
            }
        } else {
            $params['value'] = $value;
        }

        //   maxlength と フォーム定義のmax連携はサポートしない
        //   @see http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16325

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Radio
    /**
     *  ラジオボタンタグを取得する(type="radio")
     *
     *  @access protected
     */
    public function _getFormInput_Radio($name, $def, $params)
    {
        $params['type'] = 'radio';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // オプションの一覧(alist)を取得
        if (isset($def['option']) && is_array($def['option'])) {
            $options = $def['option'];
        } else {
            $options = array();
        }

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
        } else if (isset($def['default'])) {
            $current_value = $def['default'];
        } else {
            $current_value = null;
        }

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
        } else {
            $separator = "\n";
        }

        $ret = array();
        $i = 1;
        foreach ($options as $key => $value) {
            $params['value'] = $key;
            $params['id'] = $name . '_' . $i++;

            // checked
            if (strcmp($current_value,$key) === 0) {
                $params['checked'] = 'checked';
            } else {
                unset($params['checked']);
            }

            // <input type="radio" />
            $input_tag = $this->_getFormInput_Html('input', $params);

            // <label for="id">..</label>
            $ret[] = $this->_getFormInput_Html('label', array('for' => $params['id']),
                                               $input_tag . $value, false);
        }

        return implode($separator, $ret);
    }
    // }}}

    // {{{ _getFormInput_Select
    /**
     *  セレクトボックスタグを取得する(type="select")
     *
     *  @access protected
     */
    public function _getFormInput_Select($name, $def, $params)
    {
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // オプションの一覧(alist)を取得
        if (isset($def['option']) && is_array($def['option'])) {
            $options = $def['option'];
        } else {
            $options = array();
        }

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
        } else if (isset($def['default'])) {
            $current_value = $def['default'];
        } else {
            $current_value = array(0 => 0,);
        }
        $current_value = array_map('strval', to_array($current_value));

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
        } else {
            $separator = "\n";
        }

        // selectタグの中身を作る
        $contents = array();
        $selected = false;
        foreach ($options as $key => $value) {
            $attr = array('value' => $key);
            $def['_form_counter'] = empty($def['_form_counter']) ? 0 : $def['_form_counter'];
            if (isset($params['multiple']) &&
                    in_array((string)$key, $current_value, true) ||
               !isset($params['multiple']) && $selected === false &&
                    strcmp($current_value[$def['_form_counter']], $key) === 0) {
                $attr['selected'] = 'selected';
                $selected = true;
            }
            $contents[] = $this->_getFormInput_Html('option', $attr, $value);
        }

        // 空エントリ
        if (isset($params['emptyoption'])) {
            $attr = array('value' => '');
            if ($selected === false) {
                $attr['selected'] = 'selected';
            }
            array_unshift($contents,
                          $this->_getFormInput_Html('option',
                                                    $attr,
                                                    $params['emptyoption']));
            unset($params['emptyoption']);
        }

        $element = $separator . implode($separator, $contents) . $separator;
        return $this->_getFormInput_Html('select', $params, $element, false);
    }
    // }}}

    // {{{ _getFormInput_Submit
    /**
     *  フォームタグを取得する(type="submit")
     *
     *  @access protected
     */
    public function _getFormInput_Submit($name, $def, $params)
    {
        $params['type'] = 'submit';
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }
        if (isset($params['value']) === false) {
            if (isset($def['name'])) {
                $params['value'] = $def['name'];
            }
        }
        if (is_array($params['value'])) {
            $params['value'] = $params['value'][0];
        }

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Textarea
    /**
     *  フォームタグを取得する(textarea)
     *
     *  @access protected
     */
    public function _getFormInput_Textarea($name, $def, $params)
    {
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // element
        $element = '';
        if (isset($params['value'])) {
            $element = $params['value'];
        } else if (isset($params['default'])) {
            $element = $params['default'];
        } else if (isset($def['default'])) {
            $element = $def['default'];
        }
        if (is_array($element)) {
            if ($def['_form_counter'] < count($element)) {
                $element = $element[$def['_form_counter']];
            } else {
                $element = '';
            }
        }

        return $this->_getFormInput_Html('textarea', $params, $element);
    }
    // }}}

    // {{{ _getFormInput_Text
    /**
     *  フォームタグを取得する(type="text")
     *
     *  @access protected
     */
    public function _getFormInput_Text($name, $def, $params)
    {
        // type
        $params['type'] = 'text';

        // name
        if (isset($def['type'])) {
            $params['name'] = is_array($def['type']) ? $name . '[]' : $name;
        } else {
            $params['name'] = $name;
        }

        // value
        $value = '';
        if (isset($params['value'])) {
            $value = $params['value'];
        } else if (isset($params['default'])) {
            $value = $params['default'];
        } else if (isset($def['default'])) {
            $value = $def['default'];
        }
        if (is_array($value)) {
            if ($def['_form_counter'] < count($value)) {
                $params['value'] = $value[$def['_form_counter']];
            } else {
                $params['value'] = '';
            }
        } else {
            $params['value'] = $value;
        }

        //   maxlength と フォーム定義のmax連携はサポートしない
        //   @see http://sourceforge.jp/ticket/browse.php?group_id=1343&tid=16325

        return $this->_getFormInput_Html('input', $params);
    }
    // }}}

    // {{{ _getFormInput_Html
    /**
     *  HTMLタグを取得する
     *
     *  @access protected
     */
    public function _getFormInput_Html($tag, $attr, $element = null, $escape_element = true)
    {
        // 不要なパラメータは消す
        foreach ($this->helper_parameter_keys as $key) {
            unset($attr[$key]);
        }

        $r = "<$tag";

        foreach ($attr as $key => $value) {
            if ($value === null) {
                $r .= sprintf(' %s', $key);
            } else {
                $r .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, $this->ctl->getClientEncoding()));
            }
        }

        if ($element === null) {
            $r .= ' />';
        } else if ($escape_element) {
            $r .= sprintf('>%s</%s>', htmlspecialchars($element, ENT_QUOTES, $this->ctl->getClientEncoding()), $tag);
        } else {
            $r .= sprintf('>%s</%s>', $element, $tag);
        }

        return $r;
    }
    // }}}

    // {{{ _getRenderer
    /**
     *  レンダラオブジェクトを取得する
     *
     *  @access protected
     *  @return object  Ethna_Renderer  レンダラオブジェクト
     */
    public function _getRenderer()
    {
        $c = $this->backend->getController();
        $renderer = $c->getRenderer();

        $form_array = $this->af->getArray();
        $app_array = $this->af->getAppArray();
        $app_ne_array = $this->af->getAppNEArray();
        $renderer->setPropByRef('form', $form_array);
        $renderer->setPropByRef('app', $app_array);
        $renderer->setPropByRef('app_ne', $app_ne_array);
        $message_list = Ethna_Util::escapeHtml($this->ae->getMessageList());
        $renderer->setPropByRef('errors', $message_list);
        if (isset($_SESSION)) {
            $tmp_session = Ethna_Util::escapeHtml($_SESSION);
            $renderer->setPropByRef('session', $tmp_session);
        }
        $renderer->setProp('script',
            htmlspecialchars(basename($_SERVER['SCRIPT_NAME']), ENT_QUOTES, $this->ctl->getClientEncoding()));
        $renderer->setProp('request_uri',
            isset($_SERVER['REQUEST_URI'])
            ? htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, $this->ctl->getClientEncoding())
            : '');
        $renderer->setProp('config', $this->config->get());

        return $renderer;
    }
    // }}}

    // {{{ _setDefault
    /**
     *  共通値を設定する
     *
     *  @access protected
     *  @param  object  Ethna_Renderer  レンダラオブジェクト
     */
    public function _setDefault($renderer)
    {
    }
    // }}}
}
// }}}
