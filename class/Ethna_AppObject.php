<?php
// vim: foldmethod=marker
/**
 *  Ethna_AppObject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_AppObject
/**
 *  アプリケーションオブジェクトのベースクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 *  @todo       複数テーブルの対応
 */
class Ethna_AppObject
{
    // {{{ properties
    /**#@+
     *  @access private
     */

    /** @var    object  Ethna_Backend       backendオブジェクト */
    var $backend;

    /** @var    object  Ethna_Config        設定オブジェクト */
    var $config;

    /** @var    object  Ethna_I18N          i18nオブジェクト */
    var $i18n;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト */
    var $action_form;

    /** @var    object  Ethna_ActionForm    アクションフォームオブジェクト(省略形) */
    var $af;

    /** @var    object  Ethna_Session       セッションオブジェクト */
    var $session;

    /** @var    string  DB定義プレフィクス */
    var $db_prefix = null;

    /** @var    array   テーブル定義 */
    var $table_def = null;

    /** @var    array   プロパティ定義 */
    var $prop_def = null;

    /** @var    array   プロパティ */
    var $prop = null;

    /** @var    array   プロパティ(バックアップ) */
    var $prop_backup = null;

    /** @var    int     プロパティ定義キャッシュ有効期間(sec) */
    var $prop_def_cache_lifetime = 86400;

    /** @var    array   プライマリキー定義 */
    var $id_def = null;

    /** @var    int     オブジェクトID */
    var $id = null;

    /**#@-*/
    // }}}

    // {{{ Ethna_AppObject
    /**
     *  Ethna_AppObjectクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   &$backend   Ethna_Backendオブジェクト
     *  @param  mixed   $key_type   検索キー名
     *  @param  mixed   $key        検索キー
     *  @param  array   $prop       プロパティ一覧
     *  @return mixed   0:正常終了 -1:キー/プロパティ未指定 Ethna_Error:エラー
     */
    function Ethna_AppObject(&$backend, $key_type = null, $key = null, $prop = null)
    {
        $this->backend =& $backend;
        $this->config =& $backend->getConfig();
        $this->action_form =& $backend->getActionForm();
        $this->af =& $this->action_form;
        $this->session =& $backend->getSession();
        $ctl =& $backend->getController();

        // DBオブジェクトの設定
        $db_list = $this->_getDBList();
        if (Ethna::isError($db_list)) {
            return $db_list;
        } else if (is_null($db_list['rw'])) {
            return Ethna::raiseError(
                "Ethna_AppObjectを利用するにはデータベース設定が必要です",
                E_DB_NODSN);
        }
        $this->my_db_rw =& $db_list['rw'];
        $this->my_db_ro =& $db_list['ro'];
        // XXX: app objはdb typeを知らなくても動くべき
        $this->my_db_type = $this->my_db_rw->getType();

        // プロパティ定義自動取得
        if (is_null($this->table_def)) {
            $this->table_def = $this->_getTableDef();
        }
        if (is_string($this->table_def)) {
            $this->table_def = array($this->table_def => array('primary' => true));
        }
        if (is_null($this->prop_def)) {
            $this->prop_def = $this->_getPropDef();
        }

        // プロパティ定義の必須キーを補完
        foreach (array_keys($this->prop_def) as $k) {
            if (isset($this->prop_def[$k]['primary']) == false) {
                $this->prop_def[$k]['primary'] = false;
            }
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
    // }}}

    // {{{ isValid
    /**
     *  有効なオブジェクトかどうかを返す
     *
     *  @access public
     *  @return bool    true:有効 false:無効
     */
    function isValid()
    {
        if (is_array($this->id)) {
            return is_null($this->id[0]) ? false : true;
        } else {
            return is_null($this->id) ? false : true;
        }
    }
    // }}}

    // {{{ isActive
    /**
     *  アクティブなオブジェクトかどうかを返す
     *
     *  isValid()メソッドはオブジェクト自体が有効かどうかを判定するのに対し
     *  isActive()はオブジェクトがアプリケーションとして有効かどうかを返す
     *
     *  @access public
     *  @return bool    true:アクティブ false:非アクティブ
     */
    function isActive()
    {
        if ($this->isValid() == false) {
            return false;
        }
        return $this->prop['state'] == OBJECT_STATE_ACTIVE ? true : false;
    }
    // }}}

    // {{{ getDef
    /**
     *  オブジェクトのプロパティ定義を返す
     *
     *  @access public
     *  @return array   オブジェクトのプロパティ定義
     */
    function getDef()
    {
        return $this->prop_def;
    }
    // }}}

    // {{{ getIdDef
    /**
     *  プライマリキー定義を返す
     *
     *  @access public
     *  @return mixed   プライマリキーとなるプロパティ名
     */
    function getIdDef()
    {
        return $this->id_def;
    }
    // }}}

    // {{{ getId
    /**
     *  オブジェクトIDを返す
     *
     *  @access public
     *  @return mixed   オブジェクトID
     */
    function getId()
    {
        return $this->id;
    }
    // }}}

    // {{{ get
    /**
     *  オブジェクトプロパティへのアクセサ(R)
     *
     *  @access public
     *  @param  string  $key    プロパティ名
     *  @return mixed   プロパティ
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
    // }}}

    // {{{ getName
    /**
     *  オブジェクトプロパティ表示名へのアクセサ
     *
     *  @access public
     *  @param  string  $key    プロパティ名
     *  @return string  プロパティの表示名
     */
    function getName($key)
    {
        return $this->get($key);
    }
    // }}}

    // {{{ getLongName
    /**
     *  オブジェクトプロパティ表示名(詳細)へのアクセサ
     *
     *  @access public
     *  @param  string  $key    プロパティ名
     *  @return string  プロパティの表示名(詳細)
     */
    function getLongName($key)
    {
        return $this->get($key);
    }
    // }}}

    // {{{ getNameObject
    /**
     *  プロパティ表示名を格納した連想配列を取得する
     *
     *  @access public
     *  @return array   プロパティ表示名を格納した連想配列
     */
    function getNameObject()
    {
        $object = array();

        foreach ($this->prop_def as $key => $elt) {
            $object[$elt['form_name']] = $this->getName($key);
        }

        return $object;
    }
    // }}}

    // {{{ set
    /**
     *  オブジェクトプロパティへのアクセサ(W)
     *
     *  @access public
     *  @param  string  $key    プロパティ名
     *  @param  string  $value  プロパティ値
     */
    function set($key, $value)
    {
        if (isset($this->prop_def[$key]) == false) {
            trigger_error(sprintf("Unknown property [%s]", $key), E_USER_ERROR);
            return null;
        }
        $this->prop[$key] = $value;
    }
    // }}}

    // {{{ dump
    /**
     *  オブジェクトプロパティを指定の形式でダンプする(現在はCSV形式のみサポート)
     *
     *  @access public
     *  @param  string  $type   ダンプ形式("csv"...)
     *  @return string  ダンプ結果(エラーの場合はnull)
     */
    function dump($type = "csv")
    {
        $method = "_dump_$type";
        if (method_exists($this, $method) == false) {
            return Ethna::raiseError("メソッド未定義[%s]", E_APP_NOMETHOD, $method);
        }

        return $this->$method();
    }
    // }}}

    // {{{ importForm
    /**
     *  フォーム値からオブジェクトプロパティをインポートする
     *
     *  @access public
     *  @param  int     $option インポートオプション(OBJECT_IMPORT_IGNORE_NULL,...)
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
    // }}}

    // {{{ exportForm
    /**
     *  オブジェクトプロパティをフォーム値にエクスポートする
     *
     *  @access public
     */
    function exportForm()
    {
        foreach ($this->getDef() as $k => $def) {
            $this->af->set($def['form_name'], $this->get($k));
        }
    }
    // }}}

    // {{{ add
    /**
     *  オブジェクトを追加する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function add()
    {
        // next idの取得: (pgsqlの場合のみ)
        // 取得できた場合はこのidを使う
        foreach (to_array($this->id_def) as $id_def) {
            if (isset($this->prop_def[$id_def]['seq'])
                && $this->prop_def[$id_def]['seq']) {
                // NOTE: このapp object以外からinsertがないことが前提
                $next_id = $this->my_db_rw->getNextId(
                    $this->prop_def[$id_def]['table'], $id_def);
                if ($next_id !== null && $next_id >= 0) {
                    $this->prop[$id_def] = $next_id;
                }
                break;
            }
        }

        $sql = $this->_getSQL_Add();
        for ($i = 0; $i < 4; $i++) {
            $r =& $this->my_db_rw->query($sql);
            if (Ethna::isError($r)) {
                if ($r->getCode() == E_DB_DUPENT) {
                    // 重複エラーキーの判別
                    $duplicate_key_list = $this->_getDuplicateKeyList();
                    if (Ethna::isError($duplicate_key_list)) {
                        return $duplicate_key_list;
                    }
                    if (is_array($duplicate_key_list)
                        && count($duplicate_key_list) > 0) {
                        foreach ($duplicate_key_list as $k) {
                            return Ethna::raiseNotice('重複エラー[%s]',
                                                      E_APP_DUPENT, $k);
                        }
                    }
                } else {
                    return $r;
                }
            } else {
                break;
            }
        }
        if ($i == 4) {
            // cannot be reached
            return Ethna::raiseError('重複エラーキー判別エラー', E_GENERAL);
        }

        // last insert idの取得: (mysql, sqliteのみ)
        // primary key の 'seq' フラグがある(最初の)プロパティに入れる
        $insert_id = $this->my_db_rw->getInsertId(); 
        if ($insert_id !== null && $insert_id >= 0) {
            foreach (to_array($this->id_def) as $id_def) {
                if (isset($this->prop_def[$id_def]['seq'])
                    && $this->prop_def[$id_def]['seq']) {
                    $this->prop[$id_def] = $insert_id;
                    break;
                }
            }
        }

        // IDの設定
        if (is_array($this->id_def)) {
            $this->id = array();
            foreach ($this->id_def as $k) {
                $this->id[] = $this->prop[$k];
            }
        } else if (isset($this->prop[$this->id_def])) {
            $this->id = $this->prop[$this->id_def];
        } else {
            trigger_error("primary key is missing", E_USER_ERROR);
        }

        // バックアップ/キャッシュ更新
        $this->prop_backup = $this->prop;
        $this->_clearPropCache();

        return 0;
    }
    // }}}

    // {{{ update
    /**
     *  オブジェクトを更新する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function update()
    {
        $sql = $this->_getSQL_Update();
        for ($i = 0; $i < 4; $i++) {
            $r =& $this->my_db_rw->query($sql);
            if (Ethna::isError($r)) {
                if ($r->getCode() == E_DB_DUPENT) {
                    // 重複エラーキーの判別
                    $duplicate_key_list = $this->_getDuplicateKeyList();
                    if (Ethna::isError($duplicate_key_list)) {
                        return $duplicate_key_list;
                    }
                    if (is_array($duplicate_key_list)
                        && count($duplicate_key_list) > 0) {
                        foreach ($duplicate_key_list as $k) {
                            return Ethna::raiseNotice('重複エラー[%s]',
                                                      E_APP_DUPENT, $k);
                        }
                    }
                } else {
                    return $r;
                }
            } else {
                break;
            }
        }
        if ($i == 4) {
            // cannot be reached
            return Ethna::raiseError('重複エラーキー判別エラー', E_GENERAL);
        }

        $affected_rows = $this->my_db_rw->affectedRows();
        if ($affected_rows <= 0) {
            $this->backend->log(LOG_DEBUG, "update query with 0 updated rows");
        }

        // バックアップ/キャッシュ更新
        $this->prop_backup = $this->prop;
        $this->_clearPropCache();

        return 0;
    }
    // }}}

    // {{{ replace
    /**
     *  オブジェクトを置換する
     *
     *  MySQLのREPLACE文に相当する動作を行う(add()で重複エラーが発生したら
     *  update()を行う)
     *
     *  @access public
     *  @return mixed   0:正常終了 >0:オブジェクトID(追加時) Ethna_Error:エラー
     */
    function replace()
    {
        $sql = $this->_getSQL_Select($this->getIdDef(), $this->getId());

        for ($i = 0; $i < 3; $i++) {
            $r = $this->my_db_rw->query($sql);
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
    // }}}

    // {{{ remove
    /**
     *  オブジェクトを削除する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function remove()
    {
        $sql = $this->_getSQL_Remove();
        $r =& $this->my_db_rw->query($sql);
        if (Ethna::isError($r)) {
            return $r;
        }

        // プロパティ/バックアップ/キャッシュクリア
        $this->id = $this->prop = $this->prop_backup = null;
        $this->_clearPropCache();

        return 0;
    }
    // }}}

    // {{{ searchId
    /**
     *  オブジェクトIDを検索する
     *
     *  @access public
     *  @param  array   $filter     検索条件
     *  @param  array   $order      検索結果ソート条件
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return mixed   array(0 => 検索条件にマッチした件数,
     *                  1 => $offset, $countにより指定された件数のオブジェクトID一覧)
     *                  Ethna_Error:エラー
     */
    function searchId($filter = null, $order = null, $offset = null, $count = null)
    {
        if (is_null($offset) == false || is_null($count) == false) {
            $sql = $this->_getSQL_SearchLength($filter);
            $r =& $this->my_db_ro->query($sql);
            if (Ethna::isError($r)) {
                return $r;
            }
            $row = $this->my_db_ro->fetchRow($r, DB_FETCHMODE_ASSOC);
            $length = $row['id_count'];
        } else {
            $length = null;
        }

        $id_list = array();
        $sql = $this->_getSQL_SearchId($filter, $order, $offset, $count);
        $r =& $this->my_db_ro->query($sql);
        if (Ethna::isError($r)) {
            return $r;
        }
        $n = $r->numRows();
        for ($i = 0; $i < $n; $i++) {
            $row = $this->my_db_ro->fetchRow($r, DB_FETCHMODE_ASSOC);

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
    // }}}

    // {{{ searchProp
    /**
     *  オブジェクトプロパティを検索する
     *
     *  @access public
     *  @param  array   $keys       取得するプロパティ
     *  @param  array   $filter     検索条件
     *  @param  array   $order      検索結果ソート条件
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return mixed   array(0 => 検索条件にマッチした件数,
     *                  1 => $offset, $countにより指定された件数のオブジェクトプロパティ一覧)
     *                  Ethna_Error:エラー
     */
    function searchProp($keys = null, $filter = null, $order = null,
                        $offset = null, $count = null)
    {
        if (is_null($offset) == false || is_null($count) == false) {
            $sql = $this->_getSQL_SearchLength($filter);
            $r =& $this->my_db_ro->query($sql);
            if (Ethna::isError($r)) {
                return $r;
            }
            $row = $this->my_db_ro->fetchRow($r, DB_FETCHMODE_ASSOC);
            $length = $row['id_count'];
        } else {
            $length = null;
        }

        $prop_list = array();
        $sql = $this->_getSQL_SearchProp($keys, $filter, $order, $offset, $count);
        $r =& $this->my_db_ro->query($sql);
        if (Ethna::isError($r)) {
            return $r;
        }
        $n = $r->numRows();
        for ($i = 0; $i < $n; $i++) {
            $row = $this->my_db_ro->fetchRow($r, DB_FETCHMODE_ASSOC);
            $prop_list[] = $row;
        }
        if (is_null($length)) {
            $length = count($prop_list);
        }

        return array($length, $prop_list);
    }
    // }}}

    // {{{ _setDefault
    /**
     *  オブジェクトのアプリケーションデフォルトプロパティを設定する
     *
     *  コンストラクタにより指定されたキーにマッチするエントリがなかった場合の
     *  デフォルトプロパティをここで設定することが出来る
     *
     *  @access protected
     *  @param  mixed   $key_type   検索キー名
     *  @param  mixed   $key        検索キー
     *  @return int     0:正常終了
     */
    function _setDefault($key_type, $key)
    {
        return 0;
    }
    // }}}

    // {{{ _setPropByDB
    /**
     *  オブジェクトプロパティをDBから取得する
     *
     *  @access private
     *  @param  mixed   $key_type   検索キー名
     *  @param  mixed   $key        検索キー
     */
    function _setPropByDB($key_type, $key)
    {
        global $_ETHNA_APP_OBJECT_CACHE;

        $key_type = to_array($key_type);
        $key = to_array($key);
        if (count($key_type) != count($key)) {
            trigger_error(sprintf("Unmatched key_type & key length [%d-%d]",
                          count($key_type), count($key)), E_USER_ERROR);
            return;
        }
        foreach ($key_type as $elt) {
            if (isset($this->prop_def[$elt]) == false) {
                trigger_error("Invalid key_type [$elt]", E_USER_ERROR);
                return;
            }
        }

        // キャッシュチェック
        $class_name = strtolower(get_class($this));
        if (is_array($_ETHNA_APP_OBJECT_CACHE) == false
            || array_key_exists($class_name, $_ETHNA_APP_OBJECT_CACHE) == false) {
            $_ETHNA_APP_OBJECT_CACHE[$class_name] = array();
        }
        $cache_key = serialize(array($key_type, $key));
        if (array_key_exists($cache_key, $_ETHNA_APP_OBJECT_CACHE[$class_name])) {
            $this->prop = $_ETHNA_APP_OBJECT_CACHE[$class_name][$cache_key];
            return;
        }

        // SQL文構築
        $sql = $this->_getSQL_Select($key_type, $key);

        // プロパティ取得
        $r =& $this->my_db_ro->query($sql);
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
        $this->prop = $this->my_db_ro->fetchRow($r, DB_FETCHMODE_ASSOC);

        // キャッシュアップデート
        $_ETHNA_APP_OBJECT_CACHE[$class_name][$cache_key] = $this->prop;
    }
    // }}}

    // {{{ _setPropByValue
    /**
     *  コンストラクタで指定されたプロパティを設定する
     *
     *  @access private
     *  @param  array   $prop   プロパティ一覧
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
    // }}}

    // {{{ _getPrimaryTable
    /**
     *  オブジェクトのプライマリテーブルを取得する
     *
     *  @access private
     *  @return string  オブジェクトのプライマリテーブル名
     */
    function _getPrimaryTable()
    {
        $tables = array_keys($this->table_def);
        $table = $tables[0];
        
        return $table;
    }
    // }}}

    // {{{ _getDuplicateKeyList
    /**
     *  重複キーを取得する
     *
     *  @access private
     *  @return mixed   0:重複なし Ethna_Error:エラー array:重複キーのプロパティ名一覧
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
            $r =& $this->my_db_rw->query($sql);
            if (Ethna::isError($r)) {
                return $r;
            } else if ($r->numRows() > 0) {
                // we can overwrite $key_list here
                $duplicate_key_list = to_array($this->id_def);
            }
        }

        // ユニークキー
        foreach ($this->prop_def as $k => $v) {
            if ($v['primary'] == true || $v['key'] == false) {
                continue;
            }
            $sql = $this->_getSQL_Duplicate($k);
            $r =& $this->my_db_rw->query($sql);
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
    // }}}

    // {{{ _getSQL_Select
    /**
     *  オブジェクトプロパティを取得するSQL文を構築する
     *
     *  @access private
     *  @param  array   $key_type   キーとなるプロパティ名一覧
     *  @param  array   $key        $key_typeに対応するキー一覧
     *  @return string  SELECT文
     */
    function _getSQL_Select($key_type, $key)
    {
        $key_type = to_array($key_type);
        if (is_null($key)) {
            // add()前
            $key = array();
            for ($i = 0; $i < count($key_type); $i++) {
                $key[$i] = null;
            }
        } else {
            $key = to_array($key);
        }

        // SQLエスケープ
        Ethna_AppSQL::escapeSQL($key, $this->my_db_type);

        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));
        $columns = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->prop_def)));

        // 検索条件
        $condition = null;
        for ($i = 0; $i < count($key_type); $i++) {
            if (is_null($condition)) {
                $condition = "WHERE ";
            } else {
                $condition .= " AND ";
            }
            $condition .= Ethna_AppSQL::getCondition(
                $this->my_db_ro->quoteIdentifier($key_type[$i]), $key[$i]);
        }

        $sql = "SELECT $columns FROM $tables $condition";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_Add
    /**
     *  オブジェクトと追加するSQL文を構築する
     *
     *  @access private
     *  @return string  オブジェクトを追加するためのINSERT文
     */
    function _getSQL_Add()
    {
        $tables = implode(',',
            $this->my_db_rw->quoteIdentifier(array_keys($this->table_def)));

        $key_list = array();
        $set_list = array();
        $prop_arg_list = $this->prop;

        Ethna_AppSQL::escapeSQL($prop_arg_list, $this->my_db_type);
        foreach ($this->prop_def as $k => $v) {
            if (isset($prop_arg_list[$k]) == false) {
                continue;
            }
            $key_list[] = $this->my_db_rw->quoteIdentifier($k);
            $set_list[] = $prop_arg_list[$k];
        }

        $key_list = implode(', ', $key_list);
        $set_list = implode(', ', $set_list);
        $sql = "INSERT INTO $tables ($key_list) VALUES ($set_list)";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_Update
    /**
     *  オブジェクトプロパティを更新するSQL文を構築する
     *
     *  @access private
     *  @return オブジェクトプロパティを更新するためのUPDATE文
     */
    function _getSQL_Update()
    {
        $tables = implode(',',
            $this->my_db_rw->quoteIdentifier(array_keys($this->table_def)));

        // SET句構築
        $set_list = "";
        $prop_arg_list = $this->prop;
        Ethna_AppSQL::escapeSQL($prop_arg_list, $this->my_db_type);
        foreach ($this->prop_def as $k => $v) {
            if ($set_list != "") {
                $set_list .= ",";
            }
            $set_list .= sprintf("%s=%s",
                                 $this->my_db_rw->quoteIdentifier($k),
                                 $prop_arg_list[$k]);
        }

        // 検索条件(primary key)
        $condition = null;
        foreach (to_array($this->id_def) as $k) {
            if (is_null($condition)) {
                $condition = "WHERE ";
            } else {
                $condition .= " AND ";
            }
            $v = $this->prop_backup[$k];    // equals to $this->id
            Ethna_AppSQL::escapeSQL($v, $this->my_db_type);
            $condition .= Ethna_AppSQL::getCondition(
                $this->my_db_rw->quoteIdentifier($k), $v);
        }

        $sql = "UPDATE $tables SET $set_list $condition";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_Remove
    /**
     *  オブジェクトを削除するSQL文を構築する
     *
     *  @access private
     *  @return string  オブジェクトを削除するためのDELETE文
     */
    function _getSQL_Remove()
    {
        $tables = implode(',',
            $this->my_db_rw->quoteIdentifier(array_keys($this->table_def)));

        // 検索条件(primary key)
        $condition = null;
        foreach (to_array($this->id_def) as $k) {
            if (is_null($condition)) {
                $condition = "WHERE ";
            } else {
                $condition .= " AND ";
            }
            $v = $this->prop_backup[$k];    // equals to $this->id
            Ethna_AppSQL::escapeSQL($v, $this->my_db_type);
            $condition .= Ethna_AppSQL::getCondition(
                $this->my_db_rw->quoteIdentifier($k), $v);
        }
        if (is_null($condition)) {
            trigger_error("DELETE with no conditon", E_USER_ERROR);
            return null;
        }

        $sql = "DELETE FROM $tables $condition";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_Duplicate
    /**
     *  オブジェクトプロパティのユニークチェックを行うSQL文を構築する
     *
     *  @access private
     *  @param  mixed   $key    ユニークチェックを行うプロパティ名
     *  @return string  ユニークチェックを行うためのSELECT文
     */
    function _getSQL_Duplicate($key)
    {
        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));
        $columns = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->prop_def)));

        $condition = null;
        // 検索条件(現在設定されているプライマリキーは検索対象から除く)
        if (is_null($this->id) == false) {
            $primary_value = to_array($this->getId());
            $n = 0;
            foreach (to_array($this->id_def) as $k) {
                if (is_null($condition)) {
                    $condition = "WHERE ";
                } else {
                    $condition .= " AND ";
                }
                $value = $primary_value[$n];
                Ethna_AppSQL::escapeSQL($value, $this->my_db_type);
                $condition .= Ethna_AppSQL::getCondition(
                    $this->my_db_ro->quoteIdentifier($k), $value, OBJECT_CONDITION_NE);
                $n++;
            }
        }

        foreach (to_array($key) as $k) {
            if (is_null($condition)) {
                $condition = "WHERE ";
            } else {
                $condition .= " AND ";
            }
            $v = $this->prop[$k];
            Ethna_AppSQL::escapeSQL($v, $this->my_db_type);
            $condition .= Ethna_AppSQL::getCondition(
                $this->my_db_ro->quoteIdentifier($k), $v);
        }

        $sql = "SELECT $columns FROM $tables $condition";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_SearchLength
    /**
     *  オブジェクト検索総数(offset, count除外)を取得するSQL文を構築する
     *
     *  @access private
     *  @param  array   $filter     検索条件
     *  @return string  検索総数を取得するためのSELECT文
     *  @todo   my_db_typeの参照を廃止
     */
    function _getSQL_SearchLength($filter)
    {
        // テーブル
        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));
        if ($this->_isAdditionalField($filter)) {
            $tables .= " " . $this->_SQLPlugin_SearchTable();
        }

        $id_def = to_array($this->id_def);
        $column_id = $this->my_db_ro->quoteIdentifier($this->_getPrimaryTable())
            . "." . $this->my_db_ro->quoteIdentifier($id_def[0]);
        $id_count = $this->my_db_ro->quoteIdentifier('id_count');
        $condition = $this->_getSQL_SearchCondition($filter);

        if ($this->my_db_type === 'sqlite') {
            $sql = "SELECT COUNT(*) AS $id_count FROM "
                . " (SELECT DISTINCT $column_id FROM $tables $condition)";
        } else {
            $sql = "SELECT COUNT(DISTINCT $column_id) AS $id_count "
                . "FROM $tables $condition";
        }

        return $sql;
    }
    // }}}

    // {{{ _getSQL_SearchId
    /**
     *  オブジェクトID検索を行うSQL文を構築する
     *
     *  @access private
     *  @param  array   $filter     検索条件
     *  @param  array   $order      検索結果ソート条件
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return string  オブジェクト検索を行うSELECT文
     */
    function _getSQL_SearchId($filter, $order, $offset, $count)
    {
        // テーブル
        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));
        if ($this->_isAdditionalField($filter)
            || $this->_isAdditionalField($order)) {
            $tables .= " " . $this->_SQLPlugin_SearchTable();
        }

        $column_id = "";
        foreach (to_array($this->id_def) as $id) {
            if ($column_id != "") {
                $column_id .= ",";
            }
            $column_id .= $this->my_db_ro->quoteIdentifier($this->_getPrimaryTable())
                . "." . $this->my_db_ro->quoteIdentifier($id);
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
                $sort .= sprintf("%s %s", $this->my_db_ro->quoteIdentifier($k),
                                 $v == OBJECT_SORT_ASC ? "ASC" : "DESC");
            }
        }

        $limit = "";
        if (is_null($count) == false) {
            $limit = sprintf("LIMIT %d", $count);
            if (is_null($offset) == false) {
                $limit .= sprintf(" OFFSET %d", $offset);
            }
        }

        $sql = "SELECT DISTINCT $column_id FROM $tables $condition $sort $limit";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_SearchProp
    /**
     *  オブジェクトプロパティ検索を行うSQL文を構築する
     *
     *  @access private
     *  @param  array   $keys       取得プロパティ一覧
     *  @param  array   $filter     検索条件
     *  @param  array   $order      検索結果ソート条件
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return string  オブジェクト検索を行うSELECT文
     */
    function _getSQL_SearchProp($keys, $filter, $order, $offset, $count)
    {
        // テーブル
        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));
        if ($this->_isAdditionalField($filter)
            || $this->_isAdditionalField($order)) {
            $tables .= " " . $this->_SQLPlugin_SearchTable();
        }
        $p_table = $this->_getPrimaryTable();

        // 検索用追加プロパティ
        if ($this->_isAdditionalField($filter)
            || $this->_isAdditionalField($order)) {
            $search_prop_def = $this->_SQLPlugin_SearchPropDef();
        } else {
            $search_prop_def = array();
        }
        $def = array_merge($this->getDef(), $search_prop_def);

        // カラム
        $column = "";
        $keys = $keys === null ? array_keys($def) : to_array($keys);
        foreach ($keys as $key) {
            if (isset($def[$key]) == false) {
                continue;
            }
            if ($column != "") {
                $column .= ", ";
            }
            $t = isset($def[$key]['table']) ? $def[$key]['table'] : $p_table;
            $column .= sprintf("%s.%s",
                               $this->my_db_ro->quoteIdentifier($t),
                               $this->my_db_ro->quoteIdentifier($key));
        }

        // WHERE の条件
        $condition = $this->_getSQL_SearchCondition($filter);

        // ORDER BY
        $sort = "";
        if (is_array($order)) {
            foreach ($order as $k => $v) {
                if ($sort == "") {
                    $sort = "ORDER BY ";
                } else {
                    $sort .= ", ";
                }
                $sort .= sprintf("%s %s",
                                 $this->my_db_ro->quoteIdentifier($k),
                                 $v == OBJECT_SORT_ASC ? "ASC" : "DESC");
            }
        }

        // LIMIT, OFFSET
        $limit = "";
        if (is_null($count) == false) {
            $limit = sprintf("LIMIT %d", $count);
            if (is_null($offset) == false) {
                $limit .= sprintf(" OFFSET %d", $offset);
            }
        }

        $sql = "SELECT $column FROM $tables $condition $sort $limit";

        return $sql;
    }
    // }}}

    // {{{ _getSQL_SearchCondition
    /**
     *  オブジェクト検索SQLの条件文を構築する
     *
     *  @access private
     *  @param  array   $filter     検索条件
     *  @return string  オブジェクト検索の条件文(エラーならnull)
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
                $condition .= $v->toString(
                    $this->my_db_ro->quoteIdentifier($t)
                    .'.'. $this->my_db_ro->quoteIdentifier($k));
            } else if (is_array($v) && count($v) > 0 && is_object($v[0])) {
                // Ethna_AppSearchObjectが配列で指定されている場合
                $n = 0;
                foreach ($v as $so) {
                    if ($n > 0) {
                        $condition .= " AND ";
                    }
                    $condition .= $so->toString(
                        $this->my_db_ro->quoteIdentifier($t)
                        .'.'. $this->my_db_ro->quoteIdentifier($k));
                    $n++;
                }
            } else if ($prop_def[$k]['type'] == VAR_TYPE_STRING) {
                // 省略形(文字列)
                Ethna_AppSQL::escapeSQL($v, $this->my_db_type);
                $condition .= Ethna_AppSQL::getCondition(
                    $this->my_db_ro->quoteIdentifier($t)
                    .'.'. $this->my_db_ro->quoteIdentifier($k),
                    $v, OBJECT_CONDITION_LIKE);
            } else {
                // 省略形(数値)
                Ethna_AppSQL::escapeSQL($v, $this->my_db_type);
                $condition .= Ethna_AppSQL::getCondition(
                    $this->my_db_ro->quoteIdentifier($t)
                    .'.'. $this->my_db_ro->quoteIdentifier($k),
                    $v, OBJECT_CONDITION_EQ);
            }
        }

        return $condition;
    }
    // }}}

    // {{{ _SQLPlugin_SearchTable
    /**
     *  オブジェクト検索SQLプラグイン(追加テーブル)
     *
     *  sample:
     *  <code>
     *  return " LEFT JOIN bar_tbl ON foo_tbl.user_id=bar_tbl.user_id";
     *  </code>
     *
     *  @access protected
     *  @return string  テーブルJOINのSQL文
     */
    function _SQLPlugin_SearchTable()
    {
        return "";
    }
    // }}}

    // {{{ _SQLPlugin_SearchPropDef
    /**
     *  オブジェクト検索SQLプラグイン(追加条件定義)
     *
     *  sample:
     *  <code>
     *  $search_prop_def = array(
     *    'group_id' => array(
     *      'primary' => true, 'key' => true, 'type' => VAR_TYPE_INT,
     *      'form_name' => 'group_id', 'table' => 'group_user_tbl',
     *    ),
     *  );
     *  return $search_prop_def;
     *  </code>
     *
     *  @access protected
     *  @return array   追加条件定義
     */
    function _SQLPlugin_SearchPropDef()
    {
        return array();
    }
    // }}}

    // {{{ _dump_csv
    /**
     *  オブジェクトプロパティをCSV形式でダンプする
     *
     *  @access protected
     *  @return string  ダンプ結果
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
    // }}}

    // {{{ _isAdditionalField
    /**
     *  (検索条件|ソート条件)フィールドに追加フィールドが含まれるかどうかを返す
     *
     *  @access private
     *  @param  array   $field  (検索条件|ソート条件)定義
     *  @return bool    true:含まれる false:含まれない
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
            if (is_object($value)) {
                // Ethna_AppSearchObject
                if ($value->isTarget($key)) {
                    return true;
                }
            }
        }
        return false;
    }
    // }}}

    // {{{ _clearPropCache
    /**
     *  キャッシュデータを削除する
     *
     *  @access private
     */
    function _clearPropCache()
    {
        $class_name = strtolower(get_class($this));
        foreach (array('_ETHNA_APP_OBJECT_CACHE',
                       '_ETHNA_APP_MANAGER_OL_CACHE',
                       '_ETHNA_APP_MANAGER_OPL_CACHE',
                       '_ETHNA_APP_MANAGER_OP_CACHE') as $key) {
            if (array_key_exists($key, $GLOBALS)
                && array_key_exists($class_name, $GLOBALS[$key])) {
                unset($GLOBALS[$key][$class_name]);
            }
        }
    }
    // }}}

    // {{{ _getDBList
    /**
     *  DBオブジェクト(read only/read-write)を取得する
     *
     *  @access protected
     *  @return array   array('ro' => {read only db object}, 'rw' => {read-write db object})
     */
    function _getDBList()
    {
        $r = array('ro' => null, 'rw' => null);

        $db_list = $this->backend->getDBList();
        if (Ethna::isError($db_list)) {
            return $r;
        }
        foreach ($db_list as $elt) {
            if ($this->db_prefix) {
                // 特定のプレフィクスが指定されたDB接続を利用
                // (テーブルごとにDBが異なる場合など)
                if (strncmp($this->db_prefix,
                            $elt['key'],
                            strlen($this->db_prefix)) != 0) {
                    continue;
                }
            }

            $varname = $elt['varname'];

            // for B.C.
            $this->$varname =& $elt['db'];

            if ($elt['type'] == DB_TYPE_RW) {
                $r['rw'] =& $elt['db'];
            } else if ($elt['type'] == DB_TYPE_RO) {
                $r['ro'] =& $elt['db'];
            }
        }
        if ($r['ro'] == null && $r['rw'] != null) {
            $r['ro'] =& $r['rw'];
        }

        return $r;
    }
    // }}}

    // {{{ _getTableDef
    /**
     *  テーブル定義を取得する
     *
     *  (クラス名→テーブル名のルールを変えたい場合は
     *  このメソッドをオーバーライドします)
     *
     *  @access protected
     *  @return array   テーブル定義
     */
    function _getTableDef()
    {
        $class_name = get_class($this);
        if (preg_match('/(\w+)_(.*)/', $class_name, $match) == 0) {
            return null;
        }
        $table = $match[2];

        // PHP 4は常に小文字を返す...のでPHP 5専用
        $table = preg_replace('/^([A-Z])/e', "strtolower('\$1')", $table);
        $table = preg_replace('/([A-Z])/e', "'_' . strtolower('\$1')", $table);

        return array($table => array('primary' => true));
    }
    // }}}

    // {{{ _getPropDef
    /**
     *  プロパティ定義を取得する
     *
     *  @access protected
     *  @return array   プロパティ定義
     */
    function _getPropDef()
    {
        if (is_null($this->table_def)) {
            return null;
        }
        foreach ($this->table_def as $table_name => $table_attr) {
            // use 1st one
            break;
        }

        $cache_manager =& Ethna_CacheManager::getInstance('localfile');
        $cache_manager->setNamespace('ethna_app_object');
        $cache_key = md5($this->my_db_ro->getDSN() . '-' . $table_name);

        if ($cache_manager->isCached($cache_key, $this->prop_def_cache_lifetime)) {
            $prop_def = $cache_manager->get($cache_key,
                                            $this->prop_def_cache_lifetime);
            if (Ethna::isError($prop_def) == false) {
                return $prop_def;
            }
        }

        $r = $this->my_db_ro->getMetaData($table_name);
        if(Ethna::isError($r)){
            return null;
        }

        $prop_def = array();
        foreach ($r as $i => $field_def) {
            $primary  = in_array('primary_key', $field_def['flags']);
            $seq      = in_array('sequence',    $field_def['flags']);
            $required = in_array('not_null',    $field_def['flags']);
            $key      = in_array('primary_key', $field_def['flags'])
                        || in_array('multiple_key', $field_def['flags'])
                        || in_array('unique_key', $field_def['flags']);

            switch ($field_def['type']) {
            case 'int':
                $type = VAR_TYPE_INT;
                break;
            case 'boolean':
                $type = VAR_TYPE_BOOLEAN;
                break;
            case 'datetime':
                $type = VAR_TYPE_DATETIME;
                break;
            default:
                $type = VAR_TYPE_STRING;
                break;
            }

            $prop_def[$field_def['name']] = array(
                'primary'   => $primary,
                'seq'       => $seq,
                'key'       => $key,
                'type'      => $type,
                'required'  => $required,
                'length'    => $field_def['len'],
                'form_name' => $this->_fieldNameToFormName($field_def),
                'table'     => $table_name,
            );
        }
        
        $cache_manager->set($cache_key, $prop_def);

        return $prop_def;
    }
    // }}}

    // {{{ _fieldNameToFormName
    /**
     *  データベースフィールド名に対応するフォーム名を取得する
     *
     *  @access protected
     */
    function _fieldNameToFormName($field_def)
    {
        return $field_def['name'];
    }
    // }}}
}
// }}}
?>
