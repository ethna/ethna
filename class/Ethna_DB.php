<?php
// vim: foldmethod=marker
/**
 *  Ethna_DB.php
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

    /** @var    object  DB              DBオブジェクト */
    var $db;

    /** @var    array   トランザクション管理スタック */
    var $transaction = array();

    /**#@-*/


    /**
     *  Ethna_DBクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    &$controller    コントローラオブジェクト
     *  @param  string  $dsn                                DSN
     *  @param  bool    $persistent                         持続接続設定
     */
    function Ethna_DB(&$controller, $dsn, $persistent)
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
     *  @return mixed   array: PEAR::DBに準じたメタデータ Ethna_Error::エラー
     */
    function getMetaData()
    {
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
?>
