<?php
/**
 *  smarty modifier:count()
 *
 *  count()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("array", array(1, 2, 3));
 *
 *  {$array|@count}
 *  </code>
 *  <code>
 *  3
 *  </code>
 *
 *  @param  array   $array  対象となる配列
 *  @return int     配列の要素数
 */
function smarty_modifier_count($array)
{
    return count($array);
}
