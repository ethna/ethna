<?php
// vim: foldmethod=marker
/**
 *	Ethna_AppManager.php
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


// {{{ Ethna_AppManager
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
	 *	@var	object	Ethna_ActionForm	アクションフォームオブジェクト
	 */
	var $action_form;

	/**
	 *	@var	object	Ethna_ActionForm	アクションフォームオブジェクト(省略形)
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

		$db_list = $backend->getDBlist();
		if (Ethna::isError($db_list) == false) {
			foreach ($db_list as $elt) {
				$varname = $elt['varname'];
				$this->$varname =& $elt['db'];
			}
		}
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
	 *	@param	array	$keys		取得するプロパティ一覧(nullなら全て)
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

	/**
	 *	オブジェクトプロパティを返す
	 *
	 *	getObjectPropList()メソッドの簡易版で、$filterにより結果が1エントリに
	 *	制限される場合(プライマリキーでの検索等)に利用する
	 *
	 *	@access	public
	 *	@param	string	$class		Ethna_AppObjectの継承クラス名
	 *	@param	array	$keys		取得するプロパティ一覧
	 *	@param	array	$filter		検索条件
	 *	@return	mixed	array:プロパティ一覧 null:エントリなし Ethna_Error:エラー
	 */
	function getObjectProp($class, $keys = null, $filter = null)
	{
		$prop_list = array();
		$class_name = sprintf("%s_%s", $this->backend->getAppId(), $class);

		$tmp =& new $class_name($this->backend);
		list(, $prop) = $tmp->searchProp($keys, $filter);
		if (count($prop) > 0) {
			return $prop[0];
		} else {
			return null;
		}
	}
}
// }}}
?>
