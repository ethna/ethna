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
 *	アプリケーションエラークラス
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
	 *	@var	string	エラーが発生したフォーム項目名
	 */
	var $name;

	/**
	 *	@var	object	Ethna_I18N	i18nオブジェクト
	 */
	var $i18n;

	/**#@-*/


	/**
	 *	Ethna_Errorクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	int		$code		エラーコード
	 *	@param	string	$name		エラーが発生したフォーム項目(不要ならnull)
	 *	@param	string	$message	エラーメッセージ(+引数)
	 */
	function Ethna_Error($code, $name, $message)
	{
		$this->code = $code;
		$this->name = $name;
		$this->message = $message;
		$this->message_arg_list = array_slice(func_get_args(), 3);
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
		$message_arg_list = array_merge(array($this->message), $this->message_arg_list);
		$eval_statement = "return sprintf(";
		for ($i = 0; $i < count($message_arg_list); $i++) {
			if ($i > 0) {
				$eval_statement .= ", ";
			}
			$eval_statement .= "\$this->i18n->get(\$message_arg_list[$i])";
		}
		$eval_statement .= ");";
		return eval($eval_statement);
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

	/**
	 *	@var	object	Ethna_I18N	i18nオブジェクト
	 */
	var $i18n;

	/**#@-*/

	/**
	 *	Ethna_ActionErrorクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_I18N	$i18n	i18nオブジェクト
	 */
	function Ethna_ActionError(&$i18n)
	{
		$this->i18n =& $i18n;
	}

	/**
	 *	エラーオブジェクトを生成/追加する
	 *
	 *	@access	public
	 *	@param	int		$code		エラーコード
	 *	@param	string	$name		エラーの発生したフォーム項目名(不要ならnull)
	 *	@param	string	$message	エラーメッセージ(+引数)
	 */
	function add($code, $name, $message)
	{
		$message_arg_list = array_slice(func_get_args(), 3);
		$eval_statement = "\$error = new Ethna_Error(\$code, \$name, \$message";
		for ($i = 0; $i < count($message_arg_list); $i++) {
			$eval_statement .= ", \$message_arg_list[$i]";
		}
		$eval_statement .= ");";
		eval($eval_statement);
		$this->error_list[] =& $error;
	}

	/**
	 *	エラーオブジェクトを追加する
	 *
	 *	@access	public
	 *	@param	object	Ethna_Error	$error	エラーオブジェクト
	 */
	function addObject(&$error)
	{
		$this->error_list[] =& $error;
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
	 *	エラーオブジェクトの数を返す(Count()メソッドのエイリアス)
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
