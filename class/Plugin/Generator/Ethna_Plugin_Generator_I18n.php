<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Generator_I18n.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com> 
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Generator_I18n
/**
 *  i18n 向け、メッセージカタログ生成クラスのスーパークラス
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com> 
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Generator_I18n extends Ethna_Plugin_Generator
{
    /**#@+
     *  @access protected 
     */

    /** @var    array  解析済みトークン  */ 
    var $tokens = array();

    /** @var    string   ロケール名  */ 
    var $locale;

    /** @var    boolean  gettext利用フラグ  */ 
    var $use_gettext;
   
    /**
     *  プロジェクトのメッセージカタログを生成する
     *
     *  @access public
     *  @param  string  $locale         生成するカタログのロケール
     *  @param  int     $use_gettext    gettext 使用フラグ
     *                                  true ならgettext のカタログ生成
     *                                  false ならEthna組み込みのカタログ生成
     *  @param  array   $ext_dirs       走査する追加のディレクトリの配列
     *  @return true|Ethna_Error        true:成功 Ethna_Error:失敗
     */
    function &generate($locale, $use_gettext, $ext_dirs = array())
    {
        $this->locale = $locale;
        $this->use_gettext = $use_gettext;
        $outfile_path = $this->_get_output_file();
 
        // ファイルの存在チェック
        if (file_exists($outfile_path)) {
            printf('Message Catalog file already exists! overwrite?(y/n) : ');
            flush();
            $fp = fopen("php://stdin", "r");
            $r = trim(fgets($fp, 128));
            fclose($fp);
            if (strtolower($r) != 'y') {
                return Ethna::raiseError('aborted by user');
            }
        }        

        // app ディレクトリとテンプレートディレクトリを
        // 再帰的に走査する。ユーザから指定があればそれも走査
        $app_dir = $this->ctl->getDirectory('app');
        $template_dir = $this->ctl->getDirectory('template');
        $scan_dir = array(
            $app_dir, $template_dir,
        );
        $scan_dir = array_merge($scan_dir, $ext_dirs);

        //  ディレクトリを走査
        foreach ($scan_dir as $dir) {
            if (is_dir($dir) === false) {
                Ethna::raiseNotice("$dir is not Directory.", E_GENERAL);
                continue;
            }
            $r = $this->_analyzeDirectory($dir);
            if (Ethna::isError($r)) {
                return $r;
            }
        } 

        //  解析済みトークンを元に、カタログファイルを生成
        $r = $this->_generateFile();
        if (Ethna::isError($r)) {
            return $r;
        }

        $true = true;
        return $true;
    }

    /**
     *  出力ファイル名を取得します。 
     *
     *  @access private
     *  @return string  出力ファイル名
     */
    function _get_output_file()
    {
        $locale_dir = $this->ctl->getDirectory('locale');
        $ext = ($this->use_gettext) ? 'po' : 'ini';
        $filename = $this->locale . ".${ext}";
        $outfile_path = "${locale_dir}/"
                      . $this->locale
                      . "/LC_MESSAGES/$filename";

        return $outfile_path;
    }
 
    /**
     *  指定されたディレクトリを再帰的に走査します。
     *
     *  @access protected
     *  @param  string  $dir     走査対象ディレクトリ 
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeDirectory($dir)
    {
        $dh = opendir($dir);
        if ($dh == false) {
            return Ethna::raiseWarning(
                       "unable to open Directory: $dir", E_GENERAL
                   );
        }

        //  走査対象はテンプレートとPHPスクリプト 
        $php_ext = $this->ctl->getExt('php');
        $tpl_ext = $this->ctl->getExt('tpl');
        $r = NULL;
    
        //  ディレクトリなら再帰的に走査
        //  ファイルならトークンを解析する
        while(($file = readdir($dh)) !== false) {
            if (is_dir("$dir/$file")) {
                if (strpos($file, '.') !== 0) {  // 隠しファイルは対象外
                   $r = $this->_analyzeDirectory("$dir/$file");
                }
            } else {
                if (preg_match("#\.${php_ext}\$#i", $file) > 0) {
                    $r = $this->_analyzeFile("$dir/$file");
                }
                if (preg_match("#\.${tpl_ext}\$#i", $file) > 0) {
                    $r = $this->_analyzeTemplate("$dir/$file");
                }
            }
            if (Ethna::isError($r)) {
                return $r;
            }
        }

        closedir($dh);
        return true;
    }

    /**
     *  指定されたPHPスクリプトを調べ、メッセージ処理関数の呼び出し
     *  箇所を取得します。
     *
     *  NOTICE: このメソッドは、指定ファイルがPHPスクリプトとして
     *          正しいものかどうかはチェックしません。 
     *
     *  @access protected
     *  @param  string  $file     走査対象ファイル
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeFile($file)
    {
        //  トークンを取得する
        $file_path = realpath($file);

        printf("Analyzing file ... %s\n", $file);

        //  ファイルを開けないならエラー
        $fp = @fopen($file_path, 'r');
        if ($fp === false) {
            return Ethna::raiseWarning(
                       "unable to open file: $file", E_GENERAL
                   );
        }
        fclose($fp);

        //  トークンを全て取得。
        $file_tokens = token_get_all(
                           file_get_contents($file_path)
                       );
        $token_num = count($file_tokens);
        $in_et_function = false;   

        //  トークンを走査し、関数呼び出しを解析する
        for ($i = 0; $i < $token_num; $i++) {

            $token = $file_tokens[$i];
            $token_idx = false;
            $token_str = NULL;
            $token_linenum = false;

            //   面倒を見るのは、トークンの場合のみ
            //   単純な文字列は読み飛ばす
            if (is_array($token)) {
                $token_idx = array_shift($token);
                $token_str = array_shift($token);

                //  PHP 5.2.2 以降のみ行番号を取得可能
                //  @see http://www.php.net/token_get_all
                if (version_compare(PHP_VERSION, '5.2.2') >= 0) {
                    $token_linenum = array_shift($token);
                } 
                //  i18n 呼び出し関数の場合、フラグを立てる
                if ($token_idx == T_STRING && $token_str == '_et') {
                    $in_et_function = true;
                    continue; 
                }
                //  i18n 呼び出しの後、定数文字列が来たら、
                //  それを引数と看做す。PHPの文法的にvalid
                //  か否かはこのルーチンでは面倒を見ない
                if ($in_et_function == true
                 && $token_idx == T_CONSTANT_ENCAPSED_STRING) {
                    $token_str = substr($token_str, 1);     // 最初のクォートを除く
                    $token_str = substr($token_str, 0, -1); // 最後のクォートを除く
                    $this->tokens[$file_path][] = array($token_str, $token_linenum);
                    $in_et_function = false;
                    continue;
                }

                //
                //  TODO: ActionForm の $form メンバ解析
                //
            }
        }

        return true; 
    }

    /**
     *  指定されたテンプレートファイルを調べ、メッセージ処理関数
     *  の呼び出し箇所を取得します。各テンプレートの実装に応じて
     *  このメソッドを実装してください。 
     *
     *  @access protected
     *  @param  string  $file    走査対象ファイル 
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeTemplate($file)
    {
        //  TODO: you should override this method.
    }

    /**
     *  解析済みのメッセージ処理関数の情報を元に、カタログファイ
     *  ルを生成します。 生成先は以下のパスになる。
     *  [appid]/[locale_dir]/[locale_name]/LC_MESSAGES/[locale_name].[ini|po]
     *
     *  @access protected 
     *  @param  string  $locale         生成するカタログのロケール
     *  @param  int     $use_gettext    gettext 使用フラグ
     *                                  true ならgettext のカタログ生成
     *                                  false ならEthna組み込みのカタログ生成
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _generateFile()
    {
        $outfile_path = $this->_get_output_file();

        $skel = ($this->use_gettext)
              ? 'locale/skel.msg.po'
              : 'locale/skel.msg.ini';
        $resolved = $this->_resolveSkelfile($skel);
        if ($resolved === false) {
            return Ethna::raiseError("skelton file [%s] not found.\n", $skel);
        } else {
            $skel = $resolved;
        }

        $contents = file_get_contents($skel);
        $macro['project_id'] = $this->ctl->getAppId();
        $macro['locale_name'] = $this->locale;
        $macro['now_date'] = strftime('%Y-%m-%d %H:%M%z');
        foreach ($macro as $k => $v) {
            $contents = preg_replace("/{\\\$$k}/", $v, $contents);
        }

        //  generate file contents
        foreach ($this->tokens as $file_path => $tokens) {
            $is_first_loop = false;
            foreach ($tokens as $token) {
                $token_str = array_shift($token);
                $token_line = array_shift($token);
                $token_line = ($token_line !== false) ? ":${token_line}" : '';

                if ($this->use_gettext) {
                    $contents .= (
                        "#: ${file_path}${token_line}\n"
                      . "msgid \"${token_str}\"\n"
                      . "msgstr \"\"\n\n"
                    ); 
                } else {
                    if ($is_first_loop === false) {
                        $contents .= "\n; ${file_path}\n";
                        $is_first_loop = true;
                    }
                    $contents .= "\"${token_str}\" = \"\"\n";
                }
            }
        } 

        //  finally write.
        $wfp = @fopen($outfile_path, "w");
        if ($wfp == null) {
            return Ethna::raiseError("unable to open file: $outfile_path");
        }
        if (fwrite($wfp, $contents) === false) {
            fclose($wfp);
            return Ethna::raiseError("unable to write contents to $outfile_path");
        }
        fclose($wfp);
        printf("Message catalog template successfully created [%s]\n", $outfile_path);

        return true;
    }
}
// }}}

?>
