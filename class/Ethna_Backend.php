<?php
// vim: foldmethod=marker
/**
 *	Ethna_Backend.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_Backend
/**
 *	バックエンド処理クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_Backend
{
	/**#@+
	 *	@access		private
	 */

	/**	@var	object	Ethna_Controller	controllerオブジェクト */
	var	$controller;

	/**	@var	object	Ethna_Controller	controllerオブジェクト($controllerの省略形) */
	var	$ctl;

	/**	@var	object	Ethna_Config		設定オブジェクト */
	var	$config;

	/**	@var	object	Ethna_I18N			i18nオブジェクト */
	var $i18n;

	/**	@var	object	Ethna_ActionError	アクションエラーオブジェクト */
	var $action_error;

	/**	@var	object	Ethna_ActionError	アクションエラーオブジェクト($action_errorの省略形) */
	var $ae;

	/**	@var	object	Ethna_ActionForm	アクションフォームオブジェクト */
	var $action_form;

	/**	@var	object	Ethna_ActionForm	アクションフォームオブジェクト($action_formの省略形) */
	var $af;

	/**	@var	object	Ethna_ActionClass	アクションクラスオブジェクト */
	var $action_form;

	/**	@var	object	Ethna_ActionClass	アクションクラスオブジェクト($action_classの省略形) */
	var $ac;

	/**	@var	object	Ethna_Session		セッションオブジェクト */
	var $session;

	/**	@var	array	Ethna_DBオブジェクトを格納した配列 */
	var $db;

	/**	@var	object	Ethna_Logger		ログオブジェクト */
	var $logger;

	/**	@var	array	マネージャオブジェクトキャッシュ */
	var $manager = array();

	/**#@-*/


	/**
	 *	Ethna_Backendクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	コントローラオブジェクト
	 */
	function Ethna_Backend(&$controller)
	{
		// オブジェクトの設定
		$this->controller =& $controller;
		$this->ctl =& $this->controller;

		$this->config =& $controller->getConfig();
		$this->i18n =& $controller->getI18N();

		$this->action_error =& $controller->getActionError();
		$this->ae =& $this->action_error;
		$this->action_form =& $controller->getActionForm();
		$this->af =& $this->action_form;
		$this->action_class = null;
		$this->ac =& $this->action_class;

		$this->session =& $this->controller->getSession();
		$this->db = array();
		$this->logger =& $this->controller->getLogger();

		// マネージャオブジェクトの生成
		$manager_list = $controller->getManagerList();
		foreach ($manager_list as $key => $value) {
			$class_name = $this->controller->getManagerClassName($value);
			$this->manager[$value] =& new $class_name($this);
		}

		foreach ($manager_list as $key => $value) {
			foreach ($manager_list as $k => $v) {
				if ($v == $value) {
					/* skip myself */
					continue;
				}
				$this->manager[$value]->$k =& $this->manager[$v];
			}
		}
	}

	/**
	 *	controllerオブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Controller	controllerオブジェクト
	 */
	function &getController()
	{
		return $this->controller;
	}

	/**
	 *	設定オブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Config		設定オブジェクト
	 */
	function &getConfig()
	{
		return $this->config;
	}

	/**
	 *	アプリケーションIDを返す
	 *
	 *	@access	public
	 *	@return	string	アプリケーションID
	 */
	function getAppId()
	{
		return $this->controller->getAppId();
	}

	/**
	 *	I18Nオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_I18N	i18nオブジェクト
	 */
	function &getI18N()
	{
		return $this->i18n;
	}

	/**
	 *	アクションエラーオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionError	アクションエラーオブジェクト
	 */
	function &getActionError()
	{
		return $this->action_error;
	}

	/**
	 *	アクションフォームオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_ActionForm	アクションフォームオブジェクト
	 */
	function &getActionForm()
	{
		return $this->action_form;
	}

	/**
	 *	実行中のアクションクラスを返す
	 *
	 *	@access	public
	 *	@return	mixed	Ethna_ActionClass:アクションクラス null:アクションクラス未定
	 */
	function &getActionClass()
	{
		return $this->action_class;
	}

	/**
	 *	ログオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Logger	ログオブジェクト
	 */
	function &getLogger()
	{
		return $this->logger;
	}

	/**
	 *	セッションオブジェクトのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_Session	セッションオブジェクト
	 */
	function &getSession()
	{
		return $this->session;
	}

	/**
	 *	マネージャオブジェクトへのアクセサ(R)
	 *
	 *	@access	public
	 *	@return	object	Ethna_AppManager	マネージャオブジェクト
	 */
	function &getManager($type)
	{
		if (isset($this->manager[$type])) {
			return $this->manager[$type];
		}
		return null;
	}

	/**
	 *	アプリケーションのベースディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	ベースディレクトリのパス名
	 */
	function getBasedir()
	{
		return $this->controller->getBasedir();
	}

	/**
	 *	アプリケーションのテンプレートディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	テンプレートディレクトリのパス名
	 */
	function getTemplatedir()
	{
		return $this->controller->getTemplatedir();
	}

	/**
	 *	アプリケーションの設定ディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	設定ディレクトリのパス名
	 */
	function getEtcdir()
	{
		return $this->controller->getDirectory('etc');
	}

	/**
	 *	アプリケーションのテンポラリディレクトリを取得する
	 *
	 *	@access	public
	 *	@return	string	テンポラリディレクトリのパス名
	 */
	function getTmpdir()
	{
		return $this->controller->getDirectory('tmp');
	}

	/**
	 *	アプリケーションのテンプレートファイル拡張子を取得する
	 *
	 *	@access	public
	 *	@return	string	テンプレートファイルの拡張子
	 */
	function getTemplateext()
	{
		return $this->controller->getExt('tpl');
	}

	/**
	 *	現在処理中のクライアント種別を取得する
	 *
	 *	@access	public
	 *	@return	int		クライアント種別
	 */
	function getClientType()
	{
		return $this->controller->getClientType();
	}

	/**
	 *	ログを出力する
	 *
	 *	@access	public
	 *	@param	int		$level		ログレベル(LOG_DEBUG, LOG_NOTICE...)
	 *	@param	string	$message	ログメッセージ(printf形式)
	 */
	function log($level, $message)
	{
		$args = func_get_args();
		if (count($args) > 2) {
			array_splice($args, 0, 2);
			$message = vsprintf($message, $args);
		}
		$this->logger->log($level, $message);
	}

	/**
	 *	バックエンド処理を実行する
	 *
	 *	@access	public
	 *	@param	string	$action_name	実行するアクションの名称
	 *	@return	mixed	(string):Forward名(nullならforwardしない) Ethna_Error:エラー
	 */
	function perform($action_name)
	{
		$forward_name = null;

		$action_class_name = $this->controller->getActionClassName($action_name);
		$this->action_class =& new $action_class_name($this);
		$this->ac =& $this->action_class;

		// アクションの実行
		$forward_name = $this->ac->authenticate();
		if ($forward_name === false) {
			return null;
		} else if ($forward_name !== null) {
			return $forward_name;
		}

		$forward_name = $this->ac->prepare();
		if ($forward_name === false) {
			return null;
		} else if ($forward_name !== null) {
			return $forward_name;
		}

		$forward_name = $this->ac->perform();

		return $forward_name;
	}

	/**
	 *	DBオブジェクトを返す
	 *
	 *	@access	public
	 *	@param	string	$type		DB種別(Ethna_Controller::dbメンバで定義)
	 *	@return	mixed	Ethna_DB:DBオブジェクト null:DSN設定なし Ethna_Error:エラー
	 */
	function &getDB($type = "")
	{
		$key =& $this->_getDB($type);
		if (Ethna::isError($key)) {
			return $key;
		}
		if (isset($this->db[$key]) && $this->db[$key] != null) {
			return $this->db[$key];
		}

		$dsn = $this->controller->getDSN($type);
		if ($dsn == "") {
			// DB接続不要
			return null;
		}
		$dsn_persistent = $this->controller->getDSN_persistent($type);

		$class_factory =& $this->controller->getClassFactory();
		$db_class_name = $class_factory->getObjectName('db');
		
		// BC: Ethna_DB -> Ethna_DB_PEAR
		if ($db_class_name == 'Ethna_DB') {
			$db_class_name = 'Ethna_DB_PEAR';
		}

		$this->db[$key] =& new $db_class_name($this->controller, $dsn, $dsn_persistent);
		$r = $this->db[$key]->connect();
		if (Ethna::isError($r)) {
			$this->db[$key] = null;
			return $r;
		}

		register_shutdown_function(array($this, 'shutdownDB'));

		return $this->db[$key];
	}

	/**
	 *	DBオブジェクト(全て)を取得する
	 *
	 *	@access	public
	 *	@return	mixed	array:Ethna_DBオブジェクトの一覧 Ethan_Error:(いずれか一つ以上の接続で)エラー
	 *	@todo	respect access controlls
	 */
	function getDBList()
	{
		$r = array();
		foreach ($this->controller->db as $key => $value) {
			$db =& $this->getDB($key);
			if (Ethna::isError($db)) {
				return $r;
			}
			$elt = array();
			$elt['db'] =& $db;
			$elt['type'] = $value;
			$elt['varname'] = "db";
			if ($key != "") {
				$elt['varname'] = sprintf("db_%s", strtolower($key));
			}
			$r[] = $elt;
		}
		return $r;
	}

	/**
	 *	DBコネクションを切断する
	 *
	 *	@access	public
	 */
	function shutdownDB()
	{
		foreach (array_keys($this->db) as $key) {
			if ($this->db[$key] != null && $this->db[$key]->isValid()) {
				$this->db[$key]->disconnect();
				unset($this->db[$key]);
			}
		}
	}

	/**
	 *	指定されたDB種別に対応する(当該DBオブジェクトを格納するための)メンバ変数名を取得する
	 *
	 *	@access	private
	 *	@param	string	$type	DB種別
	 *	@return	mixed	string:メンバ変数名 Ethna_Error:不正なDB種別
	 */
	function &_getDB($type = "")
	{
		$r = $this->controller->getDBType($type);
		if (is_null($r)) {
			return Ethna::raiseError(E_DB_INVALIDTYPE, "未定義のDB種別[%s]", $type);
		}

		if ($type == "") {
			$key = "";
		} else {
			$key = sprintf("%s", strtolower($type));
		}

		return $key;
	}
}
// }}}
?>
