<?php
/**
 *	Ethna_UrlHandler.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@package    Ethna
 *	@version    $Id$
 */

/**
 *	アクションゲートウェイクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	GREE
 */
class Ethna_UrlHandler
{
	/**	@var	array	アクションマッピング */
	var	$action_map = array(
        /*
		 * 'user'	=> array(
		 *	'user_login' => array(
		 *		'path'			=> 'login',
		 *		'path_regexp'	=> false,
		 *		'path_ext'		=> false,
		 *		'option'		=> array(),
		 *	),
		 * ),
         */
	);

	/**
	 *	Ethna_UrlHandlerクラスのコンストラクタ
	 *
	 *	@access	public
	 */
	function Ethna_UrlHandler()
	{
	}

	/**
	 *	Ethna_UrlHandlerクラスのインスタンスを取得する
	 *
	 *	@access	public
	 */
	function &getInstance($class_name = null)
	{
		static $instance = array();

        if (is_null($class_name)) {
            $class_name = __CLASS__;
        }

		if (isset($instance[$class_name]) && is_object($instance[$class_name])) {
			return $instance[$class_name];
		}

		$instance[$class_name] =& new $class_name();

		return $instance[$class_name];
	}

	/**
	 *	アクションをユーザリクエストに変換する
	 *
	 *	@access	public
	 */
	function actionToRequest($action, $param)
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
		if (method_exists($this, $method)) {
			list($path, $path_key) = $this->$method($action, $param);
			if ($path == "") {
				return null;
			}
		} else {
			return null;
		}

		// append action path
		if ($action_value['path']) {
			$path .= "/" . $action_value['path'];
		}

		// append path_ext key
		$path_ext_list = is_array($action_value['path_regexp']) ? $action_value['path_ext'] : array($action_value['path_ext']);
		foreach ($path_ext_list as $index => $path_ext) {
			$path_ext_match = false;
            if (is_array($path_ext) == false) {
                continue;
            }
			foreach ($path_ext as $ext => $ext_value) {
				if (isset($param[$ext]) == false) {
					break;
				}

				$path_ext_match = true;
				$path_key[] = $ext;

				$ext_param = $param[$ext];
				if (isset($ext_value['output_filter']) && $ext_value['output_filter'] != "") {
					$method = $ext_value['output_filter'];
					if (method_exists($this, $method)) {
						$ext_param = $this->$method($ext_param);
					}
				}

				// remove form (pre|suf)fix, add url (pre|suf)fix
				if (isset($ext_value['form_prefix']) && $ext_value['form_prefix'] != "") {
					$s = $ext_value['form_prefix'];
					if (substr($ext_param, 0, strlen($s)) == $s) {
						$ext_param = substr($ext_param, strlen($s));
					}
				}
				if (isset($ext_value['form_suffix']) && $ext_value['form_suffix'] != "") {
					$s = $ext_value['form_suffix'];
					if (substr($ext_param, -strlen($s)) == $s) {
						$ext_param = substr($ext_param, 0, -strlen($s));
					}
				}

				$ext_param_prefix = $ext_param_suffix = "";
				if (isset($ext_value['url_prefix']) && $ext_value['url_prefix'] != "") {
					$ext_param_prefix = $ext_value['url_prefix'];
				}
				if (isset($ext_value['url_suffix']) && $ext_value['url_suffix'] != "") {
					$ext_param_suffix = $ext_value['url_suffix'];
				}

				$path .= "/" . $ext_param_prefix . urlencode($ext_param) . $ext_param_suffix;
			}
			if ($path_ext_match) {
				break;
			}
		}

		return array($path, $path_key);
	}

	/**
	 *	ユーザリクエストをアクションに変換する
	 *
	 *	@access	public
	 */
	function requestToAction($http_vars)
	{
		if (isset($http_vars['__url_handler__']) == false ||
			isset($this->action_map[$http_vars['__url_handler__']]) == false) {
			return array();
		}

		$url_handler = $http_vars['__url_handler__'];

		// parameter fix
		$method = sprintf("_normalizeRequest_%s", ucfirst($url_handler));
		if (method_exists($this, $method)) {
			$http_vars = $this->$method($http_vars);
		}

		$action_map = $this->action_map[$url_handler];

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
			$match_length = strlen($value['path']);
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

			// try regexp
			if ($value['path_regexp']) {
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
		}
		if (is_null($action)) {
			return array();
		}
		$action_value = $action_map[$action];

		// build parameters
        $http_vars = $this->buildActionParameter($http_vars, $action);

		// extra parameters
		$path_ext = is_null($action_regexp_index) ? $action_value['path_ext'] : $action_value['path_ext'][$action_regexp_index];
		if (is_array($path_ext) && is_array($action_match)) {
			$n = 1;
			foreach ($path_ext as $key => $value) {
				if (isset($action_match[$n]) == false) {
					break;
				}

				// remove url (pre|suf)fix, add form (pre|suf)fix
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

				if (isset($value['form_prefix']) && $value['form_prefix'] != "") {
					$action_match[$n] = $value['form_prefix'] . $action_match[$n];
				}
				if (isset($value['form_suffix']) && $value['form_suffix'] != "") {
					$action_match[$n] = $action_match[$n] . $value['form_suffix'];
				}

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
	 *	ゲートウェイパスを正規化する
	 *
	 *	@access	private
	 */
	function _normalizePath($path)
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
    function buildActionParameter($http_vars, $action)
    {
        if ($action == "") {
            return $http_vars;
        }
        $key = sprintf('action_%s', $action);
        $http_vars[$key] = true;
        return $http_vars;
    }

    /**
     *  パラメータをURLに変換する
     *
     *  @access public
     */
    function buildQueryParameter($query)
    {
        $param = '';

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (is_numeric($k)) {
                        $k = '';
                    }
                    $param .= sprintf('%s=%s&', urlencode(sprintf('%s[%s]', $key, $k)), urlencode($v));
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

// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
?>
