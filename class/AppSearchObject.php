<?php
// vim: foldmethod=marker
/**
 *  AppSearchObject.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/** アプリケーションオブジェクト検索条件: != */
define('OBJECT_CONDITION_NE', 0);

/** アプリケーションオブジェクト検索条件: == */
define('OBJECT_CONDITION_EQ', 1);

/** アプリケーションオブジェクト検索条件: LIKE */
define('OBJECT_CONDITION_LIKE', 2);

/** アプリケーションオブジェクト検索条件: > */
define('OBJECT_CONDITION_GT', 3);

/** アプリケーションオブジェクト検索条件: < */
define('OBJECT_CONDITION_LT', 4);

/** アプリケーションオブジェクト検索条件: >= */
define('OBJECT_CONDITION_GE', 5);

/** アプリケーションオブジェクト検索条件: <= */
define('OBJECT_CONDITION_LE', 6);

/** アプリケーションオブジェクト検索条件: AND */
define('OBJECT_CONDITION_AND', 7);

/** アプリケーションオブジェクト検索条件: OR */
define('OBJECT_CONDITION_OR', 8);



// {{{ Ethna_AppSearchObject
/**
 *  アプリケーションオブジェクト検索条件クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_AppSearchObject
{
    /**#@+
     *  @access private
     */

    /** @protected    string  検索値 */
    protected $value;

    /** @protected    int     検索条件 */
    protected $condition;

    /**
     *  @protected    array   追加検索条件を保持したEthna_AppSearchObjectの一覧
     */
    protected $object_list = array();

    /**#@-*/


    /**
     *  Ethna_AppSearchObjectのコンストラクタ
     *
     *  @access public
     *  @param  string  $value      検索値
     *  @param  int     $condition  検索条件(OBJECT_CONDITION_NE,...)
     */
    public function __construct($value, $condition)
    {
        $this->value = $value;
        $this->condition = $condition;
    }

    /**
     *  検索条件をOR/ANDで追加する
     *
     *  @access public
     *  @param  string                          $name           検索対象カラム名
     *  @param  object  Ethna_AppSearchObject   $search_object  追加する検索条件
     *  @param  int                             $condition      追加条件(OR/AND)
     */
    function addObject($name, $search_object, $condition)
    {
        $tmp = array();
        $tmp['name'] = $name;
        $tmp['object'] = $search_object;
        $tmp['condition'] = $condition;
        $this->object_list[] = $tmp;
    }

    /**
     *  指定されたフィールドが検索対象となっているかどうかを返す
     *
     *  @access public
     */
    function isTarget($field)
    {
        foreach ($this->object_list as $object) {
            if ($object['name'] == $field) {
                return true;
            }
            if (is_object($object['object'])) {
                $r = $object['object']->isTarget($field);
                if ($r) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  検索条件SQL文を返す
     *
     *  @access public
     *  @param  string  検索対象カラム名
     *  @return SQL文
     */
    function toString($column)
    {
        $condition = "(";
        $tmp_value = $this->value;
        Ethna_AppSQL::escapeSQL($tmp_value);
        $condition .= Ethna_AppSQL::getCondition("$column", $tmp_value, $this->condition);

        foreach ($this->object_list as $elt) {
            if ($elt['condition'] == OBJECT_CONDITION_OR) {
                $condition .= " OR ";
            } else {
                $condition .= " AND ";
            }
            $condition .= $elt['object']->toString($elt['name']);
        }

        return $condition . ")";
    }
}
// }}}
