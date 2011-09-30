<?php
// vim: foldmethod=marker
/**
 *  Getopt.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    Public Domain
 *  @package    Ethna
 *  @version    $Id$
 */

if (!defined('ETHNA_OPTVALUE_IS_DISABLED')) {
    define('ETHNA_OPTVALUE_IS_DISABLED', 1);
}
if (!defined('ETHNA_OPTVALUE_IS_REQUIRED')) {
    define('ETHNA_OPTVALUE_IS_REQUIRED', 2);
}
if (!defined('ETHNA_OPTVALUE_IS_OPTIONAL')) {
    define('ETHNA_OPTVALUE_IS_OPTIONAL', 3);
}

// {{{ Ethna_Getopt
/**
 *  コマンドラインオプション解釈クラス
 *  PEAR への依存を排除するため、 Console_Getopt クラスを最実装したもの
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 *  @see        http://pear.php.net/manual/en/package.console.console-getopt.php
 */
class Ethna_Getopt
{
    /**
     *  PHP 設定を考慮して、$argv 配列を読みます。
     *  ini ディレクティブ中の register_argc_argv を考慮します。
     *
     *  注意： PHP 4.2.0 以前では、$argv を読むためには
     *         register_globals が ON になっている必要が
     *         ありました。Ethna は この設定がoffであるこ
     *         とを前提にして書かれているため、ここでは考
     *         慮していません。
     *
     *  @return array - オプションとパラメータを含む配列、
     *                  もしくは Ethna_Error
     */
    public function readPHPArgv()
    {
        global $argv;

        if (ini_get('register_argc_argv') == false) {
            return Ethna::raiseError(
                       'Could not read cmd args (register_argc_argv=Off?'
                   );
        }
        return $argv;
    }

    /**
     *  コマンドラインオプションをパースし、結果を返します。
     *
     *  @param array  $args - コマンドライン引数の配列
     *  @param string $shortoptions - 使用できる短いオプション目録を指定します。
     *  @param array  $longoptions - 使用できる長いオプション目録を指定します。
     *
     *  @return array - パースされたオプションと非オプションのコマンドライン引数
     *                  の 2つの要素からなる配列、もしくは Ethna_Error 。
     */
    public function getopt($args, $shortoptions, $longoptions = NULL)
    {
        $shortopts = $this->_parseShortOption($shortoptions);
        if (Ethna::isError($shortopts)) {
            return $shortopts;
        }
        $longopts = $this->_parseLongOption($longoptions);
        if (Ethna::isError($longopts)) {
            return $longopts;
        }

        $parsed_arguments = array();
        $nonparsed_arguments = array();

        for ($pos = 0; $pos < count($args); $pos++) {

             $arg = $args[$pos];
             $next_arg = isset($args[$pos + 1]) ? $args[$pos + 1] : NULL;
             $is_nextarg_is_value = false;
             $required = false;

             if (strpos($arg, '--') === 0) { //  long option

                 //
                 // GNU getopt(3) の場合は、長いオプションは他と重なら
                 // ない限りにおいて短縮できる。たとえば --foo, --fuji
                 // というオプションが定義された場合、 --fo や --fu と
                 // いう短縮指定も可能というものである。
                 //
                 // PEAR の Console_Getopt はこの短縮指定に対応していな
                 // い。よって、それを使用してきた Ethna でもそこまでは
                 // 頑張らないことにする。
                 //

                 //    オプションの値を処理する
                 $lopt = str_replace('--', '', $arg);
                 $opt_and_value = explode('=', $lopt);
                 $opt = $opt_and_value[0];
                 if (!array_key_exists($opt, $longopts)) {
                     return Ethna::raiseError("unrecognized option --$opt");
                 }

                 //  オプションの値を取り出す
                 $required = $longopts[$opt];
                 $value = NULL;
                 if (count($opt_and_value) == 2) {
                     $value = $opt_and_value[1];   // --foo=bar
                 } elseif (strpos('-', $next_arg) !== 0
                        && $required == ETHNA_OPTVALUE_IS_REQUIRED) {
                     if (!empty($next_arg)) {      // --foo bar
                         // 次の $argv を値として解釈
                         // == が設定されていた場合は値として解釈「しない」
                         $value = $next_arg;
                         $pos++;
                     }
                 }

                 //  オプション設定チェック
                 switch ($required) {
                     case ETHNA_OPTVALUE_IS_REQUIRED:
                         if ($value === NULL) {
                             return Ethna::raiseError(
                                        "option --$opt requires an argument"
                                    );
                         }
                         break;
                     case ETHNA_OPTVALUE_IS_DISABLED:
                         if ($value !== NULL) {
                             return Ethna::raiseError(
                                        "option --$opt doesn't allow an argument"
                                    );
                         }
                         break;
                 }

                 //  長いオプションの場合は、-- 付きでオプション名を記録する
                 //  Console_Getopt 互換にするため。
                 $parsed_arguments[] = array("--$opt", $value);

             } elseif (strpos($arg, '-') === 0) {  // short option

                 //
                 // -abcd のように、オプションと値が続けて
                 // 入力される場合がある。この場合どうオプションを解釈
                 // するかの仕様は、GNU getopt(3) の仕様に従う
                 //
                 // 1. abcd を1文字ずつに分解し、a, b, c, d にする
                 //
                 // 2. ':' (値必須) として設定されていた場合は、次の文字以降は
                 //    全て値として解釈する。この場合は次のargvは値として解釈し
                 //    ない。また、次の文字がなく、次の argv が値だった場合は、
                 //    それを値として解釈する
                 // 3. '::'(値が任意) として設定されていた場合も次の文字以降を
                 //    全て値として解釈するが、次の文字がない場合でも次のargvは
                 //    値として解釈「しない」
                 //
                 // 4. 無設定(値設定禁止)の場合は、次の文字もオプションとして解
                 //    釈する。また、次のargvは値として解釈しない
                 //
                 // @see LANG=C; man 3 getopt (日本語マニュアルは見ない方がいいかも)
                 // @see http://www.gnu.org/software/libtool/manual/libc/Using-Getopt.html
                 //
                 //  TODO: ambiguous なオプションを検出できるようにする
                 //

                 $sopt = str_replace('-', '', $arg);
                 $sopt_len = strlen($sopt);

                 for ($sopt_pos = 0; $sopt_pos < $sopt_len; $sopt_pos++) {

                     //  オプションを取り出す
                     $opt = $sopt[$sopt_pos];

                     $value = NULL;
                     $do_next_arg = false;
                     $required = isset($shortopts[$opt]) ? $shortopts[$opt] : NULL;
                     switch ($required) {
                         case ETHNA_OPTVALUE_IS_REQUIRED:
                         case ETHNA_OPTVALUE_IS_OPTIONAL:
                            if ($sopt_len == 1
                             && $required == ETHNA_OPTVALUE_IS_REQUIRED) {
                                if ($next_arg[0] != '-') { // -a hoge
                                    // 次の $argv を値として解釈
                                    // 但し、:: の場合は解釈しない
                                    $value = $next_arg;
                                    $pos++;
                                }
                            } else {
                                //  残りの文字を値として解釈
                                $value = substr($sopt, $sopt_pos + 1);
                                $value = (empty($value)) ? NULL : $value;
                            }
                            if ($required == ETHNA_OPTVALUE_IS_REQUIRED
                              && empty($value)) {
                                 return Ethna::raiseError(
                                            "option -$opt requires an argument"
                                        );
                             }
                             // ':' または '::' が設定された場合は、次の文字
                             // 以降を全て値として解釈するため、次のargv要素に
                             // 解釈を移す
                             $do_next_arg = true;
                             break;
                         case ETHNA_OPTVALUE_IS_DISABLED:
                             //   値を設定禁止にした場合は、値が解釈されなく
                             //   なるので、値設定のチェックは不要
                             break;
                         default:
                             return Ethna::raiseError("unrecognized option -$opt");
                             break;
                     }

                     //  短いオプションの場合は、- を付けないでオプション名を記録する
                     //  Console_Getopt 互換にするため。
                     $parsed_arguments[] = array($opt, $value);

                     if ($do_next_arg === true) {
                         break;
                     }
                 }

             } else {  // オプションとして解釈されない

                 //   non-parsed なオプションに辿り着いた
                 //   ら、それ以降の解釈を停止する
                 //   つまり、それ以降は全て値として解釈する
                 //
                 //   これは POSIX_CORRECT な実装であって
                 //   GNU Getopt な実装ではないが、実際に
                 //   Console_Getopt で行われている以上、
                 //   それに従った実装
                 $nonparsed_arguments = array_slice($args, $pos);
                 break;
             }
        }

        return array($parsed_arguments, $nonparsed_arguments);
    }

    /**
     *  短いオプション目録を解析します。
     *
     *  @param  string $sopts 短いオプション目録
     *  @return array  オプションと引数指定種別の配列
     *                 エラーの場合は Ethna_Error
     *  @access protected
     */
    protected function _parseShortOption($sopts)
    {
        if (empty($sopts)) {
            return array();
        }

        if (!preg_match('/^[A-Za-z:]+$/', $sopts)) {
            return Ethna::raiseError('invalid short options.');
        }

        $analyze_result = array();

        for ($pos = 0; $pos < strlen($sopts); $pos++) {
            $char = $sopts[$pos];
            $next_char = (isset($sopts[$pos + 1]))
                       ? $sopts[$pos + 1]
                       : NULL;
            $next2_char = (isset($sopts[$pos + 2]))
                        ? $sopts[$pos + 2]
                        : NULL;

            if ($char == ':') {
                continue;
            }

            //   $sopts[$pos] is character.
            if ($next_char == ':' && $next2_char == ':') {
                $analyze_result[$char] = ETHNA_OPTVALUE_IS_OPTIONAL; // 値は任意
            } elseif ($next_char == ':' && $next2_char != ':') {
                $analyze_result[$char] = ETHNA_OPTVALUE_IS_REQUIRED; // 値は必須
            } else {
                $analyze_result[$char] = ETHNA_OPTVALUE_IS_DISABLED; // 値は不要
            }
        }

        return $analyze_result;
    }

    /**
     *  長いオプション目録を解析します。
     *
     *  @param  array $lopts 長いオプション目録
     *  @return array オプションと引数指定種別の配列
     *                エラーの場合は Ethna_Error
     *  @access protected
     */
    protected function _parseLongOption($lopts)
    {
        if (empty($lopts)) {
            return array();
        }

        if (!is_array($lopts)) {
            return Ethna::raiseError('invalid long options.');
        }

        $analyze_result = array();

        foreach ($lopts as $opt) {
            if (preg_match('/==$/', $opt) > 0) {
                $opt = substr($opt, 0, -2);
                $analyze_result[$opt] = ETHNA_OPTVALUE_IS_OPTIONAL; // 値は任意
            } elseif (preg_match('/=$/', $opt) > 0) {
                $opt = substr($opt, 0, -1);
                $analyze_result[$opt] = ETHNA_OPTVALUE_IS_REQUIRED; // 値は必須
            } else {
                $analyze_result[$opt] = ETHNA_OPTVALUE_IS_DISABLED; // 値は不要
            }
        }

        return $analyze_result;
    }
}

// }}}

