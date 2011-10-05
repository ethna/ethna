<?php
// vim: foldmethod=marker
/**
 *  DB.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_DB
/**
 *  Ethna用DB抽象クラス
 *
 *  EthnaのフレームワークでDBオブジェクトを扱うための抽象クラス
 *  (のつもり...あぁすばらしきPHP 4)
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_DB
{
    /**#@+
     *  @access private
     */

    /**
     * @XXX stay public because of B.C.
     * @protected    object  DB              DBオブジェクト
     */
    public $db;

    /** @protected    array   トランザクション管理スタック */
    protected $transaction = array();

    /**#@-*/


    /**
     *  Ethna_DBクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    コントローラオブジェクト
     *  @param  string  $dsn                                DSN
     *  @param  bool    $persistent                         持続接続設定
     */
    public function __construct($controller, $dsn, $persistent)
    {
        $this->dsn = $dsn;
        $this->persistent = $persistent;
    }

    /**
     *  DBに接続する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function connect()
    {
    }

    /**
     *  DB接続を切断する
     *
     *  @access public
     */
    function disconnect()
    {
    }

    /**
     *  DB接続状態を返す
     *
     *  @access public
     *  @return bool    true:正常(接続済み) false:エラー/未接続
     */
    function isValid()
    {
    }

    /**
     *  DBトランザクションを開始する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function begin()
    {
    }

    /**
     *  DBトランザクションを中断する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function rollback()
    {
    }

    /**
     *  DBトランザクションを終了する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function commit()
    {
    }

    /**
     *  テーブル定義情報を取得する
     *
     *  @access public
     *  @return mixed   array: PEAR::DBに準じたメタデータ
     *                  Ethna_Error::エラー
     */
    function getMetaData()
    {
        //   このメソッドはAppObject
        //   との連携に必要。
    }

    /**
     *  DSNを取得する
     *
     *  @access public
     *  @return string  DSN
     */
    function getDSN()
    {
        return $this->dsn;
    }
}
// }}}
