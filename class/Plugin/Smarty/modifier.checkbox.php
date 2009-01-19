<?php
/**
 *  smarty modifier:チェックボックス用フィルタ
 *
 *  sample:
 *  <code>
 *  <input type="checkbox" name="test" {""|checkbox}>
 *  <input type="checkbox" name="test" {"1"|checkbox}>
 *  </code>
 *  <code>
 *  <input type="checkbox" name="test">
 *  <input type="checkbox" name="test" checked="checked">
 *  </code>
 *
 *  @param  string  $string チェックボックスに渡されたフォーム値(スカラーのみ)
 *  @return string  $stringが空文字列あるいは0, null, false 以外の場合は"checked"
 */
function smarty_modifier_checkbox($string)
{
    if (is_scalar($string) && $string != "" && $string != "0") {
        return 'checked="checked"';
    }
}

