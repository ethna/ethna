<?php
// vim: foldmethod=marker
/**
 *	Ethna_ActionError.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

include_once(ETHNA_BASE . '/class/Ethna_Error.php');
include_once(ETHNA_BASE . '/class/Ethna_AppError.php');

// {{{ Ethna_ActionError
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
		$app_error =& new Ethna_AppError($error->getLevel(), $error->getCode(), $name, $message, $message_arg_list, false);
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
// }}}
?>
