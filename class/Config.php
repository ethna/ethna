<?php
// vim: foldmethod=marker
/**
 *  Config.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

// {{{ Ethna_Config
/**
 *  設定クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Config
{
    /**#@+
     *  @access private
     */

    /** @var Ethna_Controller $controller    controllerオブジェクト */
    public $controller;

    /** @var array $config  設定内容 */
    public $config = null;

    /**#@-*/


    /**
     *  Ethna_Configクラスのコンストラクタ
     *
     *  @access public
     *  @param  object  Ethna_Controller    $controller    controllerオブジェクト
     */
    public function __construct($controller)
    {
        $this->controller = $controller;

        // 設定ファイルの読み込み
        $r = $this->_getConfig();
        if (Ethna::isError($r)) {
            // この時点ではlogging等は出来ない(Loggerオブジェクトが生成されていない)
            $fp = fopen("php://stderr", "r");
            fputs($fp, sprintf("error occured while reading config file(s) [%s]\n"), $r->getInfo(0));
            fclose($fp);
            $this->controller->fatal();
        }
    }

    /**
     *  設定値へのアクセサ(R)
     *
     *  @access public
     *  @param  string  $key    設定項目名
     *  @return string  設定値
     */
    function get($key = null)
    {
        if (is_null($key)) {
            return $this->config;
        }
        if (isset($this->config[$key]) == false) {
            return null;
        }
        return $this->config[$key];
    }

    /**
     *  設定値へのアクセサ(W)
     *
     *  @access public
     *  @param  string  $key    設定項目名
     *  @param  string  $value  設定値
     */
    function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     *  設定ファイルを更新する
     *
     *  @access public
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function update()
    {
        return $this->_setConfig();
    }

    /**
     *  設定ファイルを読み込む
     *
     *  @access private
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function _getConfig()
    {
        $config = array();
        $file = $this->_getConfigFile();
        if (file_exists($file)) {
            $lh = Ethna_Util::lockFile($file, 'r');
            if (Ethna::isError($lh)) {
                return $lh;
            }

            include($file);

            Ethna_Util::unlockFile($lh);
        }

        // デフォルト値設定
        if (isset($_SERVER['HTTP_HOST']) && isset($config['url']) == false) {
            $config['url'] = sprintf("http://%s/", $_SERVER['HTTP_HOST']);
        }
        if (isset($config['dsn']) == false) {
            $config['dsn'] = "";
        }
        if (isset($config['log_facility']) == false) {
            $config['log_facility'] = "";
        }
        if (isset($config['log_level']) == false) {
            $config['log_level'] = "";
        }
        if (isset($config['log_option']) == false) {
            $config['log_option'] = "";
        }

        $this->config = $config;

        return 0;
    }

    /**
     *  設定ファイルに書き込む
     *
     *  @access private
     *  @return mixed   0:正常終了 Ethna_Error:エラー
     */
    function _setConfig()
    {
        $file = $this->_getConfigFile();

        $lh = Ethna_Util::lockFile($file, 'w');
        if (Ethna::isError($lh)) {
            return $lh;
        }

        fwrite($lh, "<?php\n");
        fwrite($lh, sprintf("/*\n * %s\n *\n * update: %s\n */\n", basename($file), strftime('%Y/%m/%d %H:%M:%S')));
        fwrite($lh, "\$config = array(\n");
        foreach ($this->config as $key => $value) {
            $this->_setConfigValue($lh, $key, $value, 0);
        }
        fwrite($lh, ");\n");

        Ethna_Util::unlockFile($lh);

        return 0;
    }

    /**
     *  設定ファイルに設定値を書き込む
     *
     *  @access private
     */
    function _setConfigValue($fp, $key, $value, $level)
    {
        fputs($fp, sprintf("%s'%s' => ", str_repeat("    ", $level+1), $key));
        if (is_array($value)) {
            fputs($fp, sprintf("array(\n"));
            foreach ($value as $k => $v) {
                $this->_setConfigValue($fp, $k, $v, $level+1);
            }
            fputs($fp, sprintf("%s),\n", str_repeat("    ", $level+1)));
        } else {
            fputs($fp, sprintf("'%s',\n", $value));
        }
    }

    /**
     *  設定ファイル名を取得する
     *
     *  @access private
     *  @return string  設定ファイルへのフルパス名
     */
    function _getConfigFile()
    {
        return $this->controller->getDirectory('etc') . '/' . strtolower($this->controller->getAppId()) . '-ini.php';
    }
}
// }}}
