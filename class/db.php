<?php
/**
 *	db.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

/**
 *	DBクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 *	@todo		MySQL以外対応
 */
class Ethna_DB
{
	/**#@+
	 *	@access	private
	 */

	/**
	 *	@var	object	DB				PEAR DBオブジェクト
	 */
	var $db;

	/**
	 *	@var	object	Ethna_AppSQL	SQLオブジェクト
	 */
	var $sql;

	/**
	 *	@var	string	DSN
	 */
	var $dsn;

	/**
	 *	@var	bool	持続接続フラグ
	 */
	var $persistent;

	/**
	 *	@var	string	エラーメッセージ
	 */
	var	$message;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト
	 */
	var	$action_error;

	/**
	 *	@var	object	Ethna_ActionError	action errorオブジェクト(省略形)
	 */
	var	$ae;

	/**
	 *	@var	array	DBトランザクション管理スタック
	 */
	var	$transaction = array();

	/**#@-*/


	/**
	 *	Ethna_DBクラスのコンストラクタ
	 */
	function Ethna_DB($dsn, $persistent, &$controller)
	{
		$this->dsn = $dsn;
		$this->persistent = $persistent;
		$this->message = null;
		$this->action_error =& $controller->getActionError();
		$this->ae =& $this->action_error;

		$this->db =& DB::Connect($dsn, $persistent);
		if (DB::isError($this->db)) {
			trigger_error(sprintf("db connect error: %s", mysql_error()), E_USER_ERROR);
		}
		$this->sql =& $controller->getSQL();
	}

	/**
	 *	DB接続を切断する
	 *
	 *	@access	public
	 */
	function disconnect()
	{
		$this->db->disconnect();
	}

	/**
	 *	DB接続状態を返す
	 *
	 *	@access	public
	 *	@return	bool	true:正常 false:エラー
	 */
	function isValid()
	{
		if (DB::isError($this->db)) {
			$this->message = $this->db->GetMessage();
			return false;
		}
		return true;
	}

	/**
	 *	最新のエラーメッセージを返す
	 *
	 *	@access	public
	 *	@return	string	エラーメッセージ
	 */
	function getMessage()
	{
		return $this->message;
	}

	/**
	 *	クエリを発行する
	 *
	 *	@access	public
	 *	@param	string	$query	SQL文
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function &query($query)
	{
		return $this->_query($query, false);
	}

	/**
	 *	クエリを発行する(テストモード)
	 *
	 *	@access	public
	 *	@param	string	$query	SQL文
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function &query_test($query)
	{
		return $this->_query($query, true);
	}

	/**
	 *	SQL文指定クエリを発行する
	 *
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function &sqlquery($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $this->_query($query, false);
	}

	/**
	 *	SQL文指定クエリを発行する(テストモード)
	 *
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function &sqlquery_test($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $this->_query($query, true);
	}

	/**
	 *	SQL文を取得する
	 *	
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID
	 *	@return	string	SQL文
	 */
	function sql($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $query;
	}

	/**
	 *	直近のINSERTによるIDを返す
	 *
	 *	@access	public
	 *	@return	int		直近のINSERTにより生成されたID
	 */
	function getInsertId()
	{
		return mysql_insert_id($this->db->connection);
	}

	/**
	 *	テーブルをロックする
	 *
	 *	@access	public
	 *	@param	mixed	ロック対象テーブル名
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function lock($tables)
	{
		$this->message = null;

		$sql = "";
		foreach (to_array($tables) as $table) {
			if ($sql != "") {
				$sql .= ", ";
			}
			$sql .= "$table WRITE";
		}

		return $this->query("LOCK TABLES $sql;");
	}

	/**
	 *	テーブルのロックを解放する
	 *
	 *	@access	public
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function unlock()
	{
		$this->message = null;
		return $this->query("UNLOCK TABLES;");
	}

	/**
	 *	DBトランザクションを開始する
	 *
	 *	@access	public
	 */
	function begin()
	{
		if (count($this->transaction) > 0) {
			$this->transaction[] = true;
			return;
		}

		$this->query('BEGIN;');
		$this->transaction[] = true;
	}

	/**
	 *	DBトランザクションを中断する
	 *
	 *	@access	public
	 */
	function rollback()
	{
		if (count($this->transaction) == 0) {
			return;
		} else if (count($this->transaction) > 1) {
			array_pop($this->transaction);
			return;
		}

		$this->query('ROLLBACK;');
		array_pop($this->transaction);
	}

	/**
	 *	DBトランザクションを終了する
	 *
	 *	@access	public
	 */
	function commit()
	{
		if (count($this->transaction) == 0) {
			return;
		} else if (count($this->transaction) > 1) {
			array_pop($this->transaction);
			return;
		}

		$this->query('COMMIT;');
		array_pop($this->transaction);
	}

	/**
	 *	クエリを発行する
	 *
	 *	@access	private
	 *	@param	string	$query	SQL文
	 *	@param	bool	$test	テストモードフラグ(true:エラーオブジェクトが追加されない)
	 *	@return	object	DB_Result	結果オブジェクト
	 */
	function &_query($query, $test = false)
	{
		$this->message = null;

		$r =& $this->db->query($query);
		if (DB::isError($r)) {
			if ($test == false) {
				// 想定外のSQLエラー
				trigger_error(sprintf("db error: %s [%s]", mysql_error(), $query), E_USER_ERROR);
				return null;
			} else {
				// 想定内のSQLエラー(duplicate entry等)
				return $r;
			}
		}
		return $r;
	}
}
?>
