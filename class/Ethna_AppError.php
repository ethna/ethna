<?php
// vim: foldmethod=marker
/**
 *	Ethna_AppError.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_AppError
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
// }}}
?>
