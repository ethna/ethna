<?php
/**
 *  smarty function:セレクトフィールド生成
 *
 *  sample:
 *  <code>
 *  $smarty->assign('hoge',
 *                   array(
 *                       '1' => array('name' => 'foo'),
 *                       '2' => array('name' => 'bar')
 *                   )
 *  );
 *  {select list=$hoge name="hoge" value="1" empty="-- please select --"}
 *  </code>
 *  <code>
 *  <select name="hoge">
 *    <option value="">-- please select --</option>
 *    <option value="1" selected="selected">foo</option>
 *    <option value="2">bar</option>
 *  </select>
 *  </code>
 *
 *  @param  array   $list   選択肢一覧
 *  @param  string  $name   フォーム項目名
 *  @param  string  $value  セレクトボックスに渡されたフォーム値
 *  @param  string  $empty  空エントリ(「---選択して下さい---」等)
 *  @deprecated
 */
function smarty_function_select($params, &$smarty)
{
    extract($params);

    //  empty="...." を加えると、無条件に追加される
    //  ない場合は追加されない
    print "<select name=\"$name\">\n";
    if ($empty) {
        printf("<option value=\"\">%s</option>\n", $empty);
    }
    foreach ($list as $id => $elt) {
        //    標準に合わせる
        //    @see http://www.w3.org/TR/html401/interact/forms.html#adef-selected
        //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd
        //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd
        //    @see http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-frameset.dtd
        //    @see http://www.w3.org/TR/xhtml-modularization/abstract_modules.html#s_sformsmodule
        printf("<option value=\"%s\" %s>%s</option>\n",
               $id, $id == $value ? 'selected="selected"' : '', $elt['name']);
    }
    print "</select>\n";
}

