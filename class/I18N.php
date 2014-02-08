<?php
// vim: foldmethod=marker
/**
 *  I18N.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

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

    /** @protected    Ethna_Controller  コントローラーオブジェクト  */
    protected $ctl;

    /** @protected    bool    gettextフラグ */
    protected $use_gettext;

    /** @protected    string  ロケール */
    protected $locale;

    /** @protected    string  プロジェクトのロケールディレクトリ */
    protected $locale_dir;

    /** @protected    string  アプリケーションID */
    protected $appid;

    /** @protected    string  システム側エンコーディング */
    protected $systemencoding;

    /** @protected    string  クライアント側エンコーディング */
    protected $clientencoding;

    /** @protected    mixed   Ethna独自のメッセージカタログ */
    protected $messages;

    /** @protected    mixed   ロガーオブジェクト */
    protected $logger;

    /**#@-*/

    /**
     *  Ethna_I18Nクラスのコンストラクタ
     *
     *  @access public
     *  @param  string  $locale_dir プロジェクトのロケールディレクトリ
     *  @param  string  $appid      アプリケーションID
     */
    public function __construct($locale_dir, $appid)
    {
        $this->locale_dir = $locale_dir;
        $this->appid = $appid;

        $this->ctl = Ethna_Controller::getInstance();
        $config = $this->ctl->getConfig();
        $this->logger = $this->ctl->getLogger();
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
     *  タイムゾーンを設定する PHP 5.1.0 以前では
     *  無意味なので呼ぶ必要がありません。
     *
     *  @access public
     *  @param  string  $timezone       タイムゾーン名(e.x Asia/Tokyo)
     *  @see    http://www.php.net/manual/ja/timezones.php
     *  @static
     */
    public static function setTimeZone($timezone = 'UTC')
    {
        //   date.timezone 設定は PHP 5.1.0 以降でのみ
        //   利用可能
        ini_set('date.timezone', $timezone);
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
    public function setLanguage($locale, $systemencoding = null, $clientencoding = null)
    {
        setlocale(LC_ALL, $locale . ($systemencoding !== null ? "." . $systemencoding : ""));

        if ($this->use_gettext) {
            bind_textdomain_codeset($locale, $clientencoding);
            bindtextdomain($locale, $this->locale_dir);
            textdomain($locale);
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
    public function get($msg)
    {

        if ($this->use_gettext) {

            //
            //    gettext から返されるメッセージは、
            //    [appid]/locale/[locale_name]/LC_MESSAGES/[locale].mo から
            //    返される。エンコーディング変換はgettext任せである
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

                $ret_message = $this->messages[$msg];

                //
                //  convert message in case $client_encoding
                //  setting IS NOT UTF-8.
                //
                //  @see Ethna_Controller#_getDefaultLanguage
                // 
                if (strcasecmp($this->clientencoding, 'UTF-8') !== 0) {
                    return mb_convert_encoding($ret_message, $this->clientencoding, 'UTF-8');
                }

                return $ret_message;
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
    private function _makeEthnaMsgCatalog()
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
    public function parseEthnaMsgCatalog($file)
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
        $contents = file($file);
        if ($contents === false) {
            return $messages;
        }

        $quote = 0;                   // ダブルクオートの数 
        $in_translation_line = false; // 翻訳行をパース中か否か
        $before_is_quote = false;     // 直前の文字がクォート文字(\)か否か
        $equal_op = 0;                // 等値演算子の数
        $is_end = false;              // 終了フラグ
        $msgid = $msgstr = '';

        foreach ($contents as $idx => $line) {

            //  コメント行または空行は無視する。
            //  ホワイトスペースを除いた上で、それと看做される行も無視する
            $ltrimed_line = ltrim($line);
            if ($in_translation_line == false
            && (strpos($ltrimed_line, ';') === 0 || preg_match('/^$/', $ltrimed_line))) {
                continue;
            }

            //    1文字ずつ、ダブルクォートの数
            //    を基準にしてパースする
            $length = strlen($line);
            for ($pos = 0; $pos < $length; $pos++) {

                //    特別な文字で分岐
                switch ($line[$pos]) {
                    case '"':
                        if ($in_translation_line == false && $pos == 0) {
                            $in_translation_line = true;  // 翻訳行開始
                        }
                        if (!$before_is_quote) {
                            $quote++;
                            continue 2;  // switch 文を抜けるのではなく、
                                         // for文に戻る = 次の文字へ
                        }
                        //  クォートされた「"」
                        $before_is_quote = false;         
                        break; 
                    case '=':
                        //  等値演算子は文法的にvalidかどうかを確
                        //  認する手段でしかない 
                        if ($quote == 2) {
                            $equal_op++;
                        }
                    case '\\': // backslash
                        //   クォート用のバックスラッシュと看做す
                        if ($quote == 1 || $quote == 3) {
                            $before_is_quote = true;
                        }
                        break;
                    default:
                        if ($before_is_quote) {
                            $before_is_quote = false;
                        }
                        if ($quote == 4) {
                            $is_end = true;   
                        }
                }

                if ($is_end == true) {
                    $in_translation_line = false;  //  翻訳行終了
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
        
            //  一行分のパース終了
            if ($is_end == false) {
                //  翻訳行がまだ終わってない場合次の行へ
                continue;
            } elseif ($equal_op != 1 || $quote != 4) { 
                //  終わっているが、valid な翻訳行でない場合
                $this->logger->log(LOG_WARNING,
                                   "invalid message catalog in {$file}, line " . ($idx + 1)
                );
                continue; 
            } 

            //  カタログに追加
            $msgid = preg_replace('#\\\"#', '"', $msgid);
            $msgstr = preg_replace('#\\\"#', '"', $msgstr);
            $messages[$msgid] = $msgstr; 

            //  パース用変数をリセット
            $quote = 0;                   
            $in_translation_line = false;
            $before_is_quote = false;
            $equal_op = 0;
            $is_end = false;
            $msgid = $msgstr = '';
        }
        
        return $messages;
    }
}
// }}}

