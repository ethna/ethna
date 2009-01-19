<?php
/**
 *  smarty modifier:strftime()
 *
 *  strftime()関数のwrapper
 *
 *  sample:
 *  <code>
 *  {"2004/01/01 01:01:01"|strftime:"%Y年%m月%d日"}
 *  </code>
 *  <code>
 *  2004年01月01日
 *  </code>
 *
 *  @param  string  $string フォーマット対象文字列
 *  @param  string  $format 書式指定文字列(strftime()関数参照)
 *  @return string  フォーマット済み文字列
 */
function smarty_modifier_strftime($string, $format)
{
    if ($string === "" || $string == null) {
        return "";
    }
    return strftime($format, strtotime($string));
}


