<?php
/**
 *	action_form.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	フォーム入力値変換フラグ: 半角カナ→全角カナ
 */
define('CONVERT_1BYTE_KANA', 1 << 0);

/**
 *	フォーム入力値変換フラグ: 全角数字→半角数字
 */
define('CONVERT_2BYTE_NUMERIC', 1 << 1);

/**
 *	フォーム入力値変換フラグ: 全角アルファベット→半角アルファベット
 */
define('CONVERT_2BYTE_ALPHABET', 1 << 2);

/**
 *	フォーム入力値変換フラグ: 左空白削除
 */
define('CONVERT_LTRIM',	1 << 3);

/**
 *	フォーム入力値変換フラグ: 右空白削除
 */
define('CONVERT_RTRIM',	1 << 4);

/**
 *	フォーム入力値変換フラグ: 左右空白削除
 */
define('CONVERT_LRTRIM', (1 << 3) | (1 << 4));

/**
 *	フォーム入力値変換フラグ: 全角英数→半角英数/左右空白削除
 */
define('CONVERT_2BYTE', (1 << 1) | (1 << 2) | (1 << 3) | (1 << 4));


/**
 *	アクションフォームクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ActionForm
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	フォーム値定義
	 */
	var $form = array();

	/**
	 *	@var	array	フォーム値
	 */
	var $form_vars = array();

	/**
	 *	@var	array	アプリケーション設定値
	 */
	var $app_vars = array();

	/**
	 *	@var	array	アプリケーション設定値(自動エスケープなし)
	 */
	var $app_ne_vars = array();

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト(省略形)
	 */
	var $ae;

	/**
	 *	@var	object	Ethna_I18N	i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	array	フォーム定義要素
	 */
	var $def = array('name', 'required', 'max', 'min', 'regexp', 'custom', 'convert', 'form_type', 'type');

	/**#@-*/

	/**
	 *	Ethna_ActionFormクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	controllerオブジェクト
	 */
	function Ethna_ActionForm(&$controller)
	{
		$this->i18n =& $controller->getI18N();
		$this->action_error =& $controller->getActionError();
		$this->ae =& $this->action_error;

		if (isset($_SERVER['REQUEST_METHOD']) == false) {
			return;
		}

		if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
			$http_vars =& $_POST;
		} else {
			$http_vars =& $_GET;
		}

		// フォーム値設定
		foreach ($this->form as $name => $value) {
			// 省略値補正
			foreach ($this->def as $k) {
				if (isset($value[$k]) == false) {
					$this->form[$name][$k] = null;
				}
			}

			$type = to_array($value['type']);
			if ($type[0] == VAR_TYPE_FILE) {
				@$this->form_vars[$name] =& $_FILES[$name];
			} else {
				if (isset($http_vars[$name]) == false) {
					if (isset($http_vars["{$name}_x"])) {
						@$this->form_vars[$name] = $http_vars["{$name}_x"];
					} else {
						@$this->form_vars[$name] = null;
					}
				} else {
					@$this->form_vars[$name] = $http_vars[$name];
				}
			}
		}
	}

	/**
	 *	フォーム値のアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム値の名称
	 *	@return	mixed	フォーム値
	 */
	function get($name)
	{
		if (isset($this->form_vars[$name])) {
			return $this->form_vars[$name];
		}
		return null;
	}

	/**
	 *	フォーム値定義を取得する
	 *
	 *	@access	public
	 *	@return	array	フォーム値定義
	 */
	function getDef()
	{
		return $this->form;
	}

	/**
	 *	フォーム項目表示名を取得する
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム値の名称
	 *	@return	mixed	フォーム値の表示名
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
	 *	フォーム値へのアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム値の名称
	 *	@param	string	$value	設定する値
	 */
	function set($name, $value)
	{
		$this->form_vars[$name] = $value;
	}

	/**
	 *	フォーム値を配列にして返す
	 *
	 *	@access	public
	 *	@param	bool	$escape	HTMLエスケープフラグ(true:エスケープする)
	 *	@return	array	フォーム値を格納した配列
	 */
	function &getArray($escape = true)
	{
		$retval = array();

		$this->_getArray($this->form_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	アプリケーション設定値のアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@return	mixed	アプリケーション設定値
	 */
	function getApp($name)
	{
		if (isset($this->app_vars[$name]) == false) {
			return null;
		}
		return $this->app_vars[$name];
	}

	/**
	 *	アプリケーション設定値のアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@param	mixed	$value	値
	 */
	function setApp($name, $value)
	{
		$this->app_vars[$name] = $value;
	}

	/**
	 *	アプリケーション設定値を配列にして返す
	 *
	 *	@access	public
	 *	@param	boolean	$escape	HTMLエスケープフラグ(true:エスケープする)
	 *	@return	array	フォーム値を格納した配列
	 */
	function getAppArray($escape = true)
	{
		$retval = array();

		$this->_getArray($this->app_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	アプリケーション設定値(自動エスケープなし)のアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@return	mixed	アプリケーション設定値
	 */
	function getAppNE($name)
	{
		if (isset($this->app_ne_vars[$name]) == false) {
			return null;
		}
		return $this->app_ne_vars[$name];
	}

	/**
	 *	アプリケーション設定値(自動エスケープなし)のアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@param	mixed	$value	値
	 */
	function setAppNE($name, $value)
	{
		$this->app_ne_vars[$name] = $value;
	}

	/**
	 *	アプリケーション設定値(自動エスケープなし)を配列にして返す
	 *
	 *	@access	public
	 *	@param	boolean	$escape	HTMLエスケープフラグ(true:エスケープする)
	 *	@return	array	フォーム値を格納した配列
	 */
	function getAppNEArray($escape = false)
	{
		$retval = array();

		$this->_getArray($this->app_ne_vars, $retval, $escape);

		return $retval;
	}

	/**
	 *	フォームを配列にして返す(内部処理)
	 *
	 *	@access	private
	 *	@param	array	&$vars		フォーム(等)の配列
	 *	@param	array	&$retval	配列への変換結果
	 *	@param	bool	$escape		HTMLエスケープフラグ(true:エスケープする)
	 */
	function _getArray(&$vars, &$retval, $escape)
	{
		foreach (array_keys($vars) as $name) {
			if (is_array($vars[$name])) {
				$retval[$name] = array();
				$this->_getArray($vars[$name], $retval[$name], $escape);
			} else {
				$retval[$name] = $escape ? htmlspecialchars($vars[$name]) : $vars[$name];
			}
		}
	}

	/**
	 *	フォーム値検証メソッド
	 *
	 *	@access	public
	 *	@return	int		発生したエラーの数
	 */
	function validate()
	{
		foreach ($this->form as $name => $value) {
			$type = to_array($value['type']);
			if ($type[0] == VAR_TYPE_FILE) {
				// ファイル検証
				$tmp_name = to_array($this->form_vars[$name]['tmp_name']);
				$valid_keys = array();
				foreach ($tmp_name as $k => $v) {
					if (is_uploaded_file($tmp_name[$k]) == false) {
						// ファイル以外の値は無視
						continue;
					}
					$valid_keys[] = $k;
				}
				if (count($valid_keys) == 0 && $value['required']) {
					$this->ae->add(E_FORM_REQUIRED, $name, "{form}にファイルを選択して下さい");
					continue;
				} else if (count($valid_keys) == 0 && $value['required'] == false) {
					continue;
				}

				if (is_array($this->form_vars[$name]['tmp_name'])) {
					if (is_array($value['type']) == false) {
						// 単一指定のフォームに配列が渡されている
						$this->ae->add(E_FORM_WRONGTYPE_FILE, $name, "{form}にはスカラー値を入力してください");
						continue;
					}

					// ファイルデータを再構成
					$files = array();
					foreach ($valid_keys as $k) {
						$files[$k]['name'] = $this->form_vars[$name]['name'][$k];
						$files[$k]['type'] = $this->form_vars[$name]['type'][$k];
						$files[$k]['tmp_name'] = $this->form_vars[$name]['tmp_name'][$k];
						$files[$k]['size'] = $this->form_vars[$name]['size'][$k];
					}
					$this->form_vars[$name] = $files;

					// 配列の各要素に対する検証
					foreach (array_keys($this->form_vars[$name]) as $key) {
						$this->_validate($name, $this->form_vars[$name][$key], $value);
					}
				} else {
					if (is_array($value['type'])) {
						// 配列指定のフォームにスカラー値が渡されている
						$this->ae->add(E_FORM_WRONGTYPE_FILE, $name, "{form}には配列を入力してください");
						continue;
					}
					if (count($valid_keys) == 0) {
						$this->form_vars[$name] = null;
					}
					$this->_validate($name, $this->form_vars[$name], $value);
				}
			} else {
				if (is_array($this->form_vars[$name])) {
					if (is_array($value['type']) == false) {
						// スカラー型指定のフォームに配列が渡されている
						$this->ae->add(E_FORM_WRONGTYPE_SCALAR, $name, "{form}にはスカラー値を入力してください");
						continue;
					}

					// 配列の各要素に対する変換/検証
					foreach (array_keys($this->form_vars[$name]) as $key) {
						$this->form_vars[$name][$key] = $this->_convert($this->form_vars[$name][$key], $value['convert']);
						$this->_validate($name, $this->form_vars[$name][$key], $value);
					}
				} else {
					if ($this->form_vars[$name] == null && is_array($value['type']) && $value['required'] == false) {
						// 配列型で省略可のものは値自体が送信されてなくてもエラーとしない
						continue;
					} else if (is_array($value['type'])) {
						$this->ae->add(E_FORM_WRONGTYPE_ARRAY, $name, "{form}には配列を入力してください");
						continue;
					}
					$this->form_vars[$name] = $this->_convert($this->form_vars[$name], $value['convert']);
					$this->_validate($name, $this->form_vars[$name], $value);
				}
			}
		}

		if ($this->ae->count() == 0) {
			// 追加検証メソッド
			$this->_validatePlus();
		}

		return $this->ae->count();
	}

	/**
	 *	チェックメソッド(基底クラス)
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	array	チェック対象のフォーム値(エラーが無い場合はnull)
	 */
	function check($name)
	{
		if (is_null($this->form_vars[$name]) || $this->form_vars[$name] === "") {
			return null;
		}

		// Ethna_Backendの設定
		$c =& $GLOBALS['controller'];
		$this->backend =& $c->getBackend();

		return to_array($this->form_vars[$name]);
	}

	/**
	 *	チェックメソッド: 機種依存文字
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	object	Ethna_Error	エラーオブジェクト(エラーが無い場合はnull)
	 */
	function &checkVendorChar($name)
	{
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
				return $this->ad->add(E_FORM_INVALIDCHAR, $name, '{form}に機種依存文字が入力されています');
			} else {
				$i++;
			}
		}

		return null;
	}

	/**
	 *	チェックメソッド: メールアドレス
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	object	Ethna_Error	エラーオブジェクト(エラーが無い場合はnull)
	 */
	function &checkMailaddress($name)
	{
		$form_vars = $this->check($name);
		if ($form_vars == null) {
			return null;
		}
		foreach ($form_vars as $v) {
			if (Ethna_Util::checkMailaddress($v) == false) {
				return $this->ae->add(E_FORM_INVALIDCHAR, $name, '{form}を正しく入力してください');
			}
		}
		return null;
	}

	/**
	 *	チェックメソッド: URL
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	object	Ethna_Error	エラーオブジェクト(エラーが無い場合はnull)
	 */
	function &checkURL($name)
	{
		$form_vars = $this->check($name);
		if ($form_vars == null) {
			return null;
		}
		foreach ($form_vars as $v) {
			if (preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)/', $v) == 0) {
				return $this->ae->add(E_FORM_INVALIDCHAR, $name, '{form}を正しく入力してください');
			}
		}
		return null;
	}

	/**
	 *	フォーム値をhiddenタグとして返す
	 *
	 *	@access	public
	 *	@param	array	$include_list	配列が指定された場合、その配列に含まれるフォーム項目のみが対象となる
	 *	@param	array	$exclude_list	配列が指定された場合、その配列に含まれないフォーム項目のみが対象となる
	 *	@return	string	hiddenタグとして記述されたHTML
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
				$hidden_vars .= sprintf("<input type=\"hidden\" name=\"%s\" value=\"%s\">\n",
					$form_name, htmlspecialchars($v));
			}
		}
		return $hidden_vars;
	}

	/**
	 *	ユーザ定義検証メソッド(フォーム値間の連携チェック等)
	 *
	 *	@access	protected
	 */
	function _validatePlus()
	{
	}

	/**
	 *	フォーム値検証メソッド(実体)
	 *
	 *	@access	private
	 *	@param	string	$name		フォーム項目名
	 *	@param	mixed	$var		フォーム値
	 *	@param	array	$def		フォーム値定義
	 *	@param	bool	$test		エラーオブジェクト登録フラグ(true:登録しない)
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function _validate($name, $var, $def, $test = false)
	{
		$type = is_array($def['type']) ? $def['type'][0] : $def['type'];

		// required
		if ($type == VAR_TYPE_FILE) {
			if ($def['required'] && ($var == null || $var['size'] == 0)) {
				if ($test == false) {
					$this->ae->add(E_FORM_REQUIRED, $name, "{form}を入力してください");
				}
				return false;
			}
		} else {
			if ($def['required'] && strlen($var) == 0) {
				if ($test == false) {
					$this->ae->add(E_FORM_REQUIRED, $name, "{form}を入力してください");
				}
				return false;
			}
		}

		// type
		if (@strlen($var) > 0) {
			if ($type == VAR_TYPE_INT) {
				if (!preg_match('/^-?\d+$/', $var)) {
					if ($test == false) {
						$this->ae->add(E_FORM_WRONGTYPE_INT, $name, "{form}には数字(整数)を入力してください");
					}
					return false;
				}
			} else if ($type == VAR_TYPE_FLOAT) {
				if (!preg_match('/^-?\d+$/', $var) && !preg_match('/^-?\d+\.\d+$/', $var)) {
					if ($test == false) {
						$this->ae->add(E_FORM_WRONGTYPE_FLOAT, $name, "{form}には数字(小数)を入力してください");
					}
					return false;
				}
			} else if ($type == VAR_TYPE_DATETIME) {
				if (strtotime($var) == -1) {
					if ($test == false) {
						$this->ae->add(E_FORM_WRONGTYPE_DATETIME, $name, "{form}には日付を入力してください");
					}
					return false;
				}
			} else if ($type == VAR_TYPE_BOOLEAN) {
				if ($var != "1" && $var != "0") {
					if ($test == false) {
						$this->ae->add(E_FORM_WRONGTYPE_BOOLEAN, $name, "{form}には1または0のみ入力できます");
					}
					return false;
				}
			} else if ($type == VAR_TYPE_STRING) {
				// nothing to do
			}
		}

		// min
		if ($type == VAR_TYPE_INT && $var !== "") {
			if (!is_null($def['min']) && $var < $def['min']) {
				if ($test == false) {
					$this->ae->add(E_FORM_MIN_INT, $name, "{form}には%d以上の数字(整数)を入力してください", $def['min']);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FLOAT && $var !== "") {
			if (!is_null($def['min']) && $var < $def['min']) {
				if ($test == false) {
					$this->ae->add(E_FORM_MIN_FLOAT, $name, "{form}には%f以上の数字(小数)を入力してください", $def['min']);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_DATETIME && $var !== "") {
			if (!is_null($def['min'])) {
				$t_min = strtotime($def['min']);
				$t_var = strtotime($var);
				if ($t_var < $t_min) {
					if ($test == false) {
						$this->ae->add(E_FORM_MIN_DATETIME, $name, "{form}には%s以降の日付を入力してください", $def['min']);
					}
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FILE) {
			if (!is_null($def['min'])) {
				$st = @stat($var['tmp_name']);
				if ($st[7] < $def['min'] * 1024) {
					if ($test == false) {
						$this->ae->add(E_FORM_MIN_FILE, $name, "{form}には%dKB以上のファイルを指定してください", $def['min']);
					}
					return false;
				}
			}
		} else {
			if (!is_null($def['min']) && strlen($var) < $def['min'] && $var !== "") {
				if ($test == false) {
					$this->ae->add(E_FORM_MIN_STRING, $name, "{form}には%d文字以上入力してください", $def['min']);
				}
				return false;
			}
		}

		// max
		if ($type == VAR_TYPE_INT && $var !== "") {
			if (!is_null($def['max']) && $var > $def['max']) {
				if ($test == false) {
					$this->ae->add(E_FORM_MAX_INT, $name, "{form}には%d以下の数字(整数)を入力してください", $def['max']);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FLOAT && $var !== "") {
			if (!is_null($def['max']) && $var > $def['max']) {
				if ($test == false) {
					$this->ae->add(E_FORM_MAX_FLOAT, $name, "{form}には%d以下の数字(小数)を入力してください", $def['max']);
				}
				return false;
			}
		} else if ($type == VAR_TYPE_DATETIME && $var !== "") {
			if (!is_null($def['max'])) {
				$t_min = strtotime($def['max']);
				$t_var = strtotime($var);
				if ($t_var > $t_min) {
					if ($test == false) {
						$this->ae->add(E_FORM_MAX_DATETIME, $name, "{form}には%s以前の日付を入力してください", $def['max']);
					}
				}
				return false;
			}
		} else if ($type == VAR_TYPE_FILE) {
			if (!is_null($def['max'])) {
				$st = @stat($var['tmp_name']);
				if ($st[7] > $def['max'] * 1024) {
					if ($test == false) {
						$this->ae->add(E_FORM_MAX_FILE, $name, "{form}には%dKBまでのファイルを指定してください", $def['max']);
					}
					return false;
				}
			}
		} else {
			if (!is_null($def['max']) && strlen($var) > $def['max'] && $var !== "") {
				if ($test == false) {
					$this->ae->add(E_FORM_MAX_STRING, $name, "{form}は%d文字以下で入力してください", $name, $def['max']);
				}
				return false;
			}
		}

		// regexp
		if ($def['regexp'] != null && strlen($var) > 0 && preg_match($def['regexp'], $var) == 0) {
			if ($test == false) {
				$this->ae->add(E_FORM_REGEXP, $name, "{form}を正しく入力してください");
			}
			return false;
		}

		// custom
		if ($def['custom'] != null) {
			$error =& $this->{$def['custom']}($name);
			if ($error != null) {
				if ($test == false) {
					$this->ae->addObject($error);
				}
				return false1;
			}
		}

		return true;
	}

	/**
	 *	フラグに従いフォーム値を変換する
	 *
	 *	@access	private
	 *	@param	mixed	$value	フォーム値
	 *	@param	int		$flag	変換フラグ
	 *	@return	mixed	変換結果
	 */
	function _convert($value, $flag)
	{
		$flag = intval($flag);

		$key = "";
		if ($flag & CONVERT_LTRIM) {
			$value = ltrim($value);
		}
		if ($flag & CONVERT_RTRIM) {
			$value = rtrim($value);
		}
		if ($flag & CONVERT_1BYTE_KANA) {
			$key .= "K";
		}
		if ($flag & CONVERT_2BYTE_NUMERIC) {
			$key .= "n";
		}
		if ($flag & CONVERT_2BYTE_ALPHABET) {
			$key .= "r";
		}
		if ($key == "") {
			return $value;
		}

		return mb_convert_kana($value, $key);
	}
}

/**
 *	SOAPフォームクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_SOAP_ActionForm extends Ethna_ActionForm
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	引数定義
	 */
	var $arg = array();

	/**
	 *	@var	array	戻り値定義
	 */
	var $retval = array();

	/**#@-*/

	/**
	 *	Ethna_SOAP_ActionFormクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_ActionError	$action_error	action errorオブジェクト
	 */
	function Ethna_SOAP_ActionForm(&$action_error)
	{
		$this->form =& $this->arg;

		parent::Ethna_ActionForm($action_error);
	}
}
?>
