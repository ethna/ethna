<?php
/**
 *  smarty modifier:unique()
 *
 *  unique()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array1", array("a", "a", "b", "a", "b", "c"));
 *  $smarty->assign("array2", array(
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 1, "bar" => 4),
 *      array("foo" => 2, "bar" => 5),
 *      array("foo" => 3, "bar" => 6),
 *      array("foo" => 2, "bar" => 5),
 *  ));
 *
 *  {$array1|@unique|@join:''}
 *  {$array2|@unique:"foo"|@join:''}
 *  </code>
 *  <code>
 *  abc
 *  123
 *  </code>
 *
 *  @param  array   $array  処理対象となる配列
 *  @param  key     $key    処理対象となるキー(nullなら配列要素)
 *  @return array   再構成された配列
 */
function smarty_modifier_unique($array, $key = null)
{
    if (is_array($array) == false) {
        return $array;
    }
    if ($key != null) {
        $tmp = array();
        foreach ($array as $v) {
            if (isset($v[$key]) == false) {
                continue;
            }
            $tmp[] = $v[$key];
        }
        return array_unique($tmp);
    } else {
        return array_unique($array);
    }
}

