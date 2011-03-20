<?php
// vim: foldmethod=marker
/**
 *  List.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_View_List
/**
 *  リストビュー基底クラスの実装
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_View_List extends Ethna_ViewClass
{
    /**#@+
     *  @access protected
     */

    /** @protected    int     表示開始オフセット */
    protected $offset = 0;

    /** @protected    int     表示件数 */
    protected $count = 25;

    /** @protected    array   検索対象項目一覧 */
    protected $search_list = array();

    /** @protected    string  検索マネージャクラス名 */
    protected $manager_name = null;

    /** @protected    string  表示対象クラス名 */
    protected $class_name = null;

    /**#@-*/

    /**
     *  遷移前処理
     *
     *  @access public
     */
    public function preforward()
    {
        // 表示オフセット/件数設定
        $this->offset = $this->af->get('offset');
        if ($this->offset == "") {
            $this->offset = 0;
        }
        if (intval($this->af->get('count')) > 0) {
            $this->count = intval($this->af->get('count'));
        }

        // 検索条件
        $filter = array();
        $sort = array();
        foreach ($this->search_list as $key) {
            if ($this->af->get("s_$key") != "") {
                $filter[$key] = $this->af->get("s_$key");
            }
            if ($this->af->get("sort") == $key) {
                $order = $this->af->get("order") == "desc" ? OBJECT_SORT_DESC : OBJECT_SORT_ASC;
                $sort = array(
                    $key => $order,
                );
            }
        }

        // 表示項目一覧
        $manager_name = $this->manager_name;
        for ($i = 0; $i < 2; $i++) {
            list($total, $obj_list) = $this->$manager_name->getObjectList($this->class_name, $filter, $sort, $this->offset, $this->count);
            if (count($obj_list) == 0 && $this->offset >= $total) {
                $this->offset = 0;
                continue;
            }
            break;
        }

        $r = array();
        foreach ($obj_list as $obj) {
            $value = $obj->getNameObject();
            $value = $this->_fixNameObject($value, $obj);
            $r[] = $value;
        }
        $list_name = sprintf("%s_list", strtolower(preg_replace('/(.)([A-Z])/', '\\1_\\2', $this->class_name)));
        $this->af->setApp($list_name, $r);

        // ナビゲーション
        $this->af->setApp('nav', $this->_getNavigation($total, $obj_list));
        $this->af->setAppNE('query', $this->_getQueryParameter());

        // 検索オプション
        $this->_setQueryOption();
    }

    /**
     *  表示項目を修正する
     *
     *  @access protected
     */
    protected function _fixNameObject($value, $obj)
    {
        return $value;
    }
    
    /**
     *  ナビゲーション情報を取得する
     *
     *  @access private
     *  @param  int     $total      検索総件数
     *  @param  array   $list       検索結果
     *  @return array   ナビゲーション情報を格納した配列
     */
    protected function _getNavigation($total, &$list)
    {
        $nav = array();
        $nav['offset'] = $this->offset;
        $nav['from'] = $this->offset + 1;
        if ($total == 0) {
            $nav['from'] = 0;
        }
        $nav['to'] = $this->offset + count($list);
        $nav['total'] = $total;
        if ($this->offset > 0) {
            $prev_offset = $this->offset - $this->count;
            if ($prev_offset < 0) {
                $prev_offset = 0;
            }
            $nav['prev_offset'] = $prev_offset;
        }
        if ($this->offset + $this->count < $total) {
            $next_offset = $this->offset + count($list);
            $nav['next_offset'] = $next_offset;
        }
        $nav['direct_link_list'] = Ethna_Util::getDirectLinkList($total, $this->offset, $this->count);

        return $nav;
    }

    /**
     *  検索項目を生成する
     *
     *  @access protected
     */
    protected function _setQueryOption()
    {
    }

    /**
     *  検索内容を格納したGET引数を生成する
     *
     *  @access private
     *  @param  array   $search_list    検索対象一覧
     *  @return string  検索内容を格納したGET引数
     */
    protected function _getQueryParameter()
    {
        $query = "";

        foreach ($this->search_list as $key) {
            $value = $this->af->get("s_$key");
            if (is_array($value)) {
                foreach ($value as $v) {
                    $query .= "&s_$key" . "[]=" . urlencode($v);
                }
            } else {
                $query .= "&s_$key=" . urlencode($value);
            }
        }

        return $query;
    }
}
// }}}
