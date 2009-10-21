<?php
// vim: foldmethod=marker
/**
 *  Util.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_SOAP_Util
/**
 *  SOAPユーティリティクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_SOAP_Util
{
    /**
     *  型定義がオブジェクト型の配列かどうかを返す
     *
     *  @access public
     *  @param  array   $def    型定義
     *  @return bool    true:オブジェクト型配列 false:それ以外の型
     *  @static
     */
    function isArrayOfObject($def)
    {
        if (is_array($def) == false) {
            return false;
        }
        $keys = array_keys($def);
        if (count($keys) == 1 && is_array($def[$keys[0]])) {
            return true;
        }
        return false;
    }

    /**
     *  型定義がスカラー値の配列かどうかを返す
     *
     *  @access public
     *  @param  array   $def    型定義
     *  @return bool    true:スカラー型配列 false:それ以外の型
     *  @static
     */
    function isArrayOfScalar($def)
    {
        if (is_array($def) == false) {
            return false;
        }
        $keys = array_keys($def);
        if (count($keys) == 1 && is_array($def[$keys[0]]) == false) {
            return true;
        }
    }

    /**
     *  スカラー値の型名を返す
     *
     *  @access public
     *  @param  array   $def    型定義
     *  @return string  型名
     *  @static
     */
    function getScalarTypeName($def)
    {
        $name = null;
        switch ($def) {
        case VAR_TYPE_STRING:
            $name = "string";
            break;
        case VAR_TYPE_INT:
            $name = "int";
            break;
        case VAR_TYPE_FLOAT:
            $name = "float";
            break;
        case VAR_TYPE_DATETIME:
            $name = "datetime";
            break;
        case VAR_TYPE_BOOLEAN:
            $name = "boolean";
            break;
        }
        return $name;
    }

    /**
     *  配列の型名を返す
     *
     *  @access public
     *  @param  array   $def    型定義
     *  @return string  型名
     *  @static
     */
    function getArrayTypeName($def)
    {
        $name = null;
        switch ($def) {
        case VAR_TYPE_STRING:
            $name = "ArrayOfString";
            break;
        case VAR_TYPE_INT:
            $name = "ArrayOfInt";
            break;
        case VAR_TYPE_FLOAT:
            $name = "ArrayOfFloat";
            break;
        case VAR_TYPE_DATETIME:
            $name = "ArrayOfDatetime";
            break;
        case VAR_TYPE_BOOLEAN:
            $name = "ArrayOfBoolean";
            break;
        }
        return $name;
    }

    /**
     *  戻り値型定義を正規化する
     *
     *  @access public
     *  @param  array   $retval 戻り値型定義
     *  @static
     */
    function fixRetval(&$retval)
    {
        $retval['errorcode'] = VAR_TYPE_INT;
        $retval['errormessage'] = VAR_TYPE_STRING;
    }
}
// }}}
