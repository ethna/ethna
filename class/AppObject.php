<?php
// vim: foldmethod=marker
/**
 *  AppObject.php
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
 *  @todo       remove dependency on PEAR::DB
 *  @todo       quoteidentifier は Ethna_AppSQL に持っていくべき
 */
class Ethna_AppObject
{
    // {{{ properties
    /**#@+
     *  @access private
     */

    /** @protected    object  Ethna_Backend       backendオブジェクト */
    protected $backend;

    /** @protected    object  Ethna_Config        設定オブジェクト */
    protected $config;

    /** @protected    object  Ethna_I18N          i18nオブジェクト */
    protected $i18n;

    /** @protected    object  Ethna_ActionForm    アクションフォームオブジェクト */
    protected $action_form;

    /** @protected    object  Ethna_ActionForm    アクションフォームオブジェクト(省略形) */
    protected $af;

    /** @protected    object  Ethna_Session       セッションオブジェクト */
    protected $session;

    /** @protected    string  DB定義プレフィクス */
    protected $db_prefix = null;

    /** @protected    array   テーブル定義。対応するDB上のテーブル名を指定します。*/
    protected $table_def = null;

    /** @protected    array   プロパティ定義。テーブルのカラム定義を記述します。 */
    protected $prop_def = null;

    /** @protected    array   プロパティ。各カラムに対応する実際の値です。 */
    protected $prop = null;

    /** @protected    array   プロパティ(バックアップ) */
    protected $prop_backup = null;

    /** @protected    int     プロパティ定義キャッシュ有効期間(sec) */
    protected $prop_def_cache_lifetime = 86400;

    /** @protected    array   プライマリキー定義 */
    protected $id_def = null;

    /** @protected    int     オブジェクトID (プライマリーキーの値) */
    protected $id = null;

    /**#@-*/
    // }}}

    // {{{ Ethna_AppObject
    /**
     *  Ethna_AppObjectクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Backend   $backend   Ethna_Backendオブジェクト
     *  @param  mixed   $key_type   レコードを特定するためのカラム名
     *                              (通常はプライマリーキーのフィールド)
     *  @param  mixed   $key        レコードを特定するためのカラム値
     *  @param  array   $prop       プロパティ(レコードの値)一覧
     *  @return mixed   0:正常終了 -1:キー/プロパティ未指定 Ethna_Error:エラー
     */
    public function __construct($backend, $key_type = null, $key = null, $prop = null)
    {
        $this->backend = $backend;
        $this->config = $backend->getConfig();
        $this->action_form = $backend->getActionForm();
        $this->af = $this->action_form;
        $this->session = $backend->getSession();
        $ctl = $backend->getController();

        // DBオブジェクトの設定
        $db_list = $this->_getDBList();
        if (Ethna::isError($db_list)) {
            return $db_list;
        } else if (is_null($db_list['rw'])) {
            return Ethna::raiseError(
                "Ethna_AppObjectを利用するにはデータベース設定が必要です",
                E_DB_NODSN);
        }
        $this->my_db_rw = $db_list['rw'];
        $this->my_db_ro = $db_list['ro'];
        // XXX: app objはdb typeを知らなくても動くべき
        $this->my_db_type = $this->my_db_rw->getType();

        // テーブル定義自動取得
        // 現在、記述可能なテーブルは常に一つで、primaryはtrue
        if (is_null($this->table_def)) {
            $this->table_def = $this->_getTableDef();
        }
        if (is_string($this->table_def)) {
            $this->table_def = array($this->table_def => array('primary' => true));
        }
        // プロパティ定義(テーブルのカラム定義)自動取得
        // データベースから自動取得され、キャッシュされる
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
            } else {  // scalar の場合
                $this->id_def = array($this->id_def, $k);
            }
        }

        // キー妥当性チェック
        if (is_null($key_type) && is_null($key) && is_null($prop)) {
            // perhaps for adding object
            return 0;
        }

        // プロパティ設定
        // $key_type, $key が指定されたらDBから値を取得し、設定する
        // $prop が設定された場合はそれを設定する
        if (is_null($prop)) {
            $this->_setPropByDB($key_type, $key);
        } else {
            $this->_setPropByValue($prop);
        }

        $this->prop_backup = $this->prop;

        //   プライマリーキーの値を設定
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
     *  プライマリーキーの値が設定されてなければ不正なオブジェクトです。
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
     *  オブジェクトのプロパティ定義(カラム定義)を返す
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
     *  オブジェクトID(primary keyの値)を返す
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
     *  @param  string  $key    プロパティ名(カラム名)
     *  @return mixed   プロパティ(カラムの値)
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
     *  プロパティ値と、表示用の値が違う場合 (「県」等）に、
     *  オーバーライドして下さい。
     *
     *  表示用の値を返す形で実装します。
     *
     *  @access public
     *  @param  string  $key    プロパティ(カラム)名
     *  @return string  プロパティ(カラム)の表示名
     */
    function getName($key)
    {
        return $this->get($key);
    }
    // }}}

    /**
     *  オブジェクトプロパティ表示名(詳細)へのアクセサ
     *  プロパティ値と、表示用の値が違う場合 (「県」等）に、
     *  オーバーライドして下さい。
     *
     *  @access public
     *  @param  string  $key    プロパティ(カラム)名
     *  @return string  プロパティ(カラム)の表示名(詳細)
     */
    function getLongName($key)
    {
        return $this->get($key);
    }
    // }}}

    // {{{ getNameObject
    /**
     *  プロパティ表示名を格納した連想配列を取得する
     *  すべての getName メソッドの戻り値を配列として返します。
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
     *  オブジェクトプロパティ(カラムに対応した値)を設定します。
     *
     *  @access public
     *  @param  string  $key    プロパティ(カラム)名
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
            return Ethna::raiseError("Undefined Method [%s]", E_APP_NOMETHOD, $method);
        }

        return $this->$method();
    }
    // }}}

    // {{{ importForm
    /**
     *  フォーム値からオブジェクトプロパティをインポートする
     *
     *  @access public
     *  @param  int     $option インポートオプション
     *                  OBJECT_IMPORT_IGNORE_NULL: フォーム値が送信されていない場合はスキップ
     *                  OBJECT_IMPORT_CONVERT_NULL: フォーム値が送信されていない場合、空文字列に変換
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
     *  オブジェクトを追加する(INSERT)
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     *  @todo remove dependency on PEAR::DB
     */
    function add()
    {
        // primary key 定義が sequence の場合、
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

        //    INSERT 文を取得し、実行
        $sql = $this->_getSQL_Add();
        for ($i = 0; $i < 4; $i++) {
            $r = $this->my_db_rw->query($sql);
            //   エラーの場合 -> 重複キーエラーの場合はリトライ
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
                            return Ethna::raiseNotice('Duplicate Key Error [%s]',
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
            return Ethna::raiseError('Cannot detect Duplicate key Error', E_GENERAL);
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

        // ID(Primary Key)の値を設定
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
     *  オブジェクトを更新する(UPDATE)
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     *  @todo remove dependency on PEAR::DB
     */
    function update()
    {
        $sql = $this->_getSQL_Update();
        //   エラーの場合 -> 重複キーエラーの場合はリトライ(4回)
        for ($i = 0; $i < 4; $i++) {  //  magic number
            $r = $this->my_db_rw->query($sql);
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
                            return Ethna::raiseNotice('Duplicate Key Error [%s]',
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
            return Ethna::raiseError('Cannot detect Duplicate key Error', E_GENERAL);
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
     *  @todo remove dependency on PEAR::DB
     */
    function replace()
    {
        $sql = $this->_getSQL_Select($this->getIdDef(), $this->getId());

        //   重複機ーエラーの場合はリトライ(4回)
        for ($i = 0; $i < 3; $i++) {  // magic number
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
     *  オブジェクト(レコード)を削除する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     *  @todo remove dependency on PEAR::DB
     */
    function remove()
    {
        $sql = $this->_getSQL_Remove();
        $r = $this->my_db_rw->query($sql);
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
     *  オブジェクトID(プライマリーキーの値)を検索する
     *
     *  @access public
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @param  array   $order      検索結果ソート条件
     *                              (カラム名をキー。値には、昇順の場合は OBJECT_SORT_ASC, 降順の場合は　OBJECT_SORT_DESC)
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return mixed   array(0 => 検索条件にマッチした件数,
     *                  1 => $offset, $countにより指定された件数のオブジェクトID一覧)
     *                  Ethna_Error:エラー
     *  TODO: remove dependency on PEAR::DB
     */
    function searchId($filter = null, $order = null, $offset = null, $count = null)
    {
       //   プライマリーキー件数検索
       if (is_null($offset) == false || is_null($count) == false) {
            $sql = $this->_getSQL_SearchLength($filter);
            $r = $this->my_db_ro->query($sql);
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
        $r = $this->my_db_ro->query($sql);
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
     *  オブジェクトプロパティ(レコード)を検索する
     *
     *  @access public
     *  @param  array   $keys       取得するプロパティ(カラム名)
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @param  array   $order      検索結果ソート条件
     *                              (カラム名をキー。値には、昇順の場合は OBJECT_SORT_ASC, 降順の場合は　OBJECT_SORT_DESC)
     *  @param  int     $offset     検索結果取得オフセット
     *  @param  int     $count      検索結果取得数
     *  @return mixed   array(0 => 検索条件にマッチした件数,
     *                  1 => $offset, $countにより指定された件数のオブジェクトプロパティ一覧)
     *                  Ethna_Error:エラー
     *  TODO: remove dependency on PEAR::DB
     */
    function searchProp($keys = null, $filter = null, $order = null,
                        $offset = null, $count = null)
    {
        //   プライマリーキー件数検索
        if (is_null($offset) == false || is_null($count) == false) {
            $sql = $this->_getSQL_SearchLength($filter);
            $r = $this->my_db_ro->query($sql);
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
        $r = $this->my_db_ro->query($sql);
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
     *  TODO: depend on PEAR::DB
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
        $r = $this->my_db_ro->query($sql);
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
     *  TODO: depend on PEAR::DB
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
            $r = $this->my_db_rw->query($sql);
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
            $r = $this->my_db_rw->query($sql);
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
     *  @param  array   $key_type   検索キーとなるプロパティ(カラム)名一覧
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
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @return string  検索総数を取得するためのSELECT文
     *  @todo   my_db_typeの参照を廃止
     */
    function _getSQL_SearchLength($filter)
    {
        // テーブル名をクォートした上で連結。
        $tables = implode(',',
            $this->my_db_ro->quoteIdentifier(array_keys($this->table_def)));

        // プライマリーキー以外の検索条件が含まれていた
        // 場合は、追加テーブルがあるとみなし、
        // その解釈は _SQLPlugin_SearchTable に任せる
        if ($this->_isAdditionalField($filter)) {
            $tables .= " " . $this->_SQLPlugin_SearchTable();
        }

        $id_def = to_array($this->id_def);

        //  テーブル名.プライマリーキー名
        //  複数あった場合ははじめのものを使う
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
     *  オブジェクトID(プライマリーキー)検索を行うSQL文を構築する
     *
     *  @access private
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @param  array   $order      検索結果ソート条件
     *                              (カラム名をキー。値には、昇順の場合は OBJECT_SORT_ASC, 降順の場合は　OBJECT_SORT_DESC)
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
     *  @param  array   $keys       取得プロパティ(カラム名)一覧
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @param  array   $order      検索結果ソート条件
     *                              (カラム名をキー。値には、昇順の場合は OBJECT_SORT_ASC, 降順の場合は　OBJECT_SORT_DESC)
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

        //  検索用追加プロパティ
        //  プライマリーキー以外の検索キーが含まれていた
        //  場合は、その解釈を _SQLPlugin_SearchPropDef に任せる
        //
        //  これによって、複数のテーブルの条件を指定することが
        //  できる(一応. ダサいけど)
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
            if ($column != "") {
                $column .= ", ";
            }
            $t = isset($def[$key]['table']) ? $def[$key]['table'] : $p_table;
            //   テーブル名.カラム名
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
     *  @param  array   $filter     WHERE検索条件(カラム名をキー、値には実際の条件値か、Ethna_AppSearchObjectを指定)
     *  @return string  オブジェクト検索の条件文(エラーならnull)
     */
    function _getSQL_SearchCondition($filter)
    {
        if (is_array($filter) == false) {
            return "";
        }

        $p_table = $this->_getPrimaryTable();

        //  検索用追加プロパティ
        //  プライマリーキー以外の検索キーが含まれていた
        //  場合は、その解釈を _SQLPlugin_SearchPropDef に任せる
        //
        //  これによって、複数のテーブルの条件を指定することが
        //  できる(一応. ダサいけど)
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

            // 細かい条件を指定するには、Ethna_AppSearchObject
            // を使う必要がある  文字列の場合は LIKE, 数値の場合
            // は = 条件しか指定できないからである。
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
     *  (検索条件|ソート条件)フィールドにプライマリーキー以外
     *  の追加フィールドが含まれるかどうかを返す
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
            $this->$varname = $elt['db'];

            if ($elt['type'] == DB_TYPE_RW) {
                $r['rw'] = $elt['db'];
            } else if ($elt['type'] == DB_TYPE_RO) {
                $r['ro'] = $elt['db'];
            }
        }
        if ($r['ro'] == null && $r['rw'] != null) {
            $r['ro'] = $r['rw'];
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

        //   JOIN には対応していないので、記述可能なテーブルは
        //   常に一つ、かつ primary は trueになる
        return array($table => array('primary' => true));
    }
    // }}}

    // {{{ _getPropDef
    /**
     *  プロパティ定義を取得します。キャッシュされている場合は、
     *  そこから取得します。
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

        $cache_manager = Ethna_CacheManager::getInstance('localfile');
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
