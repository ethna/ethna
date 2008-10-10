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
   
    /** @var    boolean  既存ファイルが存在した場合にtrue */ 
    var $file_exists;

    /** @var    string   実行時のUnix Time(ファイル名生成用) */ 
    var $time;

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
        $this->time = time();
        $this->locale = $locale;
        $this->use_gettext = $use_gettext;

        $outfile_path = $this->_get_output_file();

        //
        //  既存ファイルが存在した場合は、以下の動きをする
        //
        //  1. Ethna 組み込みのカタログの場合、既存のiniファイル
        //  の中身を抽出し、既存の翻訳を可能な限りマージする
        //  2. gettext 利用の場合は、新たにファイルを作らせ、
        //  既存翻訳とのマージは msgmergeプログラムを使わせる 
        //
        if ($this->file_exists) {
            $msg = ($this->use_gettext)
                 ? ("[NOTICE]: Message catalog file already exists! "
                  . "CREATING NEW FILE ...\n"
                  . "You can run msgmerge program to merge translation.\n"
                   )
                 : ("[NOTICE]: Message catalog file already exists!\n"
                  . "This is overwritten and existing translation is merged automatically.\n");
             print "\n-------------------------------\n"
                 . $msg
                 . "-------------------------------\n\n"; 
        }

        // app ディレクトリとテンプレートディレクトリを
        // 再帰的に走査する。ユーザから指定があればそれも走査
        $app_dir = $this->ctl->getDirectory('app');
        $template_dir = $this->ctl->getDirectory('template');
        $scan_dir = array(
            $app_dir, "${template_dir}/${locale}",
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
        $new_filename = NULL;

        $outfile_path = "${locale_dir}/"
                      . $this->locale
                      . "/LC_MESSAGES/$filename";

        $this->file_exists = (file_exists($outfile_path));
        if ($this->file_exists && $this->use_gettext) {
            $new_filename = $this->locale . '_' . $this->time . ".${ext}";
            $outfile_path = "${locale_dir}/"
                          . $this->locale
                          . "/LC_MESSAGES/$new_filename";
        }

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
     *  NOTICE: このメソッドは、指定ファイルがPHPスクリプト
     *          (テンプレートファイル)として正しいものかどう
     *          かはチェックしません。 
     *
     *  @access protected
     *  @param  string  $file     走査対象ファイル
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeFile($file)
    {
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

        //  アクションディレクトリは特別扱いするため、それ
        //  を取得
        $action_dir = $this->ctl->getActionDir(GATEWAY_WWW);

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
                    $this->tokens[$file_path][] = array(
                                                      'token_str' => $token_str,
                                                      'linenum' => $token_linenum,
                                                      'translation' => ''
                                                  );
                    $in_et_function = false;
                    continue;
                }
            }
        }

        //  アクションスクリプト の場合は、
        //  ActionForm の $form メンバ解析
        $php_ext = $this->ctl->getExt('php');
        $action_dir_regex = $action_dir;
        if (ETHNA_OS_WINDOWS) {
            $action_dir_regex = str_replace('\\', '\\\\', $action_dir);
            $action_dir_regex = str_replace('/', '\\\\', $action_dir_regex);
        }
        if (preg_match("#$action_dir_regex#", $file_path)
        && !preg_match("#.*Test\.${php_ext}$#", $file_path)) {
            $this->_analyzeActionForm($file_path); 
        }

        //  Ethna組み込みのメッセージカタログであれば翻訳をマージする
        $this->_mergeEthnaMessageCatalog();

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
     *  @param  string  $file_path  走査対象ファイル
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeActionForm($file_path)
    {
        //   アクションスクリプトのトークンを取得 
        $tokens = token_get_all(
                      file_get_contents($file_path)
                  );

        //   クラスのトークンのみを取り出す
        $class_names = array();
        $class_started = false;
        for ($i = 0; $i < count($tokens); $i++) { 
            $token = $tokens[$i];
            if (is_array($token)) {
                $token_name = array_shift($token);
                $token_str = array_shift($token);
                
                if ($token_name == T_CLASS) {  //  クラス定義開始
                    $class_started = true;
                    continue;
                }
                //    T_CLASS の直後に来た T_STRING をクラス名と見做す
                if ($class_started === true && $token_name == T_STRING) {
                    $class_started = false;
                    $class_names[] = $token_str;
                } 
            }
        }

        //  アクションフォームのクラス名を特定
        $af_classname = NULL;
        foreach ($class_names as $name) {
            $action_name = $this->ctl->actionFormToName($name);
            if (!empty($action_name)) {
                $af_classname = $name;
                break;
            }
        }

        //  特定したクラスをインスタンス化し、フォーム定義を解析する
        printf("    Analyzing ActionForm class ... %s\n", $af_classname);
        require_once $file_path;
        $af = new $af_classname($this->ctl);
        $form_def = $af->getDef();
        $translatable_code = array('name', 'required_error', 'type_error', 'min_error',
                                   'max_error', 'regexp_error'
                             );
        foreach ($form_def as $key => $def) {
            //    対象となるのは name, *_error
            //    但し、定義されていた場合のみ対象にする
            //    @see http://ethna.jp/ethna-document-dev_guide-form-message.html 
            foreach ($translatable_code as $code) {
                if (array_key_exists($code, $def)) {
                    $token_str = $def[$code]; 
                    $this->tokens[$file_path][] = array(
                                                      'token_str' => $token_str,
                                                      'linenum' => false, // 行番号は取得しない
                                                      'translation' => ''
                                                  );
                }
            }
        } 
    }

    /**
     *  指定されたテンプレートファイルを調べ、メッセージ処理関数
     *  の呼び出し箇所を取得します。
     *
     *  @access protected
     *  @param  string  $file    走査対象ファイル 
     *  @return true|Ethna_Error true:成功 Ethna_Error:失敗
     */
    function _analyzeTemplate($file)
    {
        //  デフォルトはSmartyのテンプレートと看做す
        $renderer =& $this->ctl->getRenderer();
        $engine =& $renderer->getEngine();
        $engine_name = get_class($engine);
        if (strncasecmp('Smarty', $engine_name, 6) !== 0) {
            return Ethna::raiseError(
                       "You seems to use template engine other than Smarty ... : $engine_name"
                   ); 
        }

        printf("Analyzing Template file ... %s\n", $file);

        //  use smarty internal function :)
        $compile_path = $engine->_get_compile_path($file);        
        $compile_result = NULL;
        if ($engine->_is_compiled($file, $compile_path)
         || $engine->_compile_resource($file, $compile_path)) {
            $compile_result = file_get_contents($compile_path);
        }

        if (empty($compile_result)) {
            return Ethna::raiseError(
                       "could not compile template file : $file"
                   ); 
        }

        //  コンパイル済みのテンプレートを解析する
        $tokens = token_get_all($compile_result);

        for ($i = 0; $i < count($tokens); $i++) { 
            $token = $tokens[$i];
            if (is_array($token)) {
                $token_name = array_shift($token);
                $token_str = array_shift($token);
                
                if ($token_name == T_STRING
                 && strcmp($token_str, 'smarty_modifier_i18n') === 0) {
                    $i18n_str = $this->_find_template_i18n($tokens, $i);
                    if (!empty($i18n_str)) {
                        $i18n_str = substr($i18n_str, 1);     // 最初のクォートを除く
                        $i18n_str = substr($i18n_str, 0, -1); // 最後のクォートを除く
                        $this->tokens[$file][] = array(
                                                      'token_str' => $i18n_str,
                                                      'linenum' => false,
                                                      'translation' => '',
                                                 );
                    }
                }
            }
        }
    }

    /**
     *  テンプレートのトークンを逆順に走査し、
     *  翻訳トークンを取得します。
     *
     *  @param $tokens 解析対象トークン
     *  @param $index  インデックス
     *  @access private 
     */
    function _find_template_i18n($tokens, $index)
    {
        for ($j = $index; $j > 0; $j--) {
            $tmp_token = $tokens[$j];

            if (is_array($tmp_token)) {
                $tmp_token_name = array_shift($tmp_token);
                $tmp_token_str = array_shift($tmp_token);
                if ($tmp_token_name == T_CONSTANT_ENCAPSED_STRING 
                 && !preg_match('#^["\']i18n["\']$#', $tmp_token_str)) {
                    $prev_token = $tokens[$j - 1];
                    if (!is_array($prev_token) && $prev_token == '=') {
                        return $tmp_token_str;
                    } 
                }
            }
        }
        return NULL;
    }

    /**
     *  Ethna組み込みのメッセージカタログファイルを、上書き
     *  する場合にマージします。
     *
     *  @access private
     */
    function _mergeEthnaMessageCatalog()
    {
        if (!($this->file_exists && !$this->use_gettext)) {
            return;
        }
        $outfile_path = $this->_get_output_file();

        $i18n = $this->ctl->getI18N();
        $existing_catalog = $i18n->parseEthnaMsgCatalog($outfile_path);

        foreach ($this->tokens as $file_path => $tokens) {
            for ($i = 0; $i < count($tokens); $i++) {
                $token = $tokens[$i];
                $token_str = $token['token_str'];
                if (array_key_exists($token_str, $existing_catalog)) {
                    $this->tokens[$file_path][$i]['translation'] = $existing_catalog[$token_str];
                }
            }
        }
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
                $token_str = $token['token_str'];
                $token_line = $token['linenum'];
                $token_line = ($token_line !== false) ? ":${token_line}" : '';
                $translation = $token['translation'];

                if ($this->use_gettext) {
                    $contents .= (
                        "#: ${file_path}${token_line}\n"
                      . "msgid \"${token_str}\"\n"
                      . "msgstr \"${translation}\"\n\n"
                    ); 
                } else {
                    if ($is_first_loop === false) {
                        $contents .= "\n; ${file_path}\n";
                        $is_first_loop = true;
                    }
                    $contents .= "\"${token_str}\" = \"${translation}\"\n";
                }
            }
        } 

        //  finally write.
        $outfile_dir = dirname($outfile_path);
        if (!is_dir($outfile_dir)) {
            Ethna_Util::mkdir($outfile_dir, 0755);
        }
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
