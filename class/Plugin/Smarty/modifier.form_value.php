<?php
/**
 *  smarty modifier:フォーム値出力フィルタ
 *
 *  フォーム名を変数で指定してフォーム値を取得したい場合に使用する
 *
 *  sample:
 *  <code>
 *  $this->af->set('foo', 'bar');
 *  $smarty->assign('key', 'foo');
 *  {$key|form_value}
 *  </code>
 *  <code>
 *  bar
 *  </code>
 *
 *  @param  string  $string フォーム項目名
 *  @return string  フォーム値
 */
function smarty_modifier_form_value($string)
{
    $c =& Ethna_Controller::getInstance();
    $af =& $c->getActionForm();

    $elts = explode(".", $string);
    $r = $af->get($elts[0]);
    for ($i = 1; $i < count($elts); $i++) {
        $r = $r[$elts[$i]];
    }

    return htmlspecialchars($r, ENT_QUOTES);
}

