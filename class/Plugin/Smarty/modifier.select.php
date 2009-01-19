<?php
/**
 *  smarty modifier:セレクトボックス用フィルタ
 *
 *  単純なセレクトボックスの場合はsmarty関数"select"を利用することで
 *  タグを省略可能
 *
 *  sample:
 *  <code>
 *  $smarty->assign("form", 1);
 *
 *  <option value="1" {$form|select:"1"}>foo</option>
 *  <option value="2" {$form|select:"2"}>bar</option>
 *  </code>
 *  <code>
 *  <option value="1" selected="selected">foo</option>
 *  <option value="2" >bar</option>
 *  </code>
 *
 *  @param  string  $string セレクトボックスに渡されたフォーム値
 *  @param  string  $value  <option>タグに指定されている値
 *  @return string  $stringが$valueにマッチする場合は"selected"
 */
function smarty_modifier_select($string, $value)
{
    //    標準に合わせる
    //    @see http://www.w3.org/TR/html401/interact/forms.html#adef-selected
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd
    //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-frameset.dtd
    //    @see http://www.w3.org/TR/xhtml-modularization/abstract_modules.html#s_sformsmodule
    if ($string == $value) {
        return 'selected="selected"';
    }
}

