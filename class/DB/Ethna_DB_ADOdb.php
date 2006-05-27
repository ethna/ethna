<?php
// vim: foldmethod=marker
/**
 *  Ethna_DB_ADOdb.php
 *
 *  @package    Ethna
 *  @author     halt feits <halt.feits@gmail.com>
 *  @version    $Id$
 */

/**
 * ADOdb config setting
 */
define('ADODB_OUTP', 'sprintf'); //disable output error

require_once 'adodb/adodb.inc.php';

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

    /** @var    object  DB              DBオブジェクト */
    var $db;

    /** @var    string   dsn */
    var $dsn;

    /**#@-*/


    /**
     *  コンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     *  @param  string  $dsn                                DSN
     *  @param  bool    $persistent                         持続接続設定
     */
    function Ethna_DB_ADOdb(&$controller, $dsn, $persistent)
    {
        $this->dsn = $dsn;
        $this->persistent = $persistent;
        $this->logger =& $controller->getLogger();
    }

    //{{{ connect
    /**
     *  DBに接続する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function connect()
    {
        $dsn = $this->parseDSN($this->dsn);
        
        if ($dsn['phptype'] == 'sqlite') {
            $path = $dsn['database'];
            $this->db = ADONewConnection("sqlite");
            $this->db->Connect($path);
        } else {
            $this->db = ADONewConnection($this->dsn);
        }
        

        if ( $this->db ){
            $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
            return true;
        } else {    
            return false;
        }    
    }
    //}}}

    //{{{ disconnect
    /**
     *  DB接続を切断する
     *
     *  @access public
     */
    function disconnect()
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
    function isValid()
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
    function begin()
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
    function rollback()
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
    function commit()
    {
        $this->db->CommitTrans();
        return 0;
    }
    //}}}

    //{{{ query()
    /**
     *  クエリを発行する
     *
     *  @access public
     *  @param  string  $query  SQL文
     *  @return mixed   DB_Result:結果オブジェクト Ethna_Error:エラー
     */
    function &query($query, $inputarr = false)
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
    function &_query($query, $inputarr = false)
    {
        $this->logger->log(LOG_DEBUG, "$query");
        $r =& $this->db->execute($query, $inputarr);

        if ($r === false) {

            $error = Ethna::raiseError('エラー SQL[%s] CODE[%d] MESSAGE[%s]', E_DB_QUERY, $query, $this->db->ErrorNo(), $this->db->ErrorMsg());

            return $error;

        }
        return $r;
    }
    //}}}

    //{{{ getAll
    /**
     * getAll
     *
     * @access public
     */
    function getAll($query, $inputarr = false)
    {
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        return $this->db->getAll($query, $inputarr);
    }
    //}}}

    //{{{ getRow
    function getRow($query, $inputarr = false)
    {
        return $this->db->GetRow($query, $inputarr);
    }
    //}}}

    //{{{ replace
    function replace($table, $arrFields, $keyCols,$autoQuote=false)
    {
        return $this->db->Replace($table, $arrFields, $keyCols,$autoQuote=false);
    }
    //}}}

    //{{{ autoExecute()
    function autoExecute($table, $fields, $mode, $where = false, $forceUpdate = true, $magicq = false)
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
    function pageExecute($query, $nrows, $page, $inputarr = false)
    {
        return $this->db->PageExecute($query, $nrows, $page, $inputarr);
    }
    //}}}

    // {{{ parseDSN()

    /**
     * Parse a data source name
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *  phptype://username:password@hostspec/database_name
     *  phptype://username:password@hostspec
     *  phptype://username@hostspec
     *  phptype://hostspec/database
     *  phptype://hostspec
     *  phptype(dbsyntax)
     *  phptype
     * </code>
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *  + phptype:  Database backend used in PHP (mysql, odbc etc.)
     *  + dbsyntax: Database used with regards to SQL syntax etc.
     *  + protocol: Communication protocol to use (tcp, unix etc.)
     *  + hostspec: Host specification (hostname[:port])
     *  + database: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     */
    function parseDSN($dsn)
    {
        $parsed = array(
            'phptype'  => false,
            'dbsyntax' => false,
            'username' => false,
            'password' => false,
            'protocol' => false,
            'hostspec' => false,
            'port'     => false,
            'socket'   => false,
            'database' => false,
        );

        if (is_array($dsn)) {
            $dsn = array_merge($parsed, $dsn);
            if (!$dsn['dbsyntax']) {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }

        // Find phptype and dbsyntax
        if (($pos = strpos($dsn, '://')) !== false) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (!count($dsn)) {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+hostspec/database
        if (($at = strrpos($dsn,'@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }

        // Find protocol and hostspec

        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            // $dsn => proto(proto_opts)/database
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];

        } else {
            // $dsn => protocol+hostspec/database (old format)
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($parsed['protocol'] == 'tcp') {
            if (strpos($proto_opts, ':') !== false) {
                list($parsed['hostspec'],
                     $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['hostspec'] = $proto_opts;
            }
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ($dsn) {
            if (($pos = strpos($dsn, '?')) === false) {
                // /database
                $parsed['database'] = rawurldecode($dsn);
            } else {
                // /database?param1=value1&param2=value2
                $parsed['database'] = rawurldecode(substr($dsn, 0, $pos));
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else { // database?param1=value1
                    $opts = array($dsn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        return $parsed;
    }

    // }}}
}

?>
