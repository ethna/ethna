<?php
/**
 *	session.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	セッションクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Session
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト(省略形)
	 */
	var $ae;

	/**
	 *	@var	string	セッション名
	 */
	var $session_name;

	/**
	 *	@var	string	セッションデータ保存ディレクトリ
	 */
	var	$session_save_dir;

	/**
	 *	@var	bool	セッション開始フラグ
	 */
	var $session_start = false;

	/**
	 *	@var	bool	匿名セッションフラグ
	 */
	var $anonymous = false;

	/**#@-*/

	/**
	 *	Ethna_Sessionクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$appid		アプリケーションID(セッション名として使用)
	 *	@param	string	$save_dir	セッションデータを保存するディレクトリ
	 *	@param	object	Ethna_ActionError	$action_error	ActionErrorオブジェクト
	 */
	function Ethna_Session($appid, $save_dir, &$action_error)
	{
		$this->session_name = "${appid}SESSID";
		$this->session_save_dir = $save_dir;
		$this->action_error =& $action_error;
		$this->ae =& $this->action_error;

		if ($this->session_save_dir != "") {
			session_save_path($this->session_save_dir);
		}

		session_name($this->session_name);
		session_cache_limiter('private, must-revalidate');

		$this->session_start = false;
		if (isset($_SERVER['REQUEST_METHOD']) == false) {
			return;
		}

		if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') == 0) {
			$http_vars =& $_POST;
		} else {
			$http_vars =& $_GET;
		}
		if (array_key_exists($this->session_name, $http_vars) && $http_vars[$this->session_name] != null) {
			$_COOKIE[$this->session_name] = $http_vars[$this->session_name];
		}

		if (!empty($_COOKIE[$this->session_name]) || session_id() != null) {
			session_start();
			$this->session_start = true;

			// check session
			if ($this->isValidSession() == false) {
				setcookie($this->session_name, "", 0, "/");
				$this->session_start = false;
			}

			// check anonymous
			if ($this->get('__anonymous__')) {
				$this->anonymous = true;
			}
		}
	}

	/**
	 *	セッションの正当性チェック
	 *
	 *	@access	public
	 *	@return	bool	true:正当なセッション false:不当なセッション
	 */
	function isValid()
	{
		if (!$this->session_start) {
			if (!empty($_COOKIE[$this->session_name]) || session_id() != null) {
				setcookie($this->session_name, "", 0, "/");
				$this->ae->Add(E_SESSION_INVALID, null, 'invalid session');
			}
			return false;
		}

		// check remote address
		if (!isset($_SESSION['REMOTE_ADDR']) || $this->_validateRemoteAddr($_SESSION['REMOTE_ADDR'], $_SERVER['REMOTE_ADDR']) == false) {
			// we do not allow this
			setcookie($this->session_name, "", 0, "/");
			session_destroy();
			$this->session_start = false;
			$this->ae->Add(E_SESSION_INVALID, null, 'invalid session');
			return false;
		}

		return true;
	}

	/**
	 *	セッションを開始する
	 *
	 *	@access	public
	 *	@param	int		$lifetime	セッション有効期間(秒単位, 0ならセッションクッキー)
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function start($lifetime = 0, $anonymous = false)
	{
		if ($this->session_start) {
			// we need this?
			$_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
			return true;
		}

		if (is_null($lifetime)) {
			ini_set('session.use_cookies', 0);
		} else {
			ini_set('session.use_cookies', 1);
		}

		session_set_cookie_params($lifetime);
		session_id(Ethna_Util::getRandom());
		session_start();
		$_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
		if ($anonymous) {
			$_SESSION['__anonymous__'] = true;
		}
		$this->session_start = true;

		return true;
	}

	/**
	 *	セッションを破棄する
	 *
	 *	@access	public
	 *	@return	bool	true:正常終了 false:エラー
	 */
	function destroy()
	{
		if (!$this->session_start) {
			return true;
		}
		
		session_destroy();
		$this->session_start = false;
		setcookie($this->session_name, "", 0, "/");

		return true;
	}

	/**
	 *	セッション値へのアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@return	mixed	取得した値(null:セッションが開始されていない)
	 */
	function get($name)
	{
		if (!$this->session_start) {
			return null;
		}

		if (!isset($_SESSION[$name])) {
			return null;
		}
		return $_SESSION[$name];
	}

	/**
	 *	セッション値へのアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@param	string	$value	値
	 *	@return	bool	true:正常終了 false:エラー(セッションが開始されていない)
	 */
	function set($name, $value)
	{
		if (!$this->session_start) {
			// no way
			return false;
		}

		$_SESSION[$name] = $value;

		return true;
	}

	/**
	 *	セッションの値を破棄する
	 *
	 *	@access	public
	 *	@param	string	$name	キー
	 *	@return	bool	true:正常終了 false:エラー(セッションが開始されていない)
	 */
	function remove($name)
	{
		if (!$this->session_start) {
			return false;
		}

		unset($_SESSION[$name]);

		return true;
	}

	/**
	 *	セッションが開始されているかどうかを返す
	 *
	 *	@access	public
	 *	@param	string	$anonymous	匿名セッションを「開始」とみなすかどうか(default: false)
	 *	@return	bool	true:開始済み false:開始されていない
	 */
	function isStart($anonymous = false)
	{
		if ($anonymous) {
			return $this->session_start;
		} else {
			if ($this->session_start && $this->isAnonymous() != true) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 *	匿名セッションかどうかを返す
	 *
	 *	@access	public
	 *	@return	bool	true:匿名セッション false:非匿名セッション/セッション開始されていない
	 */
	function isAnonymous()
	{
		return $this->anonymous;
	}

	/**
	 *	セッションに保存されたIPアドレスとアクセス元のIPアドレスが同一ネットワーク範囲かどうかを判別する(16bit mask)
	 *
	 *	@access	private
	 *	@param	string	$src_ip		セッション開始時のアクセス元IPアドレス
	 *	@param	string	$dst_ip		現在のアクセス元IPアドレス
	 *	@return	bool	true:正常終了 false:不正なIPアドレス
	 */
	function _validateRemoteAddr($src_ip, $dst_ip)
	{
		$src = ip2long($src_ip);
		$dst = ip2long($dst_ip);

		if (($src & 0xffff0000) == ($dst & 0xffff0000)) {
			return true;
		} else {
			return false;
		}
	}
}
?>
