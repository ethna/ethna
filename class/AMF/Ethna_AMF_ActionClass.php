<?php
// vim: foldmethod=marker
/**
 *	Ethna_AMF_ActionClass.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_AMF_ActionClass
/**
 *	AMF(Flash Remoting)action実行クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_AMF_ActionClass
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	Ethna_Backend		backendオブジェクト
	 */
	var $backend;

	/**
	 *	@var	object	Ethna_Config		設定オブジェクト	
	 */
	var $config;

	/**
	 *	@var	object	Ethna_I18N			i18nオブジェクト
	 */
	var $i18n;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var $action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト(省略形)
	 */
	var $ae;

	/**
	 *	@var	object	Ethna_Session		セッションオブジェクト
	 */
	var $session;

	/**
	 *	@var	array	メソッド定義
	 */
	var $method;

	/**#@-*/

	/**
	 *	Ethna_AMF_ActionClassのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	$backend	backendオブジェクト
	 */
	function Ethna_AMF_ActionClass()
	{
		$c =& $GLOBALS['controller'];
		$this->backend =& $c->getBackend();
		$this->config =& $this->backend->getConfig();
		$this->i18n =& $this->backend->getI18N();

		$this->action_error =& $this->backend->getActionError();
		$this->ae =& $this->action_error;

		$this->session =& $this->backend->getSession();
	}

	/**
	 *	Credentialヘッダに基づいてセッション処理を行う
	 *
	 *	@access	private
	 *	@param	string	$user_id	CredentialヘッダのユーザID
	 *	@param	string	$password	Credentialヘッダのパスワード(クライアントはここにセッションIDを設定する)
	 */
	function _authenticate($user_id, $password)
	{
		if ($this->session != null) {
			// already authenticated
			return;
		}

		$c =& $this->backend->getController();

		session_id($password);
		$this->session =& new Session($c->getAppId(), $this->backend->getTmpdir(), $this->ae);
	}
}
// }}}
?>
