<?php
// vim: foldmethod=marker
/**
 *  Ethna_DB_PEAR.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */
require_once 'DB.php';

// {{{ Ethna_DB_PEAR
/**
 *  Ethna_DBクラスの実装(PEAR版)
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_DB_PEAR extends Ethna_DB
{
    // {{{ properties
    /**#@+
     *  @access private
     */

    /** @var    object  DB              PEAR DBオブジェクト */
    var $db;

    /** @var    array   トランザクション管理スタック */
    var $transaction = array();


    /** @var    object  Ethna_Logger    ログオブジェクト */
    var $logger;

    /** @var    object  Ethna_AppSQL    SQLオブジェクト */
    var $sql;

    /** @var    string  DBタイプ(mysql, pgsql...) */
    var $type;

    /** @var    string  DSN */
    var $dsn;

    /** @var    array   DSN (DB::parseDSN()の返り値) */
    var $dsninfo;

    /** @var    bool    持続接続フラグ */
    var $persistent;

    /**#@-*/
    // }}}

    // {{{ Ethna_DBクラスの実装
    // {{{ Ethna_DB_PEAR
    /**
     *  Ethna_DB_PEARクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     *  @param  string  $dsn                                DSN
     *  @param  bool    $persistent                         持続接続設定
     */
    public function __construct(&$controller, $dsn, $persistent)
    {
        parent::__construct($controller, $dsn, $persistent);

        $this->db = null;
        $this->logger =& $controller->getLogger();
        $this->sql =& $controller->getSQL();

        $this->dsninfo = DB::parseDSN($dsn);
        $this->dsninfo['new_link'] = true;
        $this->type = $this->dsninfo['phptype'];
    }
    // }}}

    // {{{ connect
    /**
     *  DBに接続する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function connect()
    {
        $this->db =& DB::connect($this->dsninfo, $this->persistent);
        if (DB::isError($this->db)) {
            $error = Ethna::raiseError('DB Connection Error: %s',
                E_DB_CONNECT,
                $this->db->getUserInfo());
            $error->addUserInfo($this->db);
            $this->db = null;
            return $error;
        }

        return 0;
    }
    // }}}

    // {{{ disconnect
    /**
     *  DB接続を切断する
     *
     *  @access public
     */
    function disconnect()
    {
        if ($this->isValid() == false) {
            return;
        }
        $this->db->disconnect();
    }
    // }}}

    // {{{ isValid
    /**
     *  DB接続状態を返す
     *
     *  @access public
     *  @return bool    true:正常 false:エラー
     */
    function isValid()
    {
        if (is_null($this->db)
            || is_resource($this->db->connection) == false) {
            return false;
        } else {
            return true;
        }
    }
    // }}}

    // {{{ begin
    /**
     *  DBトランザクションを開始する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
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
    // }}}

    // {{{ rollback
    /**
     *  DBトランザクションを中断する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
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
    // }}}

    // {{{ commit
    /**
     *  DBトランザクションを終了する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
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
    // }}}

    // {{{ getMetaData
    /**
     *  テーブル定義情報を取得する
     *
     *  @access public
     *  @param  string  $table  テーブル名
     *  @return mixed   array: PEAR::DBに準じたメタデータ Ethna_Error::エラー
     */
    function &getMetaData($table)
    {
        $def =& $this->db->tableInfo($table);
        if (is_array($def) === false) {
            return $def;
        }

        foreach (array_keys($def) as $k) {
            $def[$k] = array_map('strtolower', $def[$k]);

            // type
            $type_map = array(
                'int'       => array(
                    'int', 'integer', '^int\(?[0-9]\+', '^serial', '[a-z]+int$',
                ),
                'boolean'   => array(
                    'bit', 'bool', 'boolean',
                ),
                'datetime'  => array(
                    'timestamp', 'datetime',
                ),
            );
            foreach ($type_map as $convert_to => $regex) {
                foreach ($regex as $r) {
                    if (preg_match('/'.$r.'/', $def[$k]['type'])) {
                        $def[$k]['type'] = $convert_to;
                        break 2;
                    }
                }
            }

            // flags
            $def[$k]['flags'] = explode(' ', $def[$k]['flags']);
            switch ($this->type) {
            case 'mysql':
                // auto_increment があれば sequence
                if (in_array('auto_increment', $def[$k]['flags'])) {
                    $def[$k]['flags'][] = 'sequence';
                }
                break;
            case 'pgsql':
                // nextval があれば sequence
                foreach ($def[$k]['flags'] as $f) {
                    if (strpos($f, 'nextval') !== false) {
                        $def[$k]['flags'][] = 'sequence';
                        break;
                    }
                }
                break;
            case 'sqlite':
                // integer, primary key ならば auto_increment を追加
                if ($def[$k]['type'] == 'int'
                    && in_array('primary_key', $def[$k]['flags'])) {
                    $def[$k]['flags'][] = 'sequence';
                }
                break;
            }
        }

        return $def;
    }
    // }}}
    // }}}

    // {{{ Ethna_AppObject連携のための実装
    // {{{ getType
    /**
     *  DBタイプを返す
     *
     *  @access public
     *  @return string  DBタイプ
     */
    function getType()
    {
        return $this->type;
    }
    // }}}

    // {{{ query
    /**
     *  クエリを発行する
     *
     *  @access public
     *  @param  string  $query  SQL文
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    function &query($query)
    {
        return $this->_query($query);
    }
    // }}}

    // {{{ getNextId
    /**
     *  直後のINSERTに使うIDを取得する
     *  (pgsqlのみ対応)
     *
     *  @access public
     *  @return mixed   int
     */
    function getNextId($table_name, $field_name)
    {
        if ($this->isValid() == false) {
            return null;
        } else if ($this->type == 'pgsql') {
            $seq_name = sprintf('%s_%s', $table_name, $field_name);
            $ret = $this->db->nextId($seq_name);
            return $ret;
        }

        return null;
    }
    // }}}

    // {{{ getInsertId
    /**
     *  直前のINSERTによるIDを取得する
     *  (mysql, sqliteのみ対応)
     *
     *  @access public
     *  @return mixed   int:直近のINSERTにより生成されたID null:未サポート
     */
    function getInsertId()
    {
        if ($this->isValid() == false) {
            return null;
        } else if ($this->type == 'mysql') {
            return mysql_insert_id($this->db->connection);
        } else if ($this->type == 'sqlite') {
            return sqlite_last_insert_rowid($this->db->connection);
        }

        return null;
    }
    // }}}

    // {{{ fetchRow
    /**
     *  DB_Result::fetchRow()の結果を整形して返す
     *
     *  @access public
     *  @return int     更新行数
     */
    function &fetchRow(&$res, $fetchmode = DB_FETCHMODE_DEFAULT, $rownum = null)
    {
        $row =& $res->fetchRow($fetchmode, $rownum);
        if (is_array($row) === false) {
            return $row;
        }

        if ($this->type === 'sqlite') {
            // "table"."column" -> column
            foreach ($row as $k => $v) {
                unset($row[$k]);
                if (($f = strstr($k, '.')) !== false) {
                    $k = substr($f, 1);
                }
                if ($k{0} === '"' && $k{strlen($k)-1} === '"') {
                    $k = substr($k, 1, -1);
                }
                $row[$k] = $v;
            }
        }

        return $row;
    }
    // }}}

    // {{{ affectedRows
    /**
     *  直近のクエリによる更新行数を取得する
     *
     *  @access public
     *  @return int     更新行数
     */
    function affectedRows()
    {
        return $this->db->affectedRows();
    }
    // }}}

    // {{{ quoteIdentifier
    /**
     *  dbのtypeに応じて識別子をquoteする
     *  (配列の場合は各要素をquote)
     *
     *  @access protected
     *  @param  mixed   $identifier array or string
     */
    function quoteIdentifier($identifier)
    {
        if (is_array($identifier)) {
            foreach (array_keys($identifier) as $key) {
                $identifier[$key] = $this->quoteIdentifier($identifier[$key]);
            }
            return $identifier;
        }
            
        switch ($this->type) {
        case 'mysql':
            $ret = '`' . $identifier . '`';
            break;
        case 'pgsql':
        case 'sqlite':
        default:
            $ret = '"' . $identifier . '"';
            break;
        }
        return $ret;
    }
    // }}}
    // }}}

    // {{{ Ethna_DB_PEAR独自の実装
    // {{{ sqlquery
    /**
     *  SQL文指定クエリを発行する
     *
     *  @access public
     *  @param  string  $sqlid      SQL-ID(+引数)
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    function &sqlquery($sqlid)
    {
        $args = func_get_args();
        array_shift($args);
        $query = $this->sql->get($sqlid, $args);

        return $this->_query($query);
    }
    // }}}

    // {{{ sql
    /**
     *  SQL文を取得する
     *  
     *  @access public
     *  @param  string  $sqlid      SQL-ID
     *  @return string  SQL文
     */
    function sql($sqlid)
    {
        $args = func_get_args();
        array_shift($args);
        $query = $this->sql->get($sqlid, $args);

        return $query;
    }
    // }}}

    // {{{ lock
    /**
     *  テーブルをロックする
     *
     *  @access public
     *  @param  mixed   ロック対象テーブル名
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
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
    // }}}

    // {{{ unlock
    /**
     *  テーブルのロックを解放する
     *
     *  @access public
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    function unlock()
    {
        $this->message = null;
        return $this->query("UNLOCK TABLES");
    }
    // }}}

    // {{{ _query
    /**
     *  クエリを発行する
     *
     *  @access private
     *  @param  string  $query  SQL文
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    function &_query($query)
    {
        $this->logger->log(LOG_DEBUG, "$query");
        $r =& $this->db->query($query);
        if (DB::isError($r)) {
            if ($r->getCode() == DB_ERROR_ALREADY_EXISTS) {
                $error = Ethna::raiseNotice('Unique Constraint Error SQL[%s]',
                    E_DB_DUPENT,
                    $query,
                    $this->db->errorNative(),
                    $r->getUserInfo());
            } else {
                $error = Ethna::raiseError('Query Error SQL[%s] CODE[%d] MESSAGE[%s]',
                    E_DB_QUERY,
                    $query,
                    $this->db->errorNative(),
                    $r->getUserInfo());
            }
            return $error;
        }
        return $r;
    }
    // }}}
    // }}}
}
// }}}
