<?php
// vim: foldmethod=marker
/**
 *  Ethna_ViewClass.php
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

    /** @var    object  Ethna_Backend       backendオブジェクト */
    var $backend;

    /** @var    object  Ethna_Config        設定オブジェクト    */
    var $config;

    /** @var    object  Ethna_I18N          i18nオブジェクト */
    var $i18n;

    /** @var    object  Ethna_Logger    ログオブジェクト */
    var $logger;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト */
    var $action_error;

    /** @var    object  Ethna_ActionError   アクションエラーオブジェクト(省略形) */
    var $ae;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト */
    var $action_form;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト(省略形) */
    var $af;

    /** @var    array   アクションフォームオブジェクト(helper) */
    var $helper_action_form = array();

    /** @var    object  Ethna_Session       セッションオブジェクト */
    var $session;

    /** @var    string  遷移名 */
    var $forward_name;

    /** @var    string  遷移先テンプレートファイル名 */
    var $forward_path;

    /**#@-*/

    // {{{ Ethna_ViewClass
    /**
     *  Ethna_ViewClassのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   $backend    backendオブジェクト
     *  @param  string  $forward_name   ビューに関連付けられている遷移名
     *  @param  string  $forward_path   ビューに関連付けられているテンプレートファイル名
     */
    function Ethna_ViewClass(&$backend, $forward_name, $forward_path)
    {
        $c =& $backend->getController();
        $this->backend =& $backend;
        $this->config =& $this->backend->getConfig();
        $this->i18n =& $this->backend->getI18N();
        $this->logger =& $this->backend->getLogger();

        $this->action_error =& $this->backend->getActionError();
        $this->ae =& $this->action_error;

        $this->action_form =& $this->backend->getActionForm();
        $this->af =& $this->action_form;

        $this->session =& $this->backend->getSession();

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
     */
    function preforward()
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
    function forward()
    {
        $renderer =& $this->_getRenderer();
        $this->_setDefault($renderer);
        $renderer->perform($this->forward_path);
    }
    // }}}

    // {{{ addActionFormHelper
    /**
     *  helperアクションフォームオブジェクトを設定する
     *
     *  @access public
     */
    function addActionFormHelper($action)
    {
        if (isset($this->helper_action_form[$action])
            && is_object($this->helper_action_form[$action])) {
            return;
        }

        $ctl =& Ethna_Controller::getInstance();
        if ($action === $ctl->getCurrentActionName()) {
            $this->helper_action_form[$action] =& $this->af;
            return;
        }

        $form_name = $ctl->getActionFormName($action);
        if ($form_name == null) {
            $this->logger->log(LOG_WARNING,
                'action form for the action [%s] not found.', $action);
            return;
        }

        $this->helper_action_form[$action] =& new $form_name($ctl);
    }
    // }}}

    // {{{ clearActionFormHelper
    /**
     *  helperアクションフォームオブジェクトを削除する
     *
     *  @access public
     */
    function clearActionFormHelper($action)
    {
        unset($this->helper_action_form[$action]);
    }
    // }}}

    // {{{ _getHelperActionForm
    /**
     *  アクションフォームオブジェクト(helper)を取得する
     *  $action == null で $name が指定されているときは、$nameの定義を
     *  含むものを探す
     *
     *  @access protected
     *  @param  string  action  取得するアクション名
     *  @param  string  name    定義されていることを期待するフォーム名
     *  @return object  Ethna_ActionFormまたは継承オブジェクト
     */
    function &_getHelperActionForm($action = null, $name = null)
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
            $af =& $this->helper_action_form[$action];
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

    // {{{ getFormName
    /**
     *  指定されたフォーム項目に対応するフォーム名(w/ レンダリング)を取得する
     *
     *  @access public
     */
    function getFormName($name, $action, $params)
    {
        $af =& $this->_getHelperActionForm($action, $name);
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
    function getFormSubmit($name, $params)
    {
        $def = array('form_type' => FORM_TYPE_SUBMIT);
        $input = $this->_getFormInput_Submit($name, $def, $params);
        return $input;
    }
    // }}}

    // {{{ getFormInput
    /**
     *  指定されたフォーム項目に対応するフォームタグを取得する
     *
     *  @access public
     *  @todo   JavaScript対応
     */
    function getFormInput($name, $action, $params)
    {
        $af =& $this->_getHelperActionForm($action, $name);
        if ($af === null) {
            return '';
        }

        $def = $af->getDef($name);
        if ($def === null) {
            return '';
        }

        if (isset($def['form_type']) == false) {
            $def['form_type'] = FORM_TYPE_TEXT;
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
    function getFormBlock($content, $params)
    {
        $attr = array();

        // action
        if (isset($params['action'])) {
            $attr['action'] = $params['action'];
            unset($params['action']);
        } else {
            $action = basename($_SERVER['PHP_SELF']);
        }

        // method
        if (isset($params['method'])) {
            $attr['method'] = $params['method'];
            unset($params['method']);
        } else {
            $attr['method'] = 'post';
        }

        // enctype
        if (isset($params['enctype'])) {
            $attr['enctype'] = $params['enctype'];
            unset($params['enctype']);
        }

        return $this->_getFormInput_Html('form', $attr, $params, $content, false);
    }
    // }}}

    // {{{ _getSelectorOptions
    /**
     *  select, radio, checkbox の選択肢を取得する
     *  ($def, $paramsを書き換えることに注意)
     *
     *  @access protected
     */
    function _getSelectorOptions(&$af, &$def, &$params)
    {
        // $params, $def の順で調べる
        $source = null;
        if (isset($params['option'])) {
            $source = $params['option'];
            unset($params['option']);
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
        $split = preg_split('/%s*,%s*/', $source, 2, PREG_SPLIT_NO_EMPTY);
        if (count($split) == 1) {
            // アクションフォームから取得
            $method_or_property = $split[0];
            if (method_exists($af, $method_or_property)) {
                $options = $af->$method_or_property();
            } else {
                $options = $af->$method_or_property;
            }
        } else {
            // マネージャから取得
            $mgr =& $this->backend->getManager($split[0]);
            $options = $mgr->getAttrList($split[1]);
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
    function _getFormInput_Button($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "button";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Checkbox
    /**
     *  チェックボックスタグを取得する(type="check")
     *
     *  @access protected
     */
    function _getFormInput_Checkbox($name, $def, $params)
    {
        // オプションの一覧(alist)を取得
        if (isset($def['option']) === false
            || is_array($def['option']) === false) {
            return '';
        }
        $options = $def['option'];

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
            unset($params['default']);
        }
        $current_value = to_array($current_value);

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
            unset($params['separator']);
        } else {
            $separator = '';
        }

        $ret = array();
        $i = 1;
        $attr = array();
        $attr['type'] = 'checkbox';
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        foreach ($options as $key => $value) {
            $attr['value'] = $key;
            $attr['id'] = $name . '_' . $i++;
            if (in_array((string) $key, $current_value)) {
                $attr['checked'] = 'checked';
            } else {
                unset($attr['checked']);
            }

            // <input type="checkbox" />
            $input_tag = $this->_getFormInput_Html('input', $attr, $params, $value);

            // <label for="id">..</label>
            $ret[] = $this->_getFormInput_Html('label', array('id' => $attr['id']),
                                               $params, $input_tag, false);
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
    function _getFormInput_File($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "file";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        $attr['value'] = "";

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Hidden
    /**
     *  フォームタグを取得する(type="hidden")
     *
     *  @access protected
     */
    function _getFormInput_Hidden($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "hidden";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        if (isset($params['default'])) {
            $attr['value'] = $params['default'];
            unset($params['default']);
        } else if (isset($params['value'])) {
            $attr['value'] = $params['value'];
            unset($params['value']);
        }

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Password
    /**
     *  フォームタグを取得する(type="password")
     *
     *  @access protected
     */
    function _getFormInput_Password($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "password";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        if (isset($params['default'])) {
            $attr['value'] = $params['default'];
            unset($params['default']);
        } else if (isset($params['value'])) {
            $attr['value'] = $params['value'];
            unset($params['value']);
        }

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Radio
    /**
     *  ラジオボタンタグを取得する(type="radio")
     *
     *  @access protected
     */
    function _getFormInput_Radio($name, $def, $params)
    {
        // オプションの一覧(alist)を取得
        if (isset($def['option']) === false
            || is_array($def['option']) === false) {
            return '';
        }
        $options = $def['option'];

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
            unset($params['default']);
        }

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
            unset($params['separator']);
        } else {
            $separator = '';
        }

        $ret = array();
        $i = 1;
        $attr = array();
        $attr['type'] = 'radio';
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        foreach ($options as $key => $value) {
            $attr['value'] = $key;
            $attr['id'] = $name . '_' . $i++;
            if ($current_value === (string) $key) {
                $attr['checked'] = 'checked';
            } else {
                unset($attr['checked']);
            }

            // <input type="radio" />
            $input_tag = $this->_getFormInput_Html('input', $attr, $params, $value);

            // <label for="id">..</label>
            $ret[] = $this->_getFormInput_Html('label', array('id' => $attr['id']),
                                               $params, $input_tag, false);
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
    function _getFormInput_Select($name, $def, $params)
    {
        // オプションの一覧(alist)を取得
        if (isset($def['option']) === false
            || is_array($def['option']) === false) {
            return '';
        }
        $options = $def['option'];

        // default値の設定
        if (isset($params['default'])) {
            $current_value = $params['default'];
            unset($params['default']);
        }

        // タグのセパレータ
        if (isset($params['separator'])) {
            $separator = $params['separator'];
            unset($params['separator']);
        } else {
            $separator = '';
        }

        // selectタグの中身を作る
        $contents = array();
        $attr = array();
        foreach ($options as $key => $value) {
            $attr['value'] = $key;
            if ($current_value === (string) $key) {
                $attr['selected'] = 'selected';
            } else {
                unset($attr['selected']);
            }
            $contents[] = $this->_getFormInput_Html('option', $attr, $params, $value);
        }

        $attr = array('name' => $name);
        $element = $separator . implode($separator, $contents) . $separator;
        return $this->_getFormInput_Html('select', $attr, $params, $element, false);
    }
    // }}}

    // {{{ _getFormInput_Submit
    /**
     *  フォームタグを取得する(type="submit")
     *
     *  @access protected
     */
    function _getFormInput_Submit($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "submit";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        if (isset($params['value'])) {
            $attr['value'] = $params['value'];
            unset($params['value']);
        }

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Textarea
    /**
     *  フォームタグを取得する(textarea)
     *
     *  @access protected
     */
    function _getFormInput_Textarea($name, $def, $params)
    {
        $attr = array();
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        $element = '';
        if (isset($params['default'])) {
            $element = $params['default'];
            unset($params['default']);
        } else if (isset($params['value'])) {
            $element = $params['value'];
            unset($params['value']);
        }

        return $this->_getFormInput_Html("textarea", $attr, $params, $element);
    }
    // }}}

    // {{{ _getFormInput_Text
    /**
     *  フォームタグを取得する(type="text")
     *
     *  @access protected
     */
    function _getFormInput_Text($name, $def, $params)
    {
        $attr = array();
        $attr['type'] = "text";
        $attr['name'] = is_array($def['type']) ? $name .'[]' : $name;
        if (isset($params['default'])) {
            $attr['value'] = $params['default'];
            unset($params['default']);
        } else if (isset($params['value'])) {
            $attr['value'] = $params['value'];
            unset($params['value']);
        }
        if (isset($def['max']) && $def['max']) {
            $attr['maxlength'] = $def['max'];
        }

        return $this->_getFormInput_Html("input", $attr, $params);
    }
    // }}}

    // {{{ _getFormInput_Html
    /**
     *  HTMLタグを取得する
     *
     *  @access protected
     */
    function _getFormInput_Html($tag, $attr, $user_attr,
                                $element = null, $escape_element = true)
    {
        // user defs
        foreach ($user_attr as $key => $value) {
            if ($key == "type" || $key == "name"
                || preg_match('/^[a-z0-9]+$/i', $key) == 0) {
                continue;
            }
            $attr[$key] = $value;
        }

        $r = "<$tag";

        foreach ($attr as $key => $value) {
            if (is_null($value)) {
                $r .= sprintf(' %s', $key);
            } else {
                $r .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_QUOTES));
            }
        }

        if ($element === null) {
            $r .= " />";
        } else if ($escape_element) {
            $r .= sprintf('>%s</%s>', htmlspecialchars($element, ENT_QUOTES), $tag);
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
    function &_getRenderer()
    {
        $_ret_object =& $this->_getTemplateEngine();
        return $_ret_object;
    }
    // }}}

    // {{{ _getTemplateEngine
    /**
     *  レンダラオブジェクトを取得する(そのうち_getRenderer()に統合される予定)
     *
     *  @access protected
     *  @return object  Ethna_Renderer  レンダラオブジェクト
     *  @obsolete
     */
    function &_getTemplateEngine()
    {
        $c =& $this->backend->getController();
        $renderer =& $c->getRenderer();

        $form_array =& $this->af->getArray();
        $app_array =& $this->af->getAppArray();
        $app_ne_array =& $this->af->getAppNEArray();
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
            htmlspecialchars(basename($_SERVER['PHP_SELF']), ENT_QUOTES));
        $renderer->setProp('request_uri',
            htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES));
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
    function _setDefault(&$renderer)
    {
    }
    // }}}
}
// }}}
?>
