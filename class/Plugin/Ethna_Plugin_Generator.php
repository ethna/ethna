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
     *  コンストラクタ
     *
     *  @access public
     */
    function Ethna_Plugin_Generator(&$controller, $type, $name)
    {
        // Ethna_Generatorでpluginを取得するときに使ったコントローラ
        // ex, add-projectではEthna_Controller, app-actionではApp_Controller
        $this->ctl =& $controller;
    }

    /**
     *  スケルトンファイルの絶対パスを解決する
     *
     *  @access private
     *  @param  string  $skel   スケルトンファイル
     */
    function _resolveSkelfile($skel)
    {
        $file = realpath($skel);
        if (file_exists($file)) {
            return $file;
        }

        // アプリの skel ディレクトリ
        $base = $this->ctl->getBasedir();
        $file = "$base/skel/$skel";
        if (file_exists($file)) {
            return $file;
        }

        // Ethna本体の skel ディレクトリ
        $base = dirname(dirname(dirname(__FILE__)));
        $file = "$base/skel/$skel";
        if (file_exists($file)) {
            return $file;
        }

        return false;
    }

    /**
     *  スケルトンファイルにマクロを適用してファイルを生成する
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
        if (file_exists($entity)) {
            if ($overwrite === false) {
                printf("file [%s] already exists -> skip\n", $entity);
                return true;
            } else {
                printf("file [%s] already exists, to be overwriten.\n", $entity);
            }
        }

        $resolved = $this->_resolveSkelfile($skel);
        if ($resolved === false) {
            printf("skelton file [%s] not found.\n", $skel);
            return false;
        } else {
            $skel = $resolved;
        }

        $rfp = fopen($skel, "r");
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

        $st = stat($skel);
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
