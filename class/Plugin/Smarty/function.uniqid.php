<?php
/**
 *  smarty function:ユニークIDを生成する(double postチェック用)
 *
 *  sample:
 *  <code>
 *  {uniqid}
 *  </code>
 *  <code>
 *  <input type="hidden" name="uniqid" value="a0f24f75e...e48864d3e">
 *  </code>
 *
 *  @param  string  $type   表示タイプ("get" or "post"−デフォルト="post")
 *  @see    isDuplicatePost
 */
function smarty_function_uniqid($params, &$smarty)
{
    $uniqid = Ethna_Util::getRandom();
    if (isset($params['type']) && $params['type'] == 'get') {
        return "uniqid=$uniqid";
    } else {
        return "<input type=\"hidden\" name=\"uniqid\" value=\"$uniqid\" />\n";
    }
}

