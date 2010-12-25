<?php
/**
 *  smarty modifier:文字列のwordwrap処理
 *
 *  sample:
 *  <code>
 *  {"あいうaえaおaかきaaaくけこ"|wordwrap_i18n:8}
 *  </code>
 *  <code>
 *  あいうa
 *  えaおaか
 *  きaaaく
 *  けこ
 *  </code>
 *
 *  @param  string  $string wordwrapする文字列
 *  @param  string  $break  改行文字
 *  @param  int     $width  wordwrap幅(半角$width文字でwordwrapする)
 *  @param  int     $indent インデント幅(半角$indent文字)
 *                          数値を指定するが、はじめの行はインデントされない
 *  @return string  wordwrap処理された文字列
 */
function smarty_modifier_wordwrap_i18n($string, $width, $break = "\n", $indent = 0)
{
    $ctl = Ethna_Controller::getInstance();
    $client_enc = $ctl->getClientEncoding();

    //    いわゆる半角を単位にしてwrapする位置を測るため、いったん
    //    EUC_JP に変換する
    $euc_string = mb_convert_encoding($string, 'EUC_JP', $client_enc);

    $r = "";
    $i = "$break" . str_repeat(" ", $indent);
    $tmp = $euc_string;
    do {
        $n = strpos($tmp, $break);
        if ($n !== false && $n < $width) {
            $s = substr($tmp, 0, $n);
            $r .= $s . $i;
            $tmp = substr($tmp, strlen($s) + strlen($break));
            continue;
        }

        $s = mb_strimwidth($tmp, 0, $width, "", 'EUC_JP');
        $tmp = substr($tmp, strlen($s));
        $r .= $s . ((strlen($tmp) > 0)?$i:'');
    } while (strlen($tmp) > 0);

    //    最後に、クライアントエンコーディングに変換
    $r = mb_convert_encoding($r, $client_enc, 'EUC_JP');

    return $r;
}

