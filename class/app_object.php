<?php
/**
 *	app_object.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/** アプリケーションオブジェクト状態: 使用可能 */
define('OBJECT_STATE_ACTIVE', 0);
/** アプリケーションオブジェクト状態: 使用不可 */
define('OBJECT_STATE_INACTIVE', 100);


/** アプリケーションオブジェクトソートフラグ: 昇順 */
define('OBJECT_SORT_ASC', 0);
/** アプリケーションオブジェクトソートフラグ: 降順 */
define('OBJECT_SORT_DESC', 1);


/** アプリケーションオブジェクト検索条件: != */
define('OBJECT_CONDITION_NE', 0);

/** アプリケーションオブジェクト検索条件: == */
define('OBJECT_CONDITION_EQ', 1);

/** アプリケーションオブジェクト検索条件: LIKE */
define('OBJECT_CONDITION_LIKE', 2);

/** アプリケーションオブジェクト検索条件: > */
define('OBJECT_CONDITION_GT', 3);

/** アプリケーションオブジェクト検索条件: < */
define('OBJECT_CONDITION_LT', 4);

/** アプリケーションオブジェクト検索条件: >= */
define('OBJECT_CONDITION_GE', 5);

/** アプリケーションオブジェクト検索条件: <= */
define('OBJECT_CONDITION_LE', 6);


/** アプリケーションオブジェクトインポートオプション: NULLプロパティ無変換 */
define('OBJECT_IMPORT_IGNORE_NULL', 1);

/** アプリケーションオブジェクトインポートオプション: NULLプロパティ→空文字列変換 */
define('OBJECT_IMPORT_CONVERT_NULL', 2);


/**
 *	アプリケーションマネージャのベースクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_AppManager
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
	 *  @var    object  Ethna_DB      DBオブジェクト
	 */
	var $db;

	/**
	 *	@var	object	Ethna_I18N			i18nオブジェクト
	 */
	var $i18n;

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
	 *	Ethna_AppManagerのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	&$backend	backendオブジェクト
	 */
	function Ethna_AppManager(&$backend)
	{
		// 基本オブジェクトの設定
		$this->backend =& $backend;
		$this->config = $backend->getConfig();
		$this->i18n =& $backend->getI18N();
		$this->action_form =& $backend->getActionForm();
		$this->af =& $this->action_form;
		$this->session =& $backend->getSession();
		$this->db =& $this->backend->getDB();
	}

	/**
	 *	属性の一覧を返す
	 *
	 *	@access	public
	 *	@param	string	$attr_name	属性の名前(変数名)
	 *	@return	array	属性値一覧
	 */
	function getAttrList($attr_name)
	{
		$varname = $attr_name . "_list";
		return $this->$varname;
	}

	/**
	 *	属性の表示名を返す
	 *
	 *	@access	public
	 *	@param	string	$attr_name	属性の名前(変数名)
	 *	@param	mixed	$id			属性ID
	 *	@return	string	属性の表示名
	 */
	function getAttrName($attr_name, $id)
	{
		$varname = $attr_name . "_list";
		if (is_array($this->$varname) == false) {
			return null;
		}
		$list =& $this->$varname;
		if (isset($list[$id]) == false) {
			return null;
		}
		return $list[$id]['name'];
	}

	/**
	 *	属性の表示名(詳細)を返す
	 *
	 *	@access	public
	 *	@param	string	$attr_name	属性の名前(変数名)
	 *	@param	mixed	$id			属性ID
	 *	@return	string	属性の詳細表示名
	 */
	function getAttrLongName($attr_name, $id)
	{
		$varname = $attr_name . "_list";
		if (is_array($this->$varname) == false) {
			return null;
		}
		$list =& $this->$varname;
		if (isset($list[$id]['long_name']) == false) {
			return null;
		}

		return $list[$id]['long_name'];
	}

	/**
	 *	オブジェクトの一覧を返す
	 *
	 *	@access	public
	 *	@param	string	$class	Ethna_AppObjectの継承クラス名
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	mixed	array(0 => 検索条件にマッチした件数, 1 => $offset, $countにより指定された件数のオブジェクトID一覧) Ethna_Error:エラー
	 *	@todo	パフォーマンス対策(1オブジェクトの占有メモリが多い場合)
	 */
	function getObjectList($class, $filter = null, $order = null, $offset = null, $count = null)
	{
		$object_list = array();
		$class_name = sprintf("%s_%s", $this->backend->getAppId(), $class);

		$tmp =& new $class_name($this->backend);
		list($length, $prop_list) = $tmp->searchProp(null, $filter, $order, $offset, $count);

		foreach ($prop_list as $prop) {
			$object =& new $class_name($this->backend, null, null, $prop);
			$object_list[] = $object;
		}

		return array($length, $object_list);
	}

	/**
	 *	オブジェクトプロパティの一覧を返す
	 *
	 *	getObjectList()メソッドは条件にマッチするIDを元にEthna_AppObjectを生成する
	 *	ためコストがかかる。こちらはプロパティのみをSELECTするので低コストでデータ
	 *	を取得することが可能。
	 *
	 *	@access	public
	 *	@param	string	$class		Ethna_AppObjectの継承クラス名
	 *	@param	array	$keys		取得するプロパティ一覧
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	mixed	array(0 => 検索条件にマッチした件数, 1 => $offset, $countにより指定された件数のプロパティ一覧) Ethna_Error:エラー
	 */
	function getObjectPropList($class, $keys = null, $filter = null, $order = null, $offset = null, $count = null)
	{
		$prop_list = array();
		$class_name = sprintf("%s_%s", $this->backend->getAppId(), $class);

		$tmp =& new $class_name($this->backend);
		return $tmp->searchProp($keys, $filter, $order, $offset, $count);
	}
}

/**
 *	アプリケーションオブジェクトのベースクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 *	@todo		複数テーブルのJOIN対応
 *	@todo		複数カラムによるプライマリキー利用時の動作検証
 */
class Ethna_AppObject
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
	 *  @var    object  Ethna_DB      DBオブジェクト
	 */
	var $db;

	/**
	 *	@var	object	Ethna_I18N			i18nオブジェクト
	 */
	var $i18n;

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

	/**
	 *	@var	array	テーブル定義
	 */
	var	$table_def = array();

	/**
	 *	@var	array	プロパティ定義
	 */
	var	$prop_def = array();

	/**
	 *	@var	array	プロパティ
	 */
	var	$prop = null;
	var $prop_backup = null;

	/** 
	 *	@var	array	プライマリキー定義
	 */
	var	$id_def = null;

	/**
	 *	@var	int		オブジェクトID
	 */
	var	$id = null;

	/**#@-*/


	/**
	 *	Ethna_AppObjectクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Backend	&$backend	Ethna_Backendオブジェクト
	 *	@param	mixed	$key_type	検索キー名
	 *	@param	mixed	$key		検索キー
	 *	@param	array	$prop		プロパティ一覧
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function Ethna_AppObject(&$backend, $key_type = null, $key = null, $prop = null)
	{
		$this->backend =& $backend;
		$this->config =& $backend->getConfig();
		$this->action_form =& $backend->getActionForm();
		$this->af =& $this->action_form;
		$this->session =& $backend->getSession();
		$this->db =& $backend->getDB();

		$c =& $backend->getController();

		// Ethna_AppManagerオブジェクトの設定
		$manager_list = $c->getManagerList();
		foreach ($manager_list as $k => $v) {
			$this->$k = $backend->getManager($v);
		}

		// オブジェクトのプライマリキー定義構築
		foreach ($this->prop_def as $k => $v) {
			if ($v['primary'] == false) {
				continue;
			}
			if (is_null($this->id_def)) {
				$this->id_def = $k;
			} else if (is_array($this->id_def)) {
				$this->id_def[] = $k;
			} else {
				$this->id_def = array($this->id_def, $k);
			}
		}
		
		// DBエラー
		if (is_null($this->db)) {
			return Ethna::raiseError(E_DB_NODSN, "Ethna_AppObjectを利用するにはデータベース設定が必要です");
		} else if (Ethna::isError($this->db)) {
			return $this->db;
		}

		// キー妥当性チェック
		if (is_null($key_type) && is_null($key) && is_null($prop)) {
			// perhaps for adding object
			return 0;
		}

		// プロパティ設定
		if (is_null($prop)) {
			$this->_setPropByDB($key_type, $key);
		} else {
			$this->_setPropByValue($prop);
		}

		$this->prop_backup = $this->prop;

		if (is_array($this->id_def)) {
			$this->id = array();
			foreach ($this->id_def as $k) {
				$this->id[] = $this->prop[$k];
			}
		} else {
			$this->id = $this->prop[$this->id_def];
		}

		return 0;
	}

	/**
	 *	有効なオブジェクトかどうかを返す
	 *
	 *	@access	public
	 *	@return	bool	true:有効 false:無効
	 */
	function isValid()
	{
		return is_null($this->id) ? false : true;
	}

	/**
	 *	アクティブなオブジェクトかどうかを返す
	 *
	 *	isValid()メソッドはオブジェクト自体が有効かどうかを判定するのに対し
	 *	isActive()はオブジェクトがアプリケーションとして有効かどうかを返す
	 *
	 *	@access	public
	 *	@return	bool	true:アクティブ false:非アクティブ
	 */
	function isActive()
	{
		if ($this->isValid() == false) {
			return false;
		}
		return $this->prop['state'] == OBJECT_STATE_ACTIVE ? true : false;
	}

	/**
	 *	オブジェクトのプロパティ定義を返す
	 *
	 *	@access	public
	 *	@return	array	オブジェクトのプロパティ定義
	 */
	function getDef()
	{
		return $this->prop_def;
	}

	/**
	 *	プライマリキー定義を返す
	 *
	 *	@access	public
	 *	@return	mixed	プライマリキーとなるプロパティ名
	 */
	function getIdDef()
	{
		return $this->id_def;
	}

	/**
	 *	オブジェクトIDを返す
	 *
	 *	@access	public
	 *	@return	mixed	オブジェクトID
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 *	オブジェクトプロパティへのアクセサ(R)
	 *
	 *	@access	public
	 *	@param	string	$key	プロパティ名
	 *	@return	mixed	プロパティ
	 */
	function get($key)
	{
		if (isset($this->prop_def[$key]) == false) {
			trigger_error(sprintf("Unknown property [%s]", $key), E_USER_ERROR);
			return null;
		}
		if (isset($this->prop[$key])) {
			return $this->prop[$key];
		}
		return null;
	}

	/**
	 *	オブジェクトプロパティ表示名へのアクセサ
	 *
	 *	@access	public
	 *	@param	string	$key	プロパティ名
	 *	@return	string	プロパティの表示名
	 */
	function getName($key)
	{
		return $this->get($key);
	}

	/**
	 *	オブジェクトプロパティ表示名(詳細)へのアクセサ
	 *
	 *	@access	public
	 *	@param	string	$key	プロパティ名
	 *	@return	string	プロパティの表示名(詳細)
	 */
	function getLongName($key)
	{
		return $this->get($key);
	}

	/**
	 *	プロパティ表示名を格納した連想配列を取得する
	 *
	 *	@access	public
	 *	@return	array	プロパティ表示名を格納した連想配列
	 */
	function getNameObject()
	{
		$object = array();

		foreach ($this->prop_def as $key => $elt) {
			$object[$elt['form_name']] = $this->getName($key);
		}

		return $object;
	}

	/**
	 *	オブジェクトプロパティへのアクセサ(W)
	 *
	 *	@access	public
	 *	@param	string	$key	プロパティ名
	 *	@param	string	$value	プロパティ値
	 */
	function set($key, $value)
	{
		if (isset($this->prop_def[$key]) == false) {
			trigger_error(sprintf("Unknown property [%s]", $key), E_USER_ERROR);
			return null;
		}
		$this->prop[$key] = $value;
	}

	/**
	 *	オブジェクトプロパティを指定の形式でダンプする(現在はCSV形式のみサポート)
	 *
	 *	@access	public
	 *	@param	string	$type	ダンプ形式("csv"...)
	 *	@return	string	ダンプ結果(エラーの場合はnull)
	 */
	function dump($type = "csv")
	{
		$method = "_dump_$type";
		if (method_exists($this, $method) == false) {
			return Ethna::raiseError(E_APP_NOMETHOD, "メソッド未定義[%s]", $method);
		}

		return $this->$method();
	}

	/**
	 *	フォーム値からオブジェクトプロパティをインポートする
	 *
	 *	@access	public
	 *	@param	int		$option	インポートオプション(OBJECT_IMPORT_IGNORE_NULL,...)
	 */
	function importForm($option = null)
	{
		foreach ($this->getDef() as $k => $def) {
			$value = $this->af->get($def['form_name']);
			if (is_null($value)) {
				// フォームから値が送信されていない場合の振舞い
				if ($option == OBJECT_IMPORT_IGNORE_NULL) {
					// nullはスキップ
					continue;
				} else if ($option == OBJECT_IMPORT_CONVERT_NULL) {
					// 空文字列に変換
					$value = '';
				}
			}
			$this->set($k, $value);
		}
	}

	/**
	 *	オブジェクトプロパティをフォーム値にエクスポートする
	 *
	 *	@access	public
	 */
	function exportForm()
	{
		foreach ($this->getDef() as $k => $def) {
			$this->af->set($def['form_name'], $this->get($k));
		}
	}

	/**
	 *	オブジェクトを追加する
	 *
	 *	@access	public
	 *	@return	mixed	(int):追加したオブジェクトのID Ethna_Error:エラー
	 *	@todo	フィールド定義にseq要素を追加してINSERT後に取得
	 */
	function add()
	{
		// ユニークチェック(DBからのエラーではどのカラムが重複したかが判定できない)
		$duplicate_key_list = $this->_getDuplicateKeyList();
		if (Ethna::isError($duplicate_key_list)) {
			return $duplicate_key_list;
		}
		if (is_array($duplicate_key_list) && count($duplicate_key_list) > 0) {
			foreach ($duplicate_key_list as $k) {
				return Ethna::raiseNotice(E_APP_DUPENT, '重複エラー[%s]', $k);
			}
		}

		$sql = $this->_getSQL_Add();
		$r =& $this->db->query($sql);
		if (Ethna::isError($r)) {
			if ($r->getCode() == E_DB_DUPENT) {
				// レースコンディション
				return Ethna::raiseNotice(E_APP_DUPENT, '重複エラー[キー不明]');
			} else {
				return $error;
			}
		}

		$this->prop_backup = $this->prop;

		// オブジェクトIDの取得
		$insert_id = false;
		if (is_array($this->id_def) == false && (isset($this->prop[$this->id_def]) == false || $this->prop[$$this->id_def] === "" || $this->prop[$this->id_def] === null)) {
			$insert_id = true;
		}
		if ($insert_id) {
			$this->id = $this->db->getInsertId();
			$this->prop[$this->id_def] = $this->prop_backup[$this->id_def] = $this->id;
		} else {
			if (is_array($this->id_def)) {
				$this->id = array();
				foreach ($this->id_def as $k) {
					$this->id[] = $this->prop[$k];
				}
			} else {
				$this->id = $this->prop[$this->id_def];
			}
		}
		return $this->id;
	}

	/**
	 *	オブジェクトを更新する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function update()
	{
		// ユニークチェック(DBからのエラーではどのカラムが重複したかが判定できない)
		$duplicate_key_list = $this->_getDuplicateKeyList();
		if (Ethna::isError($duplicate_key_list)) {
			return $duplicate_key_list;
		}
		if (is_array($duplicate_key_list) && count($duplicate_key_list) > 0) {
			foreach ($duplicate_key_list as $k) {
				return Ethna::raiseNotice(E_APP_DUPENT, '重複エラー[%s]', $k);
			}
		}

		$sql = $this->_getSQL_Update();
		$r =& $this->db->query($sql);
		if (DB::isError($r)) {
			if ($r->getCode() == E_DB_DUPENT) {
				// レースコンディション
				return Ethna::raiseNotice(E_APP_DUPENT, '重複エラー[キー不明]');
			} else {
				return $error;
			}
		}
		$affected_rows = $this->db->affectedRows();
		if ($affected_rows <= 0) {
			$this->backend->log(LOG_NOTICE, "update query with 0 updated rows");
		}

		$this->prop_backup = $this->prop;

		return 0;
	}

	/**
	 *	オブジェクトを置換する
	 *
	 *	MySQLのREPLACE文に相当する動作を行う(add()で重複エラーが発生したら
	 *	update()を行う)
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 >0:オブジェクトID(追加時) Ethna_Error:エラー
	 */
	function replace()
	{
		$sql = $this->_getSQL_Select($this->getIdDef(), $this->getId());

		for ($i = 0; $i < 3; $i++) {
			$r = $this->db->query($sql);
			if (Ethna::isError($r)) {
				return $r;
			}
			$n = $r->numRows();

			if ($n > 0) {
				$r = $this->update();
				return $r;
			} else {
				$r = $this->add();
				if (Ethna::isError($r) == false) {
					return $r;
				} else if ($r->getCode() != E_APP_DUPENT) {
					return $r;
				}
			}
		}
		return $r;
	}

	/**
	 *	オブジェクトを削除する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function remove()
	{
		$sql = $this->_getSQL_Remove();
		$r =& $this->db->query($sql);
		if (Ethna::isError($r)) {
			return $r;
		}

		$this->id = $this->prop = $this->prop_backup = null;

		return 0;
	}

	/**
	 *	オブジェクトIDを検索する
	 *
	 *	@access	public
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	mixed	array(0 => 検索条件にマッチした件数, 1 => $offset, $countにより指定された件数のオブジェクトID一覧) Ethna_Error:エラー
	 */
	function searchId($filter = null, $order = null, $offset = null, $count = null)
	{
		if (is_null($filter) == false) {
			$sql = $this->_getSQL_SearchLength($filter);
			$r =& $this->db->query($sql);
			if (Ethna::isError($r)) {
				return $r;
			}
			$row = $r->fetchRow(DB_FETCHMODE_ASSOC);
			$length = $row['id_count'];
		} else {
			$length = null;
		}

		$id_list = array();
		$sql = $this->_getSQL_SearchId($filter, $order, $offset, $count);
		$r =& $this->db->query($sql);
		if (Ethna::isError($r)) {
			return $r;
		}
		$n = $r->numRows();
		for ($i = 0; $i < $n; $i++) {
			$row = $r->fetchRow(DB_FETCHMODE_ASSOC);

			// プライマリキーが1カラムならスカラー値に変換
			if (is_array($this->id_def) == false) {
				$row = $row[$this->id_def];
			}
			$id_list[] = $row;
		}
		if (is_null($length)) {
			$length = count($id_list);
		}

		return array($length, $id_list);
	}

	/**
	 *	オブジェクトプロパティを検索する
	 *
	 *	@access	public
	 *	@param	array	$keys		取得するプロパティ
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	mixed	array(0 => 検索条件にマッチした件数, 1 => $offset, $countにより指定された件数のオブジェクトプロパティ一覧) Ethna_Error:エラー
	 */
	function searchProp($keys = null, $filter = null, $order = null, $offset = null, $count = null)
	{
		if (is_null($filter) == false) {
			$sql = $this->_getSQL_SearchLength($filter);
			$r =& $this->db->query($sql);
			if (Ethna::isError($r)) {
				return $r;
			}
			$row = $r->fetchRow(DB_FETCHMODE_ASSOC);
			$length = $row['id_count'];
		} else {
			$length = null;
		}

		$prop_list = array();
		$sql = $this->_getSQL_SearchProp($keys, $filter, $order, $offset, $count);
		$r =& $this->db->query($sql);
		if (Ethna::isError($r)) {
			return $r;
		}
		$n = $r->numRows();
		for ($i = 0; $i < $n; $i++) {
			$row = $r->fetchRow(DB_FETCHMODE_ASSOC);
			$prop_list[] = $row;
		}
		if (is_null($length)) {
			$length = count($prop_list);
		}

		return array($length, $prop_list);
	}

	/**
	 *	オブジェクトのアプリケーションデフォルトプロパティを設定する
	 *
	 *	コンストラクタにより指定されたキーにマッチするエントリがなかった場合の
	 *	デフォルトプロパティをここで設定することが出来る
	 *
	 *	@access	protected
	 *	@param	mixed	$key_type	検索キー名
	 *	@param	mixed	$key		検索キー
	 *	@return	int		0:正常終了
	 */
	function _setDefault($key_type, $key)
	{
		return 0;
	}

	/**
	 *	オブジェクトプロパティをDBから取得する
	 *
	 *	@access	private
	 *	@param	mixed	$key_type	検索キー名
	 *	@param	mixed	$key		検索キー
	 */
	function _setPropByDB($key_type, $key)
	{
		$key_type = to_array($key_type);
		$key = to_array($key);
		if (count($key_type) != count($key)) {
			trigger_error(sprintf("Unmatched key_type & key length [%d-%d]", count($key_type), count($key)), E_USER_ERROR);
			return;
		}
		foreach ($key_type as $elt) {
			if (isset($this->prop_def[$elt]) == false) {
				trigger_error("Invalid key_type [$elt]", E_USER_ERROR);
				return;
			}
		}

		// SQL文構築
		$sql = $this->_getSQL_Select($key_type, $key);

		// プロパティ取得
		$r =& $this->db->query($sql);
		if (Ethna::isError($r)) {
			return;
		}
		$n = $r->numRows();
		if ($n == 0) {
			// try default
			if ($this->_setDefault($key_type, $key) == false) {
				// nop
			}
			return;
		} else if ($n > 1) {
			trigger_error("Invalid key (multiple rows found) [$key]", E_USER_ERROR);
			return;
		}
		$this->prop = $r->fetchRow(DB_FETCHMODE_ASSOC);
	}

	/**
	 *	コンストラクタで指定されたプロパティを設定する
	 *
	 *	@access	private
	 *	@param	array	$prop	プロパティ一覧
	 */
	function _setPropByValue($prop)
	{
		$def = $this->getDef();
		foreach ($def as $key => $value) {
			if ($value['primary'] && isset($prop[$key]) == false) {
				// プライマリキーは省略不可
				trigger_error("primary key is not identical", E_USER_ERROR);
			}
			$this->prop[$key] = $prop[$key];
		}
	}

	/**
	 *	オブジェクトのプライマリテーブルを取得する
	 *
	 *	@access	private
	 *	@return	string	オブジェクトのプライマリテーブル名
	 */
	function _getPrimaryTable()
	{
		$tables = array_keys($this->table_def);
		$table = $tables[0];
		
		return $table;
	}

	/**
	 *	重複キーを取得する
	 *
	 *	@access	private
	 *	@return	mixed	0:重複なし Ethna_Error:エラー array:重複キーのプロパティ名一覧
	 */
	function _getDuplicateKeyList()
	{
		$duplicate_key_list = array();

		// 現在設定されているプライマリキーにNULLが含まれる場合は検索しない
		$check_pkey = true;
		foreach (to_array($this->id_def) as $k) {
			if (isset($this->prop[$k]) == false || is_null($this->prop[$k])) {
				$check_pkey = false;
				break;
			}
		}

		// プライマリキーはmulti columnsになり得るので別扱い
		if ($check_pkey) {
			$sql = $this->_getSQL_Duplicate($this->id_def);
			$r =& $this->db->query($sql);
			if (Ethna::isError($r)) {
				return $r;
			} else if ($r->numRows() > 0) {
				$duplicate_key_list = to_array($this->id_def); // we can overwrite $key_list here
			}
		}

		// ユニークキー
		foreach ($this->prop_def as $k => $v) {
			if ($v['primary'] == true || $v['key'] == false) {
				continue;
			}
			$sql = $this->_getSQL_Duplicate($k);
			$r =& $this->db->query($sql);
			if (Ethna::isError($r)) {
				return $r;
			} else if ($r->NumRows() > 0) {
				$duplicate_key_list[] = $k;
			}
		}

		if (count($duplicate_key_list) > 0) {
			return $duplicate_key_list;
		} else {
			return 0;
		}
	}

	/**
	 *	オブジェクトプロパティを取得するSQL文を構築する
	 *
	 *	@access	private
	 *	@param	array	$key_type	キーとなるプロパティ名一覧
	 *	@param	array	$key		$key_typeに対応するキー一覧
	 *	@return	string	SELECT文
	 */
	function _getSQL_Select($key_type, $key)
	{
		$key_type = to_array($key_type);
		$key = to_array($key);

		// SQLエスケープ
		Ethna_AppSQL::escapeSQL($key);

		$tables = implode(',', array_keys($this->table_def));
		$columns = implode(',', array_keys($this->prop_def));

		// 検索条件
		$condition = null;
		for ($i = 0; $i < count($key_type); $i++) {
			if (is_null($condition)) {
				$condition = "WHERE ";
			} else {
				$condition .= " AND ";
			}
			$condition .= Ethna_AppSQL::getCondition($key_type[$i], $key[$i]);
		}

		$sql = "SELECT $columns FROM $tables $condition";

		return $sql;
	}

	/**
	 *	オブジェクトと追加するSQL文を構築する
	 *
	 *	@access	private
	 *	@return	string	オブジェクトを追加するためのINSERT文
	 */
	function _getSQL_Add()
	{
		$tables = implode(',', array_keys($this->table_def));

		// SET句構築
		$set_list = "";
		$prop_arg_list = $this->prop;
		Ethna_AppSQL::escapeSQL($prop_arg_list);
		foreach ($this->prop_def as $k => $v) {
			if (isset($prop_arg_list[$k]) == false) {
				continue;
			}
			if ($set_list != "") {
				$set_list .= ",";
			}
			$set_list .= sprintf("%s=%s", $k, $prop_arg_list[$k]);
		}

		$sql = "INSERT INTO $tables SET $set_list";

		return $sql;
	}

	/**
	 *	オブジェクトプロパティを更新するSQL文を構築する
	 *
	 *	@access	private
	 *	@return	オブジェクトプロパティを更新するためのUPDATE文
	 */
	function _getSQL_Update()
	{
		$tables = implode(',', array_keys($this->table_def));

		// SET句構築
		$set_list = "";
		$prop_arg_list = $this->prop;
		Ethna_AppSQL::escapeSQL($prop_arg_list);
		foreach ($this->prop_def as $k => $v) {
			if ($set_list != "") {
				$set_list .= ",";
			}
			$set_list .= sprintf("%s=%s", $k, $prop_arg_list[$k]);
		}

		// 検索条件(primary key)
		$condition = null;
		foreach (to_array($this->id_def) as $k) {
			if (is_null($condition)) {
				$condition = "WHERE ";
			} else {
				$condition .= " AND ";
			}
			$v = $this->prop_backup[$k];	// equals to $this->id
			Ethna_AppSQL::escapeSQL($v);
			$condition .= Ethna_AppSQL::getCondition($k, $v);
		}

		$sql = "UPDATE $tables SET $set_list $condition";

		return $sql;
	}

	/**
	 *	オブジェクトを削除するSQL文を構築する
	 *
	 *	@access	private
	 *	@return	string	オブジェクトを削除するためのDELETE文
	 */
	function _getSQL_Remove()
	{
		$tables = implode(',', array_keys($this->table_def));

		// 検索条件(primary key)
		$condition = null;
		foreach (to_array($this->id_def) as $k) {
			if (is_null($condition)) {
				$condition = "WHERE ";
			} else {
				$condition = " AND ";
			}
			$v = $this->prop_backup[$k];	// equals to $this->id
			Ethna_AppSQL::escapeSQL($v);
			$condition .= Ethna_AppSQL::getCondition($k, $v);
		}
		if (is_null($condition)) {
			trigger_error("DELETE with no conditon", E_USER_ERROR);
			return null;
		}

		$sql = "DELETE FROM $tables $condition";

		return $sql;
	}

	/**
	 *	オブジェクトプロパティのユニークチェックを行うSQL文を構築する
	 *
	 *	@access	private
	 *	@param	mixed	$key	ユニークチェックを行うプロパティ名
	 *	@return	string	ユニークチェックを行うためのSELECT文
	 */
	function _getSQL_Duplicate($key)
	{
		$tables = implode(',', array_keys($this->table_def));
		$columns = implode(',', array_keys($this->prop_def));	// any column will do

		$condition = null;
		// 検索条件(現在設定されているプライマリキーは検索対象から除く)
		if (is_null($this->id) == false) {
			foreach (to_array($this->id_def) as $k) {
				if (is_null($condition)) {
					$condition = "WHERE ";
				} else {
					$condition .= " AND ";
				}
				$v = $this->getId();
				Ethna_AppSQL::escapeSQL($v);
				$condition .= Ethna_AppSQL::getCondition($k, $v, OBJECT_CONDITION_NE);
			}
		}

		foreach (to_array($key) as $k) {
			if (is_null($condition)) {
				$condition = "WHERE ";
			} else {
				$condition .= " AND ";
			}
			$v = $this->prop[$k];
			Ethna_AppSQL::escapeSQL($v);
			$condition .= Ethna_AppSQL::getCondition($k, $v);
		}

		$sql = "SELECT $columns FROM $tables $condition";

		return $sql;
	}

	/**
	 *	オブジェクト検索総数(offset, count除外)を取得するSQL文を構築する
	 *
	 *	@access	private
	 *	@param	array	$filter		検索条件
	 *	@return	string	検索総数を取得するためのSELECT文
	 */
	function _getSQL_SearchLength($filter)
	{
		// テーブル
		$tables = implode(',', array_keys($this->table_def));
		if ($this->_isAdditionalField($filter)) {
			$tables .= " " . $this->_SQLPlugin_SearchTable();
		}

		$id_def = to_array($this->id_def);
		$column_id = $this->_getPrimaryTable() . "." . $id_def[0];	// any id columns will do

		$condition = $this->_getSQL_SearchCondition($filter);
		$sql = "SELECT DISTINCT COUNT($column_id) AS id_count FROM $tables $condition";

		return $sql;
	}

	/**
	 *	オブジェクトID検索を行うSQL文を構築する
	 *
	 *	@access	private
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	string	オブジェクト検索を行うSELECT文
	 */
	function _getSQL_SearchId($filter, $order, $offset, $count)
	{
		// テーブル
		$tables = implode(',', array_keys($this->table_def));
		if ($this->_isAdditionalField($filter) || $this->_isAdditionalField($order)) {
			$tables .= " " . $this->_SQLPlugin_SearchTable();
		}

		$column_id = "";
		foreach (to_array($this->id_def) as $id) {
			if ($column_id != "") {
				$column_id .= ",";
			}
			$column_id .= $this->_getPrimaryTable() . "." . $id;
		}
		$condition = $this->_getSQL_SearchCondition($filter);

		$sort = "";
		if (is_array($order)) {
			foreach ($order as $k => $v) {
				if ($sort == "") {
					$sort = "ORDER BY ";
				} else {
					$sort .= ", ";
				}
				$sort .= sprintf("%s %s", $k, $v == OBJECT_SORT_ASC ? "ASC" : "DESC");
			}
		}

		$limit = "";
		if (is_null($count) == false) {
			$limit = "LIMIT ";
			if (is_null($offset) == false) {
				$limit .= sprintf("%d,", $offset);
			}
			$limit .= sprintf("%d", $count);
		}

		$sql = "SELECT DISTINCT $column_id FROM $tables $condition $sort $limit";

		return $sql;
	}

	/**
	 *	オブジェクトプロパティ検索を行うSQL文を構築する
	 *
	 *	@access	private
	 *	@param	array	$keys		取得プロパティ一覧
	 *	@param	array	$filter		検索条件
	 *	@param	array	$order		検索結果ソート条件
	 *	@param	int		$offset		検索結果取得オフセット
	 *	@param	int		$count		検索結果取得数
	 *	@return	string	オブジェクト検索を行うSELECT文
	 */
	function _getSQL_SearchProp($keys, $filter, $order, $offset, $count)
	{
		// テーブル
		$tables = implode(',', array_keys($this->table_def));
		if ($this->_isAdditionalField($filter) || $this->_isAdditionalField($order)) {
			$tables .= " " . $this->_SQLPlugin_SearchTable();
		}
		$p_table = $this->_getPrimaryTable();

		// 検索用追加プロパティ
		if ($this->_isAdditionalField($filter) || $this->_isAdditionalField($order)) {
			$search_prop_def = $this->_SQLPlugin_SearchPropDef();
		} else {
			$search_prop_def = array();
		}
		$def = array_merge($this->getDef(), $search_prop_def);

		// カラム
		$column = "";
		if (is_null($keys)) {
			$keys = array_keys($def);
		}
		foreach (to_array($keys) as $key) {
			if (isset($def[$key]) == false) {
				continue;
			}
			if ($column != "") {
				$column .= ", ";
			}
			$t = isset($def[$key]['table']) ? $def[$key]['table'] : $p_table;
			$column .= sprintf("%s.%s", $t, $key);
		}

		$condition = $this->_getSQL_SearchCondition($filter);

		$sort = "";
		if (is_array($order)) {
			foreach ($order as $k => $v) {
				if ($sort == "") {
					$sort = "ORDER BY ";
				} else {
					$sort .= ", ";
				}
				$sort .= sprintf("%s %s", $k, $v == OBJECT_SORT_ASC ? "ASC" : "DESC");
			}
		}

		$limit = "";
		if (is_null($count) == false) {
			$limit = "LIMIT ";
			if (is_null($offset) == false) {
				$limit .= sprintf("%d,", $offset);
			}
			$limit .= sprintf("%d", $count);
		}

		$sql = "SELECT $column FROM $tables $condition $sort $limit";

		return $sql;
	}

	/**
	 *	オブジェクト検索SQLの条件文を構築する
	 *
	 *	@access	private
	 *	@param	array	$filter		検索条件
	 *	@return	string	オブジェクト検索の条件文(エラーならnull)
	 */
	function _getSQL_SearchCondition($filter)
	{
		if (is_array($filter) == false) {
			return "";
		}

		$p_table = $this->_getPrimaryTable();

		// 検索用追加プロパティ
		if ($this->_isAdditionalField($filter)) {
			$search_prop_def = $this->_SQLPlugin_SearchPropDef();
		} else {
			$search_prop_def = array();
		}
		$prop_def = array_merge($this->prop_def, $search_prop_def);

		$condition = null;
		foreach ($filter as $k => $v) {
			if (isset($prop_def[$k]) == false) {
				trigger_error(sprintf("Unknown property [%s]", $k), E_USER_ERROR);
				return null;
			}

			if (is_null($condition)) {
				$condition = "WHERE ";
			} else {
				$condition .= " AND ";
			}

			$t = isset($prop_def[$k]['table']) ? $prop_def[$k]['table'] : $p_table;

			if (is_object($v)) {
				// Ethna_AppSearchObjectが指定されている場合
				$tmp = $v->value;
				Ethna_AppSQL::escapeSQL($tmp);
				$condition .= Ethna_AppSQL::getCondition("$t.$k", $tmp, $v->condition);
			} else if (is_array($v) && count($v) > 0 && is_object($v[0])) {
				// Ethna_AppSearchObjectが配列で指定されている場合
				$n = 0;
				foreach ($v as $so) {
					if ($n > 0) {
						$condition .= " AND ";
					}
					$tmp = $so->value;
					Ethna_AppSQL::escapeSQL($tmp);
					$condition .= Ethna_AppSQL::getCondition("$t.$k", $tmp, $so->condition);
					$n++;
				}
			} else if ($prop_def[$k]['type'] == VAR_TYPE_STRING) {
				// 省略形(文字列)
				Ethna_AppSQL::escapeSQL($v);
				$condition .= Ethna_AppSQL::getCondition("$t.$k", $v, OBJECT_CONDITION_LIKE);
			} else {
				// 省略形(数値)
				Ethna_AppSQL::escapeSQL($v);
				$condition .= Ethna_AppSQL::getCondition("$t.$k", $v, OBJECT_CONDITION_EQ);
			}
		}

		return $condition;
	}

	/**
	 *	オブジェクト検索SQLプラグイン(追加テーブル)
	 *
	 *	sample:
	 *	<code>
	 *	return " LEFT JOIN bar_tbl ON foo_tbl.user_id=bar_tbl.user_id";
	 *	</code>
	 *
	 *	@access	protected
	 *	@return	string	テーブルJOINのSQL文
	 */
	function _SQLPlugin_SearchTable()
	{
		return "";
	}

	/**
	 *	オブジェクト検索SQLプラグイン(追加条件定義)
	 *
	 *	sample:
	 *	<code>
	 *	$search_prop_def = array(
	 *	  'group_id' => array(
	 *	    'primary' => true, 'key' => true, 'type' => VAR_TYPE_INT,
	 *	    'form_name' => 'group_id', 'table' => 'group_user_tbl',
	 *	  ),
	 *	);
	 *	return $search_prop_def;
	 *	</code>
	 *
	 *	@access	protected
	 *	@return	array	追加条件定義
	 */
	function _SQLPlugin_SearchPropDef()
	{
	}

	/**
	 *	オブジェクトプロパティをCSV形式でダンプする
	 *
	 *	@access	protected
	 *	@return	string	ダンプ結果
	 */
	function _dump_csv()
	{
		$dump = "";

		$n = 0;
		foreach ($this->getDef() as $k => $def) {
			if ($n > 0) {
				$dump .= ",";
			}
			$dump .= Ethna_Util::escapeCSV($this->getName($k));
			$n++;
		}

		return $dump;
	}

	/**
	 *	(検索条件|ソート条件)フィールドに追加フィールドが含まれるかどうかを返す
	 *
	 *	@access	private
	 *	@param	array	$field	(検索条件|ソート条件)定義
	 *	@return	bool	true:含まれる false:含まれない
	 */
	function _isAdditionalField($field)
	{
		if (is_array($field) == false) {
			return false;
		}

		$def = $this->getDef();
		foreach ($field as $key => $value) {
			if (array_key_exists($key, $def) == false) {
				return true;
			}
		}
		return false;
	}
}

/**
 *	アプリケーションオブジェクト検索条件クラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_AppSearchObject
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	string	検索値
	 */
	var $value;

	/**
	 *	@var	int		検索条件
	 */
	var $condition;

	/**#@-*/


	/**
	 *	Ethna_AppSearchObjectのコンストラクタ
	 *
	 *	@access	public
	 *	@param	string	$value		検索値
	 *	@param	int		$condition	検索条件(OBJECT_CONDITION_NE,...)
	 */
	function AppSearchObject($value, $condition)
	{
		$this->value = $value;
		$this->condition = $condition;
	}
}
?>
