<?php
// vim: foldmethod=marker
/**
 *	action_class.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_ActionClass
/**
 *	action実行クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_ActionClass
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
	 *	@var	object	Ethna_ActionForm	action formオブジェクト
	 */
	var $action_form;

	/**
	 *	@var	object	Ethna_ActionForm	action formオブジェクト(省略形)
	 */
	var $af;

	/**
	 *	@var	object	Ethna_Session		セッションオブジェクト
	 */
	var $session;

	/**#@-*/

	/**
	 *	Ethna_ActionClassのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	$backend	backendオブジェクト
	 */
	function Ethna_ActionClass(&$backend)
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
	}

	/**
	 *	ビジネスロジック実行前の処理(セッションチェック、フォーム値チェック等)を行う
	 *
	 *	@access	public
	 *	@return	string	Forward名(nullなら正常終了)
	 */
	function prepare()
	{
		return null;
	}

	/**
	 *	action処理
	 *
	 *	@access	public
	 *	@return	string	Forward名
	 */
	function perform()
	{
		return null;
	}

	/**
	 *	画面表示前処理
	 *
	 *	@access	public
	 */
	function preforward()
	{
	}
}
// }}}

// {{{ Ethna_List_ActionClass
/**
 *	リスト表示アクション基底クラスの実装
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_List_ActionClass extends Ethna_ActionClass
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	int		表示開始オフセット
	 */
	var	$offset = 0;

	/**
	 *	@var	int		表示件数
	 */
	var	$count = 25;

	/**
	 *	@var	array	検索対象項目一覧
	 */
	var	$search_list = array();

	/**
	 *	@var	string	表示対象クラス名
	 */
	var	$class_name = null;

	/**#@-*/

	/**
	 *	リスト表示アクションの前処理
	 *
	 *	@access	public
	 *	@return	string		Forward先(正常終了ならnull)
	 */
	function prepare()
	{
		return null;
	}

	/**
	 *	admin_stock_brand_indexアクションの実装
	 *
	 *	@access	public
	 *	@return	string	遷移名
	 */
	function perform()
	{
		return null;
	}

	/**
	 *	遷移前処理
	 *
	 *	@access	public
	 */
	function preforward()
	{
		// 表示オフセット/件数設定
		$this->offset = $this->af->get('offset');
		if ($this->offset == "") {
			$this->offset = 0;
		}
		if (intval($this->af->get('count')) > 0) {
			$this->count = intval($this->af->get('count'));
		}

		// 検索条件
		$filter = array();
		foreach ($this->search_list as $key) {
			if ($this->af->get("s_$key") != "") {
				$filter[$key] = $this->af->get("s_$key");
			}
		}

		// TODO: ソート条件
		$sort = array();

		// 表示項目一覧
		for ($i = 0; $i < 2; $i++) {
			list($total, $obj_list) = $this->um->getObjectList($this->class_name, $filter, $sort, $this->offset, $this->count);
			if (count($obj_list) == 0 && $this->offset >= $total) {
				$this->offset = 0;
				continue;
			}
			break;
		}

		$r = array();
		foreach ($obj_list as $obj) {
			$value = $obj->getNameObject();
			$value = $this->_fixNameObject($value);
			$r[] = $value;
		}
		$list_name = sprintf("%s_list", strtolower(preg_replace('/(.)([A-Z])/', '\\1_\\2', $this->class_name)));
		$this->af->setApp($list_name, $r);

		// ナビゲーション
		$this->af->setApp('nav', $this->_getNavigation($total, $obj_list));
		$this->af->setAppNE('query', $this->_getQueryParameter());

		// 検索オプション
		$this->_setQueryOption();
	}

	/**
	 *	表示項目を修正する
	 *
	 *	@access	protected
	 */
	function _fixNameObject($obj)
	{
		return $obj;
	}
	
	/**
	 *	ナビゲーション情報を取得する
	 *
	 *	@access	private
	 *	@param	int		$total		検索総件数
	 *	@param	array	$list		検索結果
	 *	@return	array	ナビゲーション情報を格納した配列
	 */
	function _getNavigation($total, &$list)
	{
		$nav = array();
		$nav['offset'] = $this->offset;
		$nav['from'] = $this->offset + 1;
		if ($total == 0) {
			$nav['from'] = 0;
		}
		$nav['to'] = $this->offset + count($list);
		$nav['total'] = $total;
		if ($this->offset > 0) {
			$prev_offset = $this->offset - $this->count;
			if ($prev_offset < 0) {
				$prev_offset = 0;
			}
			$nav['prev_offset'] = $prev_offset;
		}
		if ($this->offset + $this->count < $total) {
			$next_offset = $this->offset + count($list);
			$nav['next_offset'] = $next_offset;
		}
		$nav['direct_link_list'] = Ethna_Util::getDirectLinkList($total, $this->offset, $this->count);

		return $nav;
	}

	/**
	 *	検索項目を生成する
	 *
	 *	@access	protected
	 */
	function _setQueryOption()
	{
	}

	/**
	 *	検索内容を格納したGET引数を生成する
	 *
	 *	@access	private
	 *	@param	array	$search_list	検索対象一覧
	 *	@return	string	検索内容を格納したGET引数
	 */
	function _getQueryParameter()
	{
		$query = "";

		foreach ($this->search_list as $key) {
			$value = $this->af->get("s_$key");
			if (is_array($value)) {
				foreach ($value as $v) {
					$query .= "&s_$key" . "[]=" . urlencode($v);
				}
			} else {
				$query .= "&s_$key=" . urlencode($value);
			}
		}

		return $query;
	}
}
// }}}

// {{{ Ethna_CLI_ActionClass
/**
 *	コマンドラインaction実行クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_CLI_ActionClass extends Ethna_ActionClass
{
	/**
	 *	action処理
	 *
	 *	@access	public
	 */
	function Perform()
	{
		parent::Perform();
		$_SERVER['REMOTE_ADDR'] = "0.0.0.0";
		$_SERVER['HTTP_USER_AGENT'] = "";
	}
}
// }}}

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
