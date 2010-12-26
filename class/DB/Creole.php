<?php
// vim: foldmethod=marker
/**
 *  Creole.php
 *
 *  @package    Ethna
 *  @author     halt feits <halt.feits@gmail.com>
 *  @version    $Id$
 */

require_once 'creole/Creole.php';

/**
 *  Ethna用DB抽象クラス
 *
 *  EthnaのフレームワークでDBオブジェクトを扱うための抽象クラス
 *  (のつもり...あぁすばらしきPHP 4)
 *
 *  @package    Ethna
 *  @author     halt feits <halt.feits@gmail.com>
 *  @access     public
 */
class Ethna_DB_Creole extends Ethna_DB
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
    public function __construct(&$controller, $dsn, $persistent)
    {
        parent::__construct($controller, $dsn, $persistent);
    }

    /**
     *  DBに接続する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function connect()
    {
        $this->db = Creole::getConnection($this->dsn);
        return 0;
    }

    /**
     *  DB接続を切断する
     *
     *  @access public
     */
    function disconnect()
    {
        $this->db->close();
        return 0;
    }

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

    /**
     *  DBトランザクションを開始する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function begin()
    {
        return 0;
    }

    /**
     *  DBトランザクションを中断する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function rollback()
    {
        $this->db->rollback();
        return 0;
    }

    /**
     *  DBトランザクションを終了する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function commit()
    {
        $this->db->commit();
        return 0;
    }

    /**
     *
     * PrepareStatement
     *
     * @return  Object
     * @access  public
     */
    function prepareStatement($sql)
    {
        return $this->db->prepareStatement($sql);
    }
}
