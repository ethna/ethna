<?php
// vim: foldmethod=marker
/**
 *	Ethna_Error.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Error
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
// }}}
?>
