<?php
// vim: foldmethod=marker
/**
 *  Ethna_I18N.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{  mbstring enabled check
function mb_enabled()
{
    return (extension_loaded('mbstring')) ? true : false;
}
// }}}

// {{{ I18N shortcut
/**
 *  メッセージカタログからロケールに適合するメッセージを取得します。
 *  Ethna_I18N#get のショートカットです。
 *
 *  @access public
 *  @param  string  $message    メッセージ
 *  @return string  ロケールに適合するメッセージ
 *  @see    Ethna_I18N#get
 */
function _et($message) 
{
    $ctl =& Ethna_Controller::getInstance();
    $i18n =& $ctl->getI18N();
    $client_enc = $ctl->getClientEncoding();
 
    $ret_message = $i18n->get($message);

    //
    //  convert message in case $client_encoding
    //  setting IS NOT UTF-8.
    //
    //  @see Ethna_Controller#_getDefaultLanguage
    // 
    if (strcasecmp($client_enc, 'UTF-8') !== 0) {
        return mb_convert_encoding($ret_message, $client_enc, 'UTF-8');
    }

    return $ret_message;
}
// }}}
 
// {{{ Ethna_I18N
/**
 *  i18n関連の処理を行うクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_I18N
{
    /**#@+
     *  @access private
     */

    /** @var    Ethna_Controller  コントローラーオブジェクト  */
    var $ctl;

    /** @var    bool    gettextフラグ */
    var $use_gettext;

    /** @var    string  ロケール */
    var $locale;

    /** @var    string  プロジェクトのロケールディレクトリ */
    var $locale_dir;

    /** @var    string  アプリケーションID */
    var $appid;

    /** @var    string  システム側エンコーディング */
    var $systemencoding;

    /** @var    string  クライアント側エンコーディング */
    var $clientencoding;

    /** @var    mixed   Ethna独自のメッセージカタログ */
    var $messages;

    /** @var    mixed   ロガーオブジェクト */
    var $logger;

    /**#@-*/

    /**
     *  Ethna_I18Nクラスのコンストラクタ
     *
     *  @access public
     *  @param  string  $locale_dir プロジェクトのロケールディレクトリ
     *  @param  string  $appid      アプリケーションID
     */
    function Ethna_I18N($locale_dir, $appid)
    {
        $this->locale_dir = $locale_dir;
        $this->appid = strtoupper($appid);

        $this->ctl =& Ethna_Controller::getInstance();
        $config =& $this->ctl->getConfig();
        $this->logger =& $this->ctl->getLogger();
        $this->use_gettext = $config->get('use_gettext') ? true : false;

        //    gettext load check. 
        if ($this->use_gettext === true
         && !extension_loaded("gettext")) {
            $this->logger->log(LOG_WARNING,
                "You specify to use gettext in ${appid}/etc/${appid}-ini.php, "
              . "but gettext extension was not installed !!!"
            );
        }

        $this->messages = false;  //  not initialized yet.
    }

    /**
     *  ロケール、言語設定を設定する
     *
     *  @access public
     *  @param  string  $locale         ロケール名(e.x ja_JP, en_US 等)
     *                                  (ll_cc の形式。ll = 言語コード cc = 国コード)
     *  @param  string  $systemencoding システムエンコーディング名
     *  @param  string  $clientencoding クライアントエンコーディング名
     *                                  (=テンプレートのエンコーディングと考えてよい)
     *  @see    http://www.gnu.org/software/gettext/manual/html_node/Locale-Names.html 
     */
    function setLanguage($locale, $systemencoding = null, $clientencoding = null)
    {
        setlocale(LC_ALL, $locale);
        if ($this->use_gettext) {
            bindtextdomain($this->appid, $this->locale_dir);
            textdomain($this->appid);
        }

        $this->locale = $locale;
        $this->systemencoding = $systemencoding;
        $this->clientencoding = $clientencoding;

        //  強制的にメッセージカタログ再生成
        if (!$this->use_gettext) {
            $this->messages = $this->_makeEthnaMsgCatalog();
        }
    }

    /**
     *  メッセージカタログからロケールに適合するメッセージを取得する
     *
     *  @access public
     *  @param  string  $msg    メッセージ
     *  @return string  ロケールに適合するメッセージ
     */
    function get($msg)
    {
        if ($this->use_gettext) {

            //
            //    gettext から返されるメッセージは、
            //    [appid]/locale/[locale_name]/LC_MESSAGES/[appid].mo から
            //    返される。
            //
            return gettext($msg);

        } else {

            //
            //  初期化されてない場合は、
            //  Ethna独自のメッセージカタログを初期化
            //
            if ($this->messages === false) {
                $this->messages = $this->_makeEthnaMsgCatalog();
            }

            //
            //  Ethna独自のメッセージは、
            //  [appid]/locale/[locale_name]/LC_MESSAGES/*.ini から
            //  返される。
            //
            if (isset($this->messages[$msg]) && !empty($this->messages[$msg])) {
                return $this->messages[$msg];
            }

        }

        return $msg;
    }

    /**
     *  Ethna独自のメッセージカタログを読み込んで生成する
     *
     *  1. [appid]/locale/[locale_name]/LC_MESSAGES/*.ini
     *     からメッセージを読み込む。
     *  2. Ethnaが吐くメッセージカタログファイル名は ethna_sysmsg.ini とし、
     *     skel化して ETHNA_HOME/skel/locale/[locale_name]/ethna_sysmsg.ini に置く
     *  3. "ethna i18n" コマンドでは、1. のファイルとプロジェクトファイル
     *     内の _et('xxxx') を全て走査し、メッセージカタログを作る。gettext を利用
     *     するのであれば、potファイルを生成する。
     *  4. ethna_sysmsg.ini は単純な ini ファイル形式とし、
     *     "msgid" = "translation" の形式とする。エンコーディングは一律 UTF-8
     * 
     *  @access  private 
     *  @return  array     読み込んだメッセージカタログ。失敗した場合は空の配列 
     */
    function _makeEthnaMsgCatalog()
    {
        $ret_messages = array();

        //    Ethna_I18N#setLanguage を呼び出さず
        //    このメソッドを呼び出すと、ロケール名が空になる
        //    その場合は Ethna_Controller の設定を補う
        if (empty($this->locale)) {
            list($this->locale, $sys_enc, $cli_enc) = $this->ctl->getLanguage();
        }

        //    ロケールディレクトリが存在しない場合は、E_NOTICEを出し、
        //    デフォルトの skelton ファイルを使う
        $msg_dir = sprintf("%s/%s/LC_MESSAGES", $this->locale_dir, $this->locale);
        if (!file_exists($msg_dir)) {
            //   use skelton.
            $this->logger->log(LOG_NOTICE,
                               "Message directory was not found!! : $msg_dir,"
                             . " Use skelton file Instead"); 
            $msg_dir = sprintf("%s/skel/locale/%s", ETHNA_BASE, $this->locale);
            if (!file_exists($msg_dir)) {  // last fallback.
                $msg_dir = sprintf("%s/skel/locale", ETHNA_BASE);
            }
        }
                     
        //  localeディレクトリ内のファイルを読み込み、parseする
        $msg_dh = opendir($msg_dir);
        while (($file = readdir($msg_dh)) !== false) {
            if (is_dir($file) || !preg_match("/[A-Za-z0-9\-_]+\.ini$/", $file)) {
                continue;
            }
            $msg_file = sprintf("%s/%s", $msg_dir, $file);
            $messages = $this->parseEthnaMsgCatalog($msg_file);
            $ret_messages = array_merge($ret_messages, $messages);
        }
        
        return $ret_messages;
    }

    /**
     *  Ethna独自のメッセージカタログをparseする
     *
     *  @access  public
     *  @param   string    メッセージカタログファイル名
     *  @return  array     読み込んだメッセージカタログ。失敗した場合は空の配列 
     */
    function parseEthnaMsgCatalog($file)
    {
        $messages = array();

        //
        //    ファイルフォーマットは ini ファイルライクだが、
        //    parse_ini_file 関数は使わない。
        //
        //    キーに含められないキーワードや文字があるため。
        //    e.x yes, no {}|&~![() 等
        //    @see http://www.php.net/manual/en/function.parse-ini-file.php
        //
        //    TODO: 複数行に渡った場合に対応する. 現行の実装は
        //          Line Parser でしかない
        //
        $contents = file($file);
        foreach ($contents as $idx => $line) {

            //  コメント行または空行は無視する
            trim($line);
            if (strpos($line, ';') === 0 || preg_match('/^$/', $line)) {
                continue;
            }

            $quote = 0;                // ダブルクオートの数
            $before_is_quote = false;  // 直前の文字がダブルクォートか否か
            $equal_op = 0;             // 等値演算子の数
            $is_end = false;           // 終了フラグ
            $length = strlen($line);
            $msgid = $msgstr = '';

            //    1文字ずつ、ダブルクォートの数
            //    を基準にしてパースする
            for ($pos = 0; $pos < $length; $pos++) {
        
                //    特別な文字で分岐
                switch ($line[$pos]) {
                    case '"':
                        if (!$before_is_quote) {
                            $quote++;
                            continue 2;  // switch 文を抜けるのではなく、
                                         // for文に戻る。
                        }
                        $before_is_quote = false;
        
                        //  ダブルクォートが4つに達した時点で終了
                        if ($quote == 4) {
                            $is_end = true;   
                        }
                        break; 
                    case '=':
                        //  等値演算子は文法的にvalidかどうかを確
                        //  認する手段でしかない 
                        if ($quote == 2) {
                            $equal_op++;
                        }
                    case '\\': // backslash
                        if ($quote == 1 || $quote == 3) {
                            $before_is_quote = true;
                        }
                        break;
                    default:
                        if ($before_is_quote) {
                            $before_is_quote = false;
                        }
                }

                if ($is_end == true) {
                    break;
                }

                //  パース済みの文字列を追加
                if ($quote == 1) {
                    $msgid .= $line[$pos];
                }
                if ($quote == 3) {
                    $msgstr .= $line[$pos];
                }
            }
        
            //  valid な行かチェック
            if ($equal_op != 1 || $quote != 4) {
                $this->logger->log(LOG_WARNING,
                                   "invalid message catalog in {$file}, line " . ($idx + 1)
                );
                continue; 
            } 

            $messages[$msgid] = $msgstr; 
        }
        
        return $messages;
    }
}
// }}}

?>
