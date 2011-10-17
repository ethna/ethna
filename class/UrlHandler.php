<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *  UrlHandler.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  URLハンドラクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_UrlHandler
{
    /** @var    array   アクションマッピング */
    protected $action_map = array(
        /*
         * 'user'   => array(
         *  'user_login' => array(
         *      'path'          => 'login',
         *      'path_regexp'   => false,
         *      'path_ext'      => false,
         *      'option'        => array(),
         *  ),
         * ),
         */
    );

    /**
     *  Ethna_UrlHandlerクラスのコンストラクタ
     *
     *  @access public
     */
    public function __construct()
    {
    }

    /**
     *  Ethna_UrlHandlerクラスのインスタンスを取得する
     *
     *  $name がクラス名 ('_'を含む) の場合はそのクラスを、
     *  そうでないときはプラグイン名とみなしてインスタンスを返す
     *
     *  @access public
     */
    public static function getInstance($name = null)
    {
        static $instance = array();
        if ($name === null) {
            $name = __CLASS__;
        }
        if (isset($instance[$name])) {
            return $instance[$name];
        }

        if (strpos($name, '_') !== false) {
            $instance[$name] = new $name();
        } else {
            // get instance with plugin
            $ctl = Ethna_Controller::getInstance();
            $plugin = $ctl->getPlugin();
            $instance[$name] = $plugin->getPlugin('Urlhandler', $name);
        }

        return $instance[$name];
    }

    /**
     *  アクションをユーザリクエストに変換する
     *
     *  @access public
     */
    public function actionToRequest($action, $param)
    {
        $url_handler = null;
        $action_value = null;
        foreach ($this->action_map as $key => $value) {
            if (isset($value[$action])) {
                $url_handler = $key;
                $action_value = $value[$action];
                break;
            }
        }
        if (is_null($url_handler)) {
            return null;
        }

        // url_handler specific path
        $method = sprintf("_getPath_%s", ucfirst($url_handler));
        if (method_exists($this, $method) === false) {
            return null;
        }
        list($path, $path_key) = $this->$method($action, $param);
        if ($path == "") {
            return null;
        }

        // append action path
        if ($action_value['path']) {
            $path .= "/" . $action_value['path'];
        }

        // path_ext candidate list
        if (is_array($action_value['path_regexp'])) {
            // try most matcher
            $tmp = array_map('count', $action_value['path_ext']);
            arsort($tmp);
            $path_ext_list_indices = array_keys($tmp);
            $path_ext_list = $action_value['path_ext'];
        } else {
            $path_ext_list_indices = array(0);
            $path_ext_list = array(0 => $action_value['path_ext']);
        }

        // fix path_ext to use
        foreach ($path_ext_list_indices as $index) {
            if (is_array($path_ext_list[$index]) === false) {
                // no parameters needed.
                $path_ext = $path_ext_list[$index];
                break;
            }
            $path_ext_match = true;
            foreach ($path_ext_list[$index] as $key => $value) {
                if (isset($param[$key]) === false) {
                    $path_ext_match = false;
                    break;
                }
            }
            if ($path_ext_match) {
                $path_ext = $path_ext_list[$index];
                break;
            }
        }
        if (isset($path_ext) === false) {
            return null;
        }

        // append extra parameters to path.
        if (is_array($path_ext)) {
            foreach ($path_ext as $key => $value) {
                $path_key[] = $key;
                $ext_param = $param[$key];

                // output filter
                if (isset($value['output_filter']) && $value['output_filter'] != "") {
                    $method = $value['output_filter'];
                    if (method_exists($this, $method)) {
                        $ext_param = $this->$method($ext_param);
                    }
                }

                // remove form (pre|suf)fix
                if (isset($value['form_prefix']) && $value['form_prefix'] != "") {
                    $s = $value['form_prefix'];
                    if (substr($ext_param, 0, strlen($s)) == $s) {
                        $ext_param = substr($ext_param, strlen($s));
                    }
                }
                if (isset($value['form_suffix']) && $value['form_suffix'] != "") {
                    $s = $value['form_suffix'];
                    if (substr($ext_param, -strlen($s)) == $s) {
                        $ext_param = substr($ext_param, 0, -strlen($s));
                    }
                }

                // rawurlencode (url (pre|suf)fixes need not to be encoded.)
                $ext_param = rawurlencode($ext_param);

                // add url (pre|suf)fix
                if (isset($value['url_prefix']) && $value['url_prefix'] != "") {
                    $ext_param = $value['url_prefix'] . $ext_param;
                }
                if (isset($value['url_suffix']) && $value['url_suffix'] != "") {
                    $ext_param = $ext_param . $value['url_suffix'];
                }

                $path .= '/' . $ext_param;
            }
        }

        list($path, $is_slash) = $this->_normalizePath($path);
        return array($path, $path_key);
    }

    /**
     *  ユーザリクエストをアクションに変換する
     *
     *  @access public
     */
    public function requestToAction($http_vars)
    {
        if (isset($http_vars['__url_handler__']) == false
            || isset($this->action_map[$http_vars['__url_handler__']]) == false) {
            return array();
        }

        $url_handler = $http_vars['__url_handler__'];
        $action_map = $this->action_map[$url_handler];

        // parameter fix
        $method = sprintf("_normalizeRequest_%s", ucfirst($url_handler));
        if (method_exists($this, $method)) {
            $http_vars = $this->$method($http_vars);
        }

        // normalize
        if (isset($http_vars['__url_info__'])) {
            $path = $http_vars['__url_info__'];
        } else {
            $path = "";
        }
        list($path, $is_slash) = $this->_normalizePath($path);

        // match
        $action = null;
        $action_value = null;
        $action_match = null;
        $action_regexp_index = null;
        foreach ($action_map as $key => $value) {
            if (isset($value['path'])) {
                $match_length = strlen($value['path']);

                // check necessary match
                if (strncmp($path, $value['path'], $match_length) != 0) {
                    continue;
                }

                // try exact match
                if ($path == $value['path']) {
                    $action = $key;
                    break;
                }

                // continue in case w/ incomplete match
                if ($path != "" && $match_length > 0 && $path{$match_length} != "/") {
                    continue;
                }
                if ($is_slash && $path{strlen($path)-1} == "/") {
                    continue;
                }
            }

            if (isset($value['path_regexp']) && $value['path_regexp']) {
                if (is_array($value['path_regexp'])) {
                    foreach ($value['path_regexp'] as $index => $regexp) {
                        if (preg_match($regexp, $path, $tmp)) {
                            $action = $key;
                            $action_match = $tmp;
                            $action_regexp_index = $index;
                            break;
                        }
                    }
                } else {
                    if (preg_match($value['path_regexp'], $path, $tmp)) {
                        $action = $key;
                        $action_match = $tmp;
                        break;
                    }
                }
            }

            // else, continue
        }
        if (is_null($action)) {
            return array();
        }
        $action_value = $action_map[$action];

        // build parameters
        $http_vars = $this->buildActionParameter($http_vars, $action);

        // extra parameters
        $path_ext = is_null($action_regexp_index)
                    ? $action_value['path_ext']
                    : $action_value['path_ext'][$action_regexp_index];
        if (is_array($path_ext) && is_array($action_match)) {
            $n = 1;
            foreach ($path_ext as $key => $value) {
                if (isset($action_match[$n]) == false) {
                    break;
                }

                // remove url (pre|suf)fix
                if (isset($value['url_prefix']) && $value['url_prefix'] != "") {
                    $s = $value['url_prefix'];
                    if (substr($action_match[$n], 0, strlen($s)) == $s) {
                        $action_match[$n] = substr($action_match[$n], strlen($s));
                    }
                }
                if (isset($value['url_suffix']) && $value['url_suffix'] != "") {
                    $s = $value['url_suffix'];
                    if (substr($action_match[$n], -strlen($s)) == $s) {
                        $action_match[$n] = substr($action_match[$n], 0, -strlen($s));
                    }
                }

                // add form (pre|suf)fix
                if (isset($value['form_prefix']) && $value['form_prefix'] != "") {
                    $action_match[$n] = $value['form_prefix'] . $action_match[$n];
                }
                if (isset($value['form_suffix']) && $value['form_suffix'] != "") {
                    $action_match[$n] = $action_match[$n] . $value['form_suffix'];
                }

                // input filter
                if (isset($value['input_filter']) && $value['input_filter'] != "") {
                    $method = $value['input_filter'];
                    if (method_exists($this, $method)) {
                        $action_match[$n] = $this->$method($action_match[$n]);
                    }
                }

                $http_vars[$key] = $action_match[$n];
                $n++;
            }
        }

        return $http_vars;
    }

    /**
     *  ゲートウェイパスを正規化する
     *
     *  @access private
     */
    protected function _normalizePath($path)
    {
        if ($path == "") {
            return array($path, false);
        }

        $is_slash = false;
        $path = preg_replace('|/+|', '/', $path);

        if ($path{0} == '/') {
            $path = substr($path, 1);
        }
        if ($path{strlen($path)-1} == '/') {
            $path = substr($path, 0, strlen($path)-1);
            $is_slash = true;
        }

        return array($path, $is_slash);
    }

    /**
     *  アクションをリクエストパラメータに変換する
     *
     *  @access public
     */
    public function buildActionParameter($http_vars, $action)
    {
        if ($action == "") {
            return $http_vars;
        }
        $key = sprintf('action_%s', $action);
        $http_vars[$key] = 'true';
        return $http_vars;
    }

    /**
     *  パラメータをURLに変換する
     *
     *  @access public
     */
    public function buildQueryParameter($query)
    {
        $param = '';

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (is_numeric($k)) {
                        $k = '';
                    }
                    $param .= sprintf('%s=%s&',
                                      urlencode(sprintf('%s[%s]', $key, $k)),
                                      urlencode($v));
                }
            } else if (is_null($value) == false) {
                $param .= sprintf('%s=%s&', urlencode($key), urlencode($value));
            }
        }

        return substr($param, 0, -1);
    }

    // {{{ ゲートウェイリクエスト正規化
    // }}}

    // {{{ ゲートウェイパス生成
    // }}}

    // {{{ フィルタ
    // }}}
}

