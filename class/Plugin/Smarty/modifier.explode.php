<?php
/**
 *  smarty modifier:explode()
 *
 *  join()関数のwrapper
 *
 *  sample:
 *  <code>
 *  $smarty->assign("string", "1,2,3");
 *
 *  {$array|explode:","}
 *  </code>
 *  <code>
 *  array(1, 2, 3)
 *  </code>
 *
 *  @param  string  $string  explode対象の文字列
 *  @param  string  $glue   分割文字列
 *  @return array   分割後の配列
 */
function smarty_modifier_explode($string, $glue)
{
    if ($glue == "") {
        return false;
    }

    return explode($glue, $string);
}

