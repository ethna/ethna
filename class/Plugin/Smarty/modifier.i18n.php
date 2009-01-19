<?php
/**
 *  smarty modifier:i18nフィルタ
 *
 *  sample:
 *  <code>
 *  {"english"|i18n}
 *  </code>
 *  <code>
 *  英語
 *  </code>
 *
 *  @param  string  $string i18n処理対象の文字列
 *  @return string  ロケールに対応したメッセージ
 */
function smarty_modifier_i18n($string)
{
    $c =& Ethna_Controller::getInstance();

    $i18n =& $c->getI18N();

    return $i18n->get($string);
}

