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

        $ctl =& Ethna_Controller::getInstance();
        $config =& $ctl->getConfig();
        $this->logger =& $ctl->getLogger();
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
            //  Ethna独自のメッセージカタログを初期化
            //  この処理ははじめに使用するときまで遅延させる
            //
            if ($this->messages === false) {
                $this->messages = $this->_getEthnaMsgCatalog();
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
     *     からメッセージを読み込み、$this->messages を初期化する
     *  2. Ethnaが吐くメッセージカタログファイル名は ethna_sysmsg.ini とし、
     *     skel化して ETHNA_HOME/skel/locale/[locale_name]/ethna_sysmsg.ini に置く
     *  3. "ethna i18n" コマンドでは、1. のファイルとプロジェクトファイル
     *     内の _et('xxxx') を全て走査し、メッセージカタログを作る。gettext を利用
     *     するのであれば、potファイルを生成する。
     *  4. ethna_sysmsg.ini は単純な ini ファイル形式とし、
     *     msgid = translation の形式とする。エンコーディングは一律 UTF-8
     * 
     *  @access  private 
     *  @return  array     読み込んだメッセージカタログ。失敗した場合は空の配列 
     */
    function _getEthnaMsgCatalog()
    {
        $ret_messages = array();

        $msg_dir = sprintf("%s/%s/LC_MESSAGES", $this->locale_dir, $this->locale);
        if (!file_exists($msg_dir)) {
            $this->logger->log(LOG_WARNING, "Message directory was not found!! : $msg_dir"); 
            return $ret_messages;
        }
                     
        $msg_dh = opendir($msg_dir);

        //    ini ファイル形式だが、parse_ini_file 関数は使わない
        //    特殊な文字が多すぎる上、parseの仕方もバージョンによって
        //    微妙に違ったりするため。
        while (($file = readdir($msg_dh)) !== false) {
            if (is_dir($file) || !preg_match("/[A-Za-z0-9\-_]+\.ini$/", $file)) {
                continue;
            }
            $msg_file = sprintf("%s/%s", $msg_dir, $file);
            $contents = file($msg_file);
            foreach ($contents as $idx => $line) {
                if (strpos($line, ';') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                $catalog = array();
                if (preg_match('/^"(.+?)"\s*=\s*"(.*?)".*$/', $line, $catalog)) {
                    $msgid = $catalog[1];
                    $msgstr = $catalog[2];
                    $ret_messages[$msgid] = $msgstr;
                } else {
                    $this->logger->log(LOG_WARNING,
                                       "invalid message catalog in {$file}, line " . ($idx + 1)
                    ); 
                }
            }
        }
 
        return $ret_messages;
    }
}
// }}}

?>
