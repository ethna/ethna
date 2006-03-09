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
	var $action_class;

	/**	@var	object	Ethna_ActionClass	アクションクラスオブジェクト($action_classの省略形) */
	var $ac;

	/**	@var	object	Ethna_Session		セッションオブジェクト */
	var $session;

	/**	@var	array	Ethna_DBオブジェクトを格納した配列 */
	var $db_list;

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
		$this->db_list = array();
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
	 *	@param	string	$db_key DBキー
	 *	@return	mixed	Ethna_DB:DBオブジェクト null:DSN設定なし Ethna_Error:エラー
	 */
	function &getDB($db_key = "")
	{
		$db_varname =& $this->_getDBVarname($db_key);
		if (Ethna::isError($db_varname)) {
			return $db_varname;
		}
		if (isset($this->db_list[$db_varname]) && $this->db_list[$db_varname] != null) {
			return $this->db_list[$db_varname];
		}

		$dsn = $this->controller->getDSN($db_key);
		if ($dsn == "") {
			// DB接続不要
			return null;
		}
		$dsn_persistent = $this->controller->getDSN_persistent($db_key);

		$class_factory =& $this->controller->getClassFactory();
		$db_class_name = $class_factory->getObjectName('db');
		
		// BC: Ethna_DB -> Ethna_DB_PEAR
		if ($db_class_name == 'Ethna_DB') {
			$db_class_name = 'Ethna_DB_PEAR';
		}

		$this->db_list[$db_varname] =& new $db_class_name($this->controller, $dsn, $dsn_persistent);
		$r = $this->db_list[$db_varname]->connect();
		if (Ethna::isError($r)) {
			$this->db_list[$db_varname] = null;
			return $r;
		}

		register_shutdown_function(array($this, 'shutdownDB'));

		return $this->db_list[$db_varname];
	}

	/**
	 *	DBオブジェクト(全て)を取得する
	 *
	 *	@access	public
	 *	@return	mixed	array:Ethna_DBオブジェクトの一覧 Ethan_Error:(いずれか一つ以上の接続で)エラー
	 */
	function getDBList()
	{
		$r = array();
        $db_define_list = $this->controller->getDBType();
		foreach ($db_define_list as $db_key => $db_type) {
			$db =& $this->getDB($db_key);
			if (Ethna::isError($db)) {
				return $r;
			}
			$elt = array();
			$elt['db'] =& $db;
            $elt['key'] = $db_key;
			$elt['type'] = $db_type;
			$elt['varname'] = "db";
			if ($db_key != "") {
				$elt['varname'] = sprintf("db_%s", strtolower($db_key));
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
		foreach (array_keys($this->db_list) as $key) {
			if ($this->db_list[$key] != null && $this->db_list[$key]->isValid()) {
				$this->db_list[$key]->disconnect();
				unset($this->db_list[$key]);
			}
		}
	}

	/**
	 *	指定されたDBキーに対応する(当該DBオブジェクトを格納するための)メンバ変数名を取得する
     *
     *  正直もう要らないのですが、後方互換性維持のために一応残してある状態です
     *  (Ethna_AppManagerクラスなどで、$this->dbとかしている箇所が少なからずあ
     *  るので)
	 *
	 *	@access	private
	 *	@param	string	$db_key DBキー
	 *	@return	mixed	string:メンバ変数名 Ethna_Error:不正なDB種別
	 */
	function &_getDBVarname($db_key = "")
	{
		$r = $this->controller->getDBType($db_key);
		if (is_null($r)) {
			return Ethna::raiseError(E_DB_INVALIDTYPE, "未定義のDB種別[%s]", $db_key);
		}

		if ($db_key == "") {
			$db_varname = "";
		} else {
			$db_varname = sprintf("%s", strtolower($db_key));
		}

		return $db_varname;
	}
}
// }}}
?>
