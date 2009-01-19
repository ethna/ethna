<?php
/**
 *  smarty modifier:filter()
 *
 *  指定された連想配列のうち$keyで指定された要素のみを配列に再構成する
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 2, "bar" => 5),
 *      array("foo" => 3, "bar" => 6),
 *  ));
 *
 *  {$array|@filter:"foo"|@join:","}
 *  </code>
 *  <code>
 *  1,2,3
 *  </code>
 *  
 *  @param  array   $array  filter対象となる配列
 *  @param  string  $key    抜き出して配列を構成する連想配列のキー
 *  @return array   再構成された配列
 */
function smarty_modifier_filter($array, $key)
{
    if (is_array($array) == false) {
        return $array;
    }
    $tmp = array();
    foreach ($array as $v) {
        if (isset($v[$key]) == false) {
            continue;
        }
        $tmp[] = $v[$key];
    }
    return $tmp;
}


