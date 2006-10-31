<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator
/**
 *  スケルトン生成プラグイン
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator
{
    /** @var    object  Ethna_Controller    スケルトン生成に使うコントローラ */
    var $ctl;

    /**
     *  スケルトンファイルにマクロを適用してファイルを生成する
     *
     *  ethnaライブラリのディレクトリ構造が変更されていないことが前提
     *  となっている点に注意
     *
     *  @access private
     *  @param  string  $skel       スケルトンファイル
     *  @param  string  $entity     生成ファイル名
     *  @param  array   $macro      置換マクロ
     *  @param  bool    $overwrite  上書きフラグ
     *  @return bool    true:正常終了 false:エラー
     */
    function _generateFile($skel, $entity, $macro, $overwrite = false)
    {
        $base = null;

        if ($overwrite === false && file_exists($entity)) {
            printf("file [%s] already exists -> skip\n", $entity);
            return true;
        }
        if (is_object($this->ctl)) {
            $base = $this->ctl->getBasedir();
            if (file_exists("$base/skel/$skel") == false) {
                $base = null;
            }
        }
        if (is_null($base)) {
            $base = dirname(dirname(dirname(__FILE__)));
        }

        $rfp = fopen("$base/skel/$skel", "r");
        if ($rfp == null) {
            return false;
        }
        $wfp = fopen($entity, "w");
        if ($wfp == null) {
            fclose($rfp);
            return false;
        }

        for (;;) {
            $s = fread($rfp, 4096);
            if (strlen($s) == 0) {
                break;
            }

            foreach ($macro as $k => $v) {
                $s = preg_replace("/{\\\$$k}/", $v, $s);
            }
            fwrite($wfp, $s);
        }

        fclose($wfp);
        fclose($rfp);

        $st = stat("$base/skel/$skel");
        if (chmod($entity, $st[2]) == false) {
            return false;
        }

        printf("file generated [%s -> %s]\n", $skel, $entity);

        return true;
    }

    /**
     *  ユーザ定義のマクロを設定する(~/.ethna)
     *
     *  @access private
     */
    function _getUserMacro()
    {
        if (isset($_SERVER['USERPROFILE']) && is_dir($_SERVER['USERPROFILE'])) {
            $home = $_SERVER['USERPROFILE'];
        } else {
            $home = $_SERVER['HOME'];
        }

        if (is_file("$home/.ethna") == false) {
            return array();
        }

        $user_macro = parse_ini_file("$home/.ethna");
        return $user_macro;
    }
}
// }}}
?>
