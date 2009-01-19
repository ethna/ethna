<?php
/**
 *  smarty modifier:join()
 *
 *  join()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(1, 2, 3));
 *
 *  {$array|@join:":"}
 *  </code>
 *  <code>
 *  1:2:3
 *  </code>
 *
 *  @param  array   $array  join対象の配列
 *  @param  string  $glue   連結文字列
 *  @return string  連結後の文字列
 */
function smarty_modifier_join($array, $glue)
{
    if (is_array($array) == false) {
        return $array;
    }
    return implode($glue, $array);
}


