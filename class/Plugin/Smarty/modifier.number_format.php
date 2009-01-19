<?php
/**
 *  smarty modifier:number_format()
 *
 *  number_format()関数のwrapper
 *
 *  sample:
 *  <code>
 *  {"12345"|number_format}
 *  </code>
 *  <code>
 *  12,345
 *  </code>
 *
 *  @param  string  $string フォーマット対象文字列
 *  @return string  フォーマット済み文字列
 */
function smarty_modifier_number_format($string)
{
    if ($string === "" || $string == null) {
        return "";
    }
    return number_format($string);
}


