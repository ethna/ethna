<?php
// vim: foldmethod=marker
/**
 *	Ethna_ViewClass.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_ViewClass
/**
 *	viewクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ViewClass
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	Ethna_Backend		backendオブジェクト */
	var $backend;

	/**	@var	object	Ethna_Config		設定オブジェクト	*/
	var $config;

	/**	@var	object	Ethna_I18N			i18nオブジェクト */
	var $i18n;

	/**	@var	object	Ethna_ActionError	アクションエラーオブジェクト */
	var $action_error;

	/**	@var	object	Ethna_ActionError	アクションエラーオブジェクト(省略形) */
	var $ae;

	/**	@var	object	Ethna_ActionForm	アクションフォームオブジェクト */
	var $action_form;

	/**	@var	object	Ethna_ActionForm	アクションフォームオブジェクト(省略形) */
	var $af;

	/**	@var	object	Ethna_Session		セッションオブジェクト */
	var $session;

	/**	@var	string	遷移名 */
	var $forward_name;

	/**	@var	string	遷移先テンプレートファイル名 */
	var $forward_path;

	/**#@-*/

	/**
	 *	Ethna_ViewClassのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	$backend	backendオブジェクト
	 *	@param	string	$forward_name	ビューに関連付けられている遷移名
	 *	@param	string	$forward_path	ビューに関連付けられているテンプレートファイル名
	 */
	function Ethna_ViewClass(&$backend, $forward_name, $forward_path)
	{
		$c =& $backend->getController();
		$this->backend =& $backend;
		$this->config =& $this->backend->getConfig();
		$this->i18n =& $this->backend->getI18N();

		$this->action_error =& $this->backend->getActionError();
		$this->ae =& $this->action_error;

		$this->action_form =& $this->backend->getActionForm();
		$this->af =& $this->action_form;

		$this->session =& $this->backend->getSession();

		// Ethna_AppManagerオブジェクトの設定
		$manager_list = $c->getManagerList();
		foreach ($manager_list as $k => $v) {
			$this->$k = $backend->getManager($v);
		}

		$this->forward_name = $forward_name;
		$this->forward_path = $forward_path;
	}

	/**
	 *	画面表示前処理
	 *
	 *	テンプレートに設定する値でコンテキストに依存しないものは
	 *	ここで設定する(例:セレクトボックス等)
	 *
	 *	@access	public
	 */
	function preforward()
	{
	}

	/**
	 *	遷移名に対応する画面を出力する
	 *
	 *	特殊な画面を表示する場合を除いて特にオーバーライドする必要は無い
	 *	(preforward()のみオーバーライドすれば良い)
	 *
	 *	@access	public
	 */
	function forward()
	{
		$smarty =& $this->_getTemplateEngine();
		$this->_setDefault($smarty);
		$smarty->display($this->forward_path);
	}

    /**
     *  指定されたフォーム項目に対応するフォーム名(w/ レンダリング)を取得する
     *
     *  @access public
     */
    function getFormName($name, $params)
    {
    }

    /**
     *  指定されたフォーム項目に対応するフォームタグを取得する
     *
     *  @access public
     */
    function getFormInput($name, $params)
    {
    }

	/**
	 *	Smartyオブジェクトを取得する
	 *
	 *	@access	protected
	 *	@return	object	Smarty	Smartyオブジェクト
	 */
	function &_getTemplateEngine()
	{
		$c =& $this->backend->getController();
		$smarty =& $c->getTemplateEngine();

		$form_array =& $this->af->getArray();
		$app_array =& $this->af->getAppArray();
		$app_ne_array =& $this->af->getAppNEArray();
		$smarty->assign_by_ref('form', $form_array);
		$smarty->assign_by_ref('app', $app_array);
		$smarty->assign_by_ref('app_ne', $app_ne_array);
		$message_list = Ethna_Util::escapeHtml($this->ae->getMessageList());
		$smarty->assign_by_ref('errors', $message_list);
		if (isset($_SESSION)) {
			$tmp_session = Ethna_Util::escapeHtml($_SESSION);
			$smarty->assign_by_ref('session', $tmp_session);
		}
		$smarty->assign('script', basename($_SERVER['PHP_SELF']));
		$smarty->assign('request_uri', htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES));

		return $smarty;
	}

	/**
	 *	共通値を設定する
	 *
	 *	@access	protected
	 *	@param	object	Smarty	Smartyオブジェクト
	 */
	function _setDefault(&$smarty)
	{
	}
}
// }}}
?>
