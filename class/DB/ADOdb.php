<?php
// vim: foldmethod=marker
/**
 *  ADOdb.php
 *
 *  @package    Ethna
 *  @author     halt feits <halt.feits@gmail.com>
 *  @version    $Id$
 */

/**
 * ADOdb config setting
 */
define('ADODB_OUTP', 'ethna_adodb_logger'); //disable output error

require_once 'adodb/adodb.inc.php';

function ethna_adodb_logger ($msg, $newline) {
    $c = Ethna_Controller::getInstance();
    $logger = $c->getLogger();

    $logger->log(LOG_DEBUG, strip_tags(str_replace("\n", "", $msg)));
}

/**
 *  Ethna_DB_ADOdb
 *
 *  EthnaのフレームワークでADOdbオブジェクトを扱うための抽象クラス
 *
 *  @package    Ethna
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 */
class Ethna_DB_ADOdb extends Ethna_DB
{
    /**#@+
     *  @access private
     */

    /**
     * @XXX stay public because of B.C.
     * @protected    object  DB              DBオブジェクト
     */
    public $db;

    /** @protected    string   dsn */
    protected $dsn;

    /**#@-*/


    /**
     *  コンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    コントローラオブジェクト
     *  @param  string  $dsn                                DSN
     *  @param  bool    $persistent                         持続接続設定
     */
    public function __construct($controller, $dsn, $persistent)
    {
        parent::__construct($controller, $dsn, $persistent);

        $this->logger = $controller->getLogger();
    }

    //{{{ connect
    /**
     *  DBに接続する
     *
     *  @access public
     *  @return mixed   0:成功 Ethna_Error:エラー
     */
    public function connect()
    {
        $dsn = $this->parseDSN($this->dsn);

        if ($dsn['phptype'] == 'sqlite') {
            $path = $dsn['database'];
            $this->db = ADONewConnection("sqlite");
            $this->db->Connect($path);
        } else {
            $this->db = ADONewConnection($this->dsn);
        }

        if ( $this->db ) {
            $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
            return 0;
        } else {
            $error = Ethna::raiseError('DB Connection Error: %s',
                E_DB_CONNECT,
                $this->dsn);
            $this->db = null;
            return $error;
        }
    }
    //}}}

    //{{{ disconnect
    /**
     *  DB接続を切断する
     *
     *  @access public
     */
    public function disconnect()
    {
        //$this->db->close();
        return 0;
    }
    //}}}

    //{{{ isValid
    /**
     *  DB接続状態を返す
     *
     *  @access public
     *  @return bool    true:正常(接続済み) false:エラー/未接続
     */
    public function isValid()
    {
        if ( is_object($this->db) ) {
            return true;
        } else {
            return false;
        }
    }
    //}}}

    //{{{ begin
    /**
     *  DBトランザクションを開始する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    public function begin()
    {
        return $this->db->BeginTrans();
    }
    //}}}

    //{{{ rollback
    /**
     *  DBトランザクションを中断する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    public function rollback()
    {
        $this->db->RollbackTrans();
        return 0;
    }
    //}}}

    //{{{ commit
    /**
     *  DBトランザクションを終了する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    public function commit()
    {
        $this->db->CommitTrans();
        return 0;
    }
    //}}}

    //{{{ query
    /**
     *  クエリを発行する
     *
     *  @access public
     *  @param  string  $query  SQL文
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    public function query($query, $inputarr = false)
    {
        return $this->_query($query, $inputarr);
    }
    //}}}

    //{{{ _query
    /**
     *  クエリを発行する
     *
     *  @access private
     *  @param  string  $query  SQL文
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    private function _query($query, $inputarr = false)
    {
        $this->logger->log(LOG_DEBUG, $query);
        $r = $this->db->execute($query, $inputarr);

        if ($r === false) {

            $error = Ethna::raiseError('Error SQL[%s] CODE[%d] MESSAGE[%s]',
                E_DB_QUERY,
                $query,
                $this->db->ErrorNo(),
                $this->db->ErrorMsg());

            return $error;

        }

        return $r;
    }
    //}}}

    //{{{ getAll
    /**
     *  結果レコードセットを返す
     *
     *  @access public
     *  @param  string $query  SQL
     *  @param  mixed  $inputarr  プレースホルダ(スカラまたは配列)
     *  @return array  $rows   連想配列のリスト
     */
    public function getAll($query, $inputarr = false)
    {
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->db->getAll($query, $inputarr);
    }
    //}}}

    //{{{ getOne
    /**
     *  結果レコードセットのうち第１行第１列目の値を返す
     *
     *  @access public
     *  @param  string  $query  SQL
     *  @param  mixed   $inputarr  プレースホルダ(スカラまたは配列)
     *  @return string  $value
     */
    public function getOne($query, $inputarr = false)
    {
        return $this->db->GetOne($query, $inputarr);
    }
    //}}}

    //{{{ getRow
    /**
     *  結果レコードセットのうち第１行目を返す
     *
     *  @access  public
     *  @param   string $query  SQL
     *  @param   mixed  $inputarr  プレースホルダ(スカラまたは配列)
     *  @return  array  $row  連想配列
     */
    public function getRow($query, $inputarr = false)
    {
        return $this->db->GetRow($query, $inputarr);
    }
    //}}}

    //{{{ getCol
    /**
     *  結果レコードセットのうち第１列目の値リストを返す
     *
     *  @param   string $query  SQL
     *  @param   mixed  $inputarr  プレースホルダ(スカラまたは配列)
     *  @return  array  $values 値リスト
     */
    public function getCol($query, $inputarr = false)
    {
        return $this->db->GetCol($query, $inputarr);
    }
    //}}}

    /**
     *  結果レコードセットを連想配列の連想配列にして返す
     *
     *  @param string $query  SQL
     *  @param mixed  $inputarr  プレースホルダ(スカラまたは配列)
     *  @return array $rows 第一カラムの値をキーとする連想配列
     */
    public function getAssoc($sql, $inputarr = false, $force_array = false, $first2cols = false)
    {
        return $this->db->GetAssoc($sql, $inputarr, $force_array, $first2cols);
    }


    //{{{ execute
    public function execute($query, $inputarr = false)
    {
        return $this->db->Execute($query, $inputarr);
    }
    //}}}

    //{{{ replace
    public function replace($table, $arrFields, $keyCols, $autoQuote = false)
    {
        return $this->db->Replace($table, $arrFields, $keyCols, $autoQuote);
    }
    //}}}

    //{{{ autoExecute
    /**
     *
     *  @param  string $table
     *  @param  array  $fields
     *  @param  string $mode  like 'UPDATE' , 'INSERT'
     *  @param  string $where
     *  @param  bool   $forceUpdate
     *  @param  bool   $magicq
     *  @return bool true:成功, false:エラー
     */
    public function autoExecute($table, $fields, $mode, $where = false, $forceUpdate = true, $magicq = false)
    {
        return $this->db->AutoExecute($table, $fields, $mode, $where, $forceUpdate, $magicq);
    }
    //}}}

    //{{{ pageExecute
    /**
     * pageExecute
     *
     * @param string $query
     * @param string $nrows
     * @param integer $page
     * @param array $inputarr
     */
    public function pageExecute($query, $nrows, $page, $inputarr = false)
    {
        return $this->db->PageExecute($query, $nrows, $page, $inputarr);
    }
    //}}}

}

