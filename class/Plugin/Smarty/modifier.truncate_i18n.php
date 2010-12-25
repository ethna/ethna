<?php
/**
 *  smarty modifier:文字列切り詰め処理(i18n対応)
 *
 *  sample:
 *  <code>
 *  {"日本語です"|truncate_i18n:7:"..."}
 *  </code>
 *  <code>
 *  日本...
 *  </code>
 *
 *  @param  int     $len        最大文字幅
 *  @param  string  $postfix    末尾に付加する文字列
 */
function smarty_modifier_truncate_i18n($string, $len = 80, $postfix = "...")
{
    $ctl = Ethna_Controller::getInstance();
    $client_enc = $ctl->getClientEncoding();

    //    いわゆる半角を単位にしてwrapする位置を測るため、いったん
    //    EUC_JP に変換する
    $euc_string = mb_convert_encoding($string, 'EUC_JP', $client_enc);

    $r = mb_strimwidth($euc_string, 0, $len, $postfix, 'EUC_JP');

    //    最後に、クライアントエンコーディングに変換
    $r = mb_convert_encoding($r, $client_enc, 'EUC_JP');

    return $r;
}

