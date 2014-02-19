<?php
// vim: foldmethod=marker
/**
 *  File.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Plugin_Logwriter_File
/**
 *  ログ出力クラス(File)
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Logwriter_File extends Ethna_Plugin_Logwriter
{
    /**#@+
     *  @access private
     */

    /** @var    int     ログファイルハンドル */
    public $fp;

    /** @var    int     ログファイルパーミッション */
    public $mode = 0666;

    /**#@-*/

    /**
     *  Fileクラスのコンストラクタ
     *
     *  @access public
     */
    public function __construct()
    {
        $this->fp = null;
    }

    /**
     *  ログオプションを設定する
     *
     *  @access public
     *  @param  int     $option     ログオプション(LOG_FILE,LOG_FUNCTION...)
     */
    function setOption($option)
    {
        parent::setOption($option);
        
        if (isset($option['file'])) {
            $this->file = $option['file'];
        } else {
            $this->file = $this->_getLogFile();
        }

        if (isset($option['mode'])) {
            $this->mode = $option['mode'];
        }
    }

    /**
     *  ログ出力を開始する
     *
     *  @access public
     */
    function begin()
    {
        $this->fp = fopen($this->file, 'a');
        $st = fstat($this->fp);
        if (function_exists("posix_getuid") && posix_getuid() == $st[4]) {
            chmod($this->file, intval($this->mode, 8));
        }
    }

    /**
     *  ログを出力する
     *
     *  @access public
     *  @param  int     $level      ログレベル(LOG_DEBUG, LOG_NOTICE...)
     *  @param  string  $message    ログメッセージ(+引数)
     */
    function log($level, $message)
    {
        if ($this->fp == null) {
            return;
        }

        $prefix = strftime('%Y/%m/%d %H:%M:%S ') . $this->ident;
        if (array_key_exists("pid", $this->option)) {
            $prefix .= sprintf('[%d]', getmypid());
        }
        $prefix .= sprintf('(%s): ', $this->_getLogLevelName($level));
        if (array_key_exists("function", $this->option) ||
            array_key_exists("pos", $this->option)) {
            $tmp = "";
            $bt = $this->_getBacktrace();
            if ($bt && array_key_exists("function", $this->option) && $bt['function']) {
                $tmp .= $bt['function'];
            }
            if ($bt && array_key_exists("pos", $this->option) && $bt['pos']) {
                $tmp .= $tmp ? sprintf('(%s)', $bt['pos']) : $bt['pos'];
            }
            if ($tmp) {
                $prefix .= $tmp . ": ";
            }
        }
        fwrite($this->fp, $prefix . $message . "\n");

        return $prefix . $message;
    }

    /**
     *  ログ出力を終了する
     *
     *  @access public
     */
    function end()
    {
        if ($this->fp) {
            fclose($this->fp);
            $this->fp = null;
        }
    }

    /**
     *  ログファイルの書き出し先を取得する(ログファシリティに
     *  LOG_FILEが指定されている場合のみ有効)
     *
     *  ログファイルの書き出し先を変更したい場合はこのメソッドを
     *  オーバーライドします
     *
     *  @access protected
     *  @return string  ログファイルの書き出し先
     */
    function _getLogFile()
    {
        $controller = Ethna_Controller::getInstance();

        if (array_key_exists("dir", $this->option)) {
            $dir = $this->option['dir'];
        } else {
            $dir = $controller->getDirectory('log');
        }

        return sprintf('%s/%s.log',
            $dir,
            strtolower($controller->getAppid())
        );
    }
}
// }}}
