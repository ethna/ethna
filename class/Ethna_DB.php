<?php
// vim: foldmethod=marker
/**
 *	Ethna_DB.php
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@license	http://www.opensource.org/licenses/bsd-license.php The BSD License
 *	@package	Ethna
 *	@version	$Id$
 */

// {{{ Ethna_DB
/**
 *	DBクラス
 *
 *	@author		Masaki Fujimoto <fujimoto@php.net>
 *	@access		public
 *	@package	Ethna
 */
class Ethna_DB
{
	/**#@+
	 *	@access	private
	 */

	/**	@var	object	DB				PEAR DBオブジェクト */
	var $db;

	/**	@var	object	Ethna_Logger	ログオブジェクト */
	var $logger;

	/**	@var	object	Ethna_AppSQL	SQLオブジェクト */
	var $sql;

	/**	@var	string	DBタイプ(mysql, pgsql...) */
	var $type;

	/**	@var	string	DSN */
	var $dsn;

	/**	@var	bool	持続接続フラグ */
	var $persistent;

	/**	@var	array	トランザクション管理スタック */
	var	$transaction = array();

	/**#@-*/


	/**
	 *	Ethna_DBクラスのコンストラクタ
	 *
	 *	@access	public
	 *	@param	object	Ethna_Controller	&$controller	コントローラオブジェクト
	 *	@param	string	$dsn								DSN
	 *	@param	bool	$persistent						持続接続設定
	 */
	function Ethna_DB(&$controller, $dsn, $persistent)
	{
		$this->dsn = $dsn;
		$this->persistent = $persistent;
		$this->db = null;
		$this->logger =& $controller->getLogger();
		$this->sql =& $controller->getSQL();

		$dsninfo = DB::parseDSN($dsn);
		$this->type = $dsninfo['phptype'];
	}

	/**
	 *	DBに接続する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function connect()
	{
		$this->db =& DB::connect($this->dsn, $this->persistent);
		if (DB::isError($this->db)) {
			$error = Ethna::raiseError('DB接続エラー: %s', E_DB_CONNECT, $this->db->getUserInfo());
			$error->addUserInfo($this->db);
			$this->db = null;
			return $error;
		}

		return 0;
	}

	/**
	 *	DB接続を切断する
	 *
	 *	@access	public
	 */
	function disconnect()
	{
		if (is_null($this->db)) {
			return;
		}
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
		if (is_null($this->db)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 *	DBタイプを返す
	 *
	 *	@access	public
	 *	@return	string	DBタイプ
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 *	クエリを発行する
	 *
	 *	@access	public
	 *	@param	string	$query	SQL文
	 *	@return	mixed	DB_Result:結果オブジェクト Ethna_Error:エラー
	 */
	function &query($query)
	{
		return $this->_query($query);
	}

	/**
	 *	SQL文指定クエリを発行する
	 *
	 *	@access	public
	 *	@param	string	$sqlid		SQL-ID(+引数)
	 *	@return	mixed	DB_Result:結果オブジェクト Ethna_Error:エラー
	 */
	function &sqlquery($sqlid)
	{
		$args = func_get_args();
		array_shift($args);
		$query = $this->sql->get($sqlid, $args);

		return $this->_query($query);
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
	 *	直近のINSERTによるIDを取得する
	 *
	 *	接続中のDBがmysqlならmysql_insert_id()の値を返す
	 *
	 *	@access	public
	 *	@return	mixed	int:直近のINSERTにより生成されたID null:未サポート
	 */
	function getInsertId()
	{
		if ($this->isValid() == false) {
			return null;
		} else if ($this->type == 'mysql') {
			return mysql_insert_id($this->db->connection);
		}

		return null;
	}

	/**
	 *	直近のクエリによる更新行数を取得する
	 *
	 *	@access	public
	 *	@return	int		更新行数
	 */
	function affectedRows()
	{
		return $this->db->affectedRows();
	}

	/**
	 *	テーブルをロックする
	 *
	 *	@access	public
	 *	@param	mixed	ロック対象テーブル名
	 *	@return	mixed	DB_Result:結果オブジェクト Ethna_Error:エラー
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

		return $this->query("LOCK TABLES $sql");
	}

	/**
	 *	テーブルのロックを解放する
	 *
	 *	@access	public
	 *	@return	mixed	DB_Result:結果オブジェクト Ethna_Error:エラー
	 */
	function unlock()
	{
		$this->message = null;
		return $this->query("UNLOCK TABLES");
	}

	/**
	 *	DBトランザクションを開始する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function begin()
	{
		if (count($this->transaction) > 0) {
			$this->transaction[] = true;
			return 0;
		}

		$r = $this->query('BEGIN;');
		if (Ethna::isError($r)) {
			return $r;
		}
		$this->transaction[] = true;

		return 0;
	}

	/**
	 *	DBトランザクションを中断する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function rollback()
	{
		if (count($this->transaction) == 0) {
			return 0;
		}

		// ロールバック時はスタック数に関わらずトランザクションをクリアする
		$r = $this->query('ROLLBACK;');
		if (Ethna::isError($r)) {
			return $r;
		}
		$this->transaction = array();

		return 0;
	}

	/**
	 *	DBトランザクションを終了する
	 *
	 *	@access	public
	 *	@return	mixed	0:正常終了 Ethna_Error:エラー
	 */
	function commit()
	{
		if (count($this->transaction) == 0) {
			return 0;
		} else if (count($this->transaction) > 1) {
			array_pop($this->transaction);
			return 0;
		}

		$r = $this->query('COMMIT;');
		if (Ethna::isError($r)) {
			return $r;
		}
		array_pop($this->transaction);

		return 0;
	}

	/**
	 *	クエリを発行する
	 *
	 *	@access	private
	 *	@param	string	$query	SQL文
	 *	@return	mixed	DB_Result:結果オブジェクト Ethna_Error:エラー
	 */
	function &_query($query)
	{
		$this->logger->log(LOG_DEBUG, "$query");
		$r =& $this->db->query($query);
		if (DB::isError($r)) {
			if ($r->getCode() == DB_ERROR_ALREADY_EXISTS) {
				$error = Ethna::raiseNotice('ユニーク制約エラー SQL[%s]', E_DB_DUPENT, $query, $this->db->errorNative(), $r->getUserInfo());
			} else {
				$error = Ethna::raiseError('クエリエラー SQL[%s] CODE[%d] MESSAGE[%s]', E_DB_QUERY, $query, $this->db->errorNative(), $r->getUserInfo());
			}
			return $error;
		}
		return $r;
	}
}
// }}}
?>
