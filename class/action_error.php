<?php
/**
 *	action_error.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	エラークラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Error
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	int		エラーレベル
	 */
	var $level;

	/**
	 *	@var	int		エラーコード
	 */
	var $code;

	/**
	 *	@var	string	エラーメッセージ
	 */
	var $message;

	/**
	 *	@var	array	エラーメッセージ引数
	 */
	var $message_arg_list;

	/**
	 *	@var	array	ユーザ定義追加情報
	 */
	var $info;

	/**
	 *	@var	object	Ethna_I18N	i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	object	Ethna_Logger	loggerオブジェクト
	 */
	var $logger;

	/**#@-*/

	/**
	 *	Ethna_Errorクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	int		$level				エラーレベル
	 *	@param	int		$code				エラーコード
	 *	@param	string	$message			エラーメッセージ(+引数)
	 *	@param	array	$message_arg_list	エラーメッセージ引数
	 */
	function Ethna_Error($level, $code, $message, $message_arg_list = array())
	{
		$this->controller =& $GLOBALS['controller'];
		$this->i18n =& $this->controller->getI18N();
		$this->logger =& $this->controller->getLogger();

		$this->level = $level;
		$this->code = $code;
		$this->message = $message;
		$this->message_arg_list = $message_arg_list;
		$this->info = array();

		// ログ
		list ($log_level, $dummy) = Ethna_Logger::errorLevelToLogLevel($level);
		$message = $this->getMessage();
		$this->logger->log($log_level, sprintf("[APP(%d)] %s", $code, $message == null ? "(no message)" : $message));
	}

	/**
	 *	levelへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	int		エラーコード
	 */
	function getLevel()
	{
		return $this->level;
	}

	/**
	 *	codeへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	int		エラーコード
	 */
	function getCode()
	{
		return $this->code;
	}

	/**
	 *	messageへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	array	エラーメッセージ
	 */
	function getMessage()
	{
		$tmp_message = $this->i18n->get($this->message);
		$tmp_message_arg_list = array();
		for ($i = 0; $i < count($this->message_arg_list); $i++) {
			$tmp_message_arg_list[] = $this->i18n->get($this->message_arg_list[$i]);
		}
		return vsprintf($tmp_message, $tmp_message_arg_list);
	}

	/**
	 *	message引数へのアクセサ(R)
	 *
	 *	@access	public
	 *	@param	int		message引数インデックス
	 *	@return	mixed	message引数
	 */
	function getInfo($n)
	{
		if (isset($this->message_arg_list[$n])) {
			return $this->message_arg_list[$n];
		} else {
			return null;
		}
	}

	/**
	 *	messageとその引数を加工せずに返す
	 *
	 *	@access	public
	 *	@return	array	エラーメッセージ, エラーメッセージ引数
	 */
	function getMessage_Raw()
	{
		return array($this->message, $this->message_arg_list);
	}

	/**
	 *	ユーザ定義情報へのアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$key	ユーザ定義情報キー
	 *	@return	mixed	$keyで指定されたユーザ定義情報
	 */
	function get($key)
	{
		if (isset($this->info[$key])) {
			return $this->info[$key];
		} else {
			return null;
		}
	}

	/**
	 *	ユーザ定義情報へのアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$key	ユーザ定義情報キー
	 *	@param	mixed	$value	ユーザ定義情報値
	 */
	function set($key, $value)
	{
		$this->info[$key] = $value;
	}
}

/**
 *	アプリケーションエラークラス
 *
 *	このクラスはEthna_ActoinErrorクラスでのみ利用される(開発者が生成するのは
 *	Ethna_Errorクラスのみ)
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_AppError extends Ethna_Error
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	string	エラーが発生したフォーム項目名
	 */
	var $name;

	/**
	 *	@var	object	Ethna_ActionForm	action formオブジェクト
	 */
	var $action_form;

	/**#@-*/

	/**
	 *	Ethna_AppErrorクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	int		$level		エラーレベル
	 *	@param	int		$code		エラーコード
	 *	@param	string	$name		エラーが発生したフォーム項目(不要ならnull)
	 *	@param	string	$message	エラーメッセージ
	 *	@param	string	$message	エラーメッセージ引数
	 *	@param	bool	$logging	エラーログ出力フラグ
	 */
	function Ethna_AppError($level, $code, $name, $message, $message_arg_list, $logging = true)
	{
		$this->controller =& $GLOBALS['controller'];
		$this->i18n =& $this->controller->getI18N();
		$this->logger =& $this->controller->getLogger();
		$this->action_form =& $this->controller->getActionForm();

		$this->level = $level;
		$this->code = $code;
		$this->name = $name;
		$this->message = $message;
		$this->message_arg_list = $message_arg_list;

		// ログ
		if ($logging) {
			list ($log_level, $dummy) = Ethna_Logger::errorLevelToLogLevel($level);
			$message = $this->getMessage();
			$this->logger->log($log_level, sprintf("[USER(%d)-%s] %s", $code, $name == null ? "(no name)" : $name, $message == null ? "(no message)" : $message));
		}
	}

	/**
	 *	nameへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	string	エラーが発生したフォーム項目名
	 */
	function getName()
	{
		return $this->name;
	}

	/**
	 *	messageへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	array	エラーメッセージ(とその引数)
	 */
	function getMessage()
	{
		$message = parent::getMessage();

		// マクロ処理
		$form_name = $this->action_form->getName($this->getName());
		$message = str_replace("{form}", $form_name, $message);

		return $message;
	}
}

/**
 *	アプリケーションエラー管理クラス
 *
 *	@access		public
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@package	Ethna
 */
class Ethna_ActionError
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	array	Ethna_Errorエラーオブジェクトの一覧
	 */
	var $error_list = array();

	/**#@-*/

	/**
	 *	Ethna_ActionErrorクラスのコンストラクタ
	 *
	 *	@access	public
	 */
	function Ethna_ActionError()
	{
	}

	/**
	 *	Ethna_AppErrorオブジェクトを生成/追加する
	 *
	 *	@access	public
	 *	@param	int		$code		エラーコード
	 *	@param	string	$name		エラーの発生したフォーム項目名(不要ならnull)
	 *	@param	string	$message	エラーメッセージ(+引数)
	 */
	function add($code, $name, $message)
	{
		$message_arg_list = array_slice(func_get_args(), 3);
		$app_error =& new Ethna_AppError(E_USER_NOTICE, $code, $name, $message, $message_arg_list);
		$this->error_list[] =& $app_error;
	}

	/**
	 *	Ethna_Errorオブジェクトを追加する
	 *
	 *	@access	public
	 *	@param	object	Ethna_Error	$error	エラーオブジェクト
	 *	@param	string				$name	エラーに対応するフォーム項目名(不要ならnull)
	 */
	function addObject(&$error, $name = null)
	{
		list($message, $message_arg_list) = $error->getMessage_Raw();
		$app_error =& new Ethna_AppError($error->getLevel(), $error->getCode(), $message, $message_arg_list, false);
		$this->error_list[] =& $app_error;
	}

	/**
	 *	エラーオブジェクトの数を返す
	 *
	 *	@access	public
	 *	@return	int		エラーオブジェクトの数
	 */
	function count()
	{
		return count($this->error_list);
	}

	/**
	 *	エラーオブジェクトの数を返す(count()メソッドのエイリアス)
	 *
	 *	@access	public
	 *	@return	int		エラーオブジェクトの数
	 */
	function length()
	{
		return count($this->error_list);
	}

	/**
	 *	登録されたエラーオブジェクトを全て削除する
	 *
	 *	@access	public
	 */
	function clear()
	{
		$this->error_list = array();
	}

	/**
	 *	指定されたフォーム項目にエラーが発生しているかどうかを返す
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	bool	true:エラーが発生している false:エラーが発生していない
	 */
	function isError($name)
	{
		foreach ($this->error_list as $error) {
			if (strcasecmp($error->getName(), $name) == 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 *	指定されたフォーム項目に対応するエラーメッセージを返す
	 *
	 *	@access	public
	 *	@param	string	$name	フォーム項目名
	 *	@return	string	エラーメッセージ(エラーが無い場合はnull)
	 */
	function getMessage($name)
	{
		foreach ($this->error_list as $error) {
			if (strcasecmp($error->getName(), $name) == 0) {
				return $error->getMessage();
			}
		}
		return null;
	}

	/**
	 *	エラーオブジェクトを配列にして返す
	 *
	 *	@access	public
	 *	@return	array	エラーオブジェクトの配列
	 */
	function getErrorList()
	{
		return $this->error_list;
	}

	/**
	 *	エラーメッセージを配列にして返す
	 *
	 *	@access	public
	 *	@return	array	エラーメッセージの配列
	 */
	function getMessageList()
	{
		$message_list = array();

		foreach ($this->error_list as $error) {
			$message_list[] = $error->getMessage();
		}
		return $message_list;
	}
}
?>
