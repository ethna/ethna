<?php
/** Ethna_Plugin_Logwriter_DebugToolbar
 *
 * @author  Sotaro KARASAWA <sotaro.k@gmail.com>
 * @date    2008/12/01
 */


class Ethna_Plugin_Logwriter_Debugtoolbar extends Ethna_Plugin_Logwriter
{
    var $log_array = array();

    /**
     *  ログを出力する
     *
     *  @access public
     *  @param  int     $level      ログレベル(LOG_DEBUG, LOG_NOTICE...)
     *  @param  string  $message    ログメッセージ(+引数)
     */
    function log($level, $message)
    {
        $c = Ethna_Controller::getInstance();

        $prefix = $this->ident;
        if (array_key_exists("pid", $this->option)) {
            $prefix .= sprintf('[%d]', getmypid());
        }
        $pre_prefix = '<div class="ethna-debug-log ethna-debug-log-' . $this->_getLogLevelName($level) . '">';
        $prefix .= sprintf($c->getGateway() != GATEWAY_WWW ? '(%s): ' : '(<span class="ethna-debug-log-loglevel ethna-debug-log-loglevel-' . $this->_getLogLevelName($level) . ' ">%s</span>): ',
            $this->_getLogLevelName($level)
        );
        $post_prefix = '</div>';

        $tracer = '';
        if ($this->_getLogLevelName($level) != 'DEBUG'
            && preg_match('/in (template ")?(\/.+\.(php|tpl))"?\s+on line (\d+)/', $message, $match)) {
            list(, , $file, ,$line) = $match;
            $line = intval($line);
            if (file_exists($file)) {
                $tracer .= ($c->getGateway() != GATEWAY_WWW ? "" : '<pre class="ethna-debug-pre">');
                $f = new SplFileObject($file);
                $min = ($line - 4 < 0) ? 0 : $line - 4;
                $i = $min;
                foreach (new LimitIterator($f, $min, 7) as $line_str) {
                    $l = ++$i;
                    if ($l == $line) {
                        $tracer .= '<span class="ethna-debug-pre-blink">';
                    }
                    $tracer .= $l . ': ' . htmlspecialchars($line_str) . ($c->getGateway() != GATEWAY_WWW ? "" : '<br />');
                    if ($l == $line) {
                        $tracer .= '</span>';
                    }
                }
                $tracer .= ($c->getGateway() != GATEWAY_WWW ? "" : '</pre>');
            }
        }

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

        $br = $c->getGateway() != GATEWAY_WWW ? "" : "<br />";

        $log_content = ($pre_prefix . $prefix . $message . $tracer . $post_prefix . "\n");
        $this->log_array[] = $log_content;

        return $log_content;
    }

    function end()
    {
        $ctl = Ethna_Controller::getInstance();
        if (!is_null($view = $ctl->getView()) && !$view->has_default_header) {
            $this->log_array = array();
            return null;
        }
        echo '<div class="ethna-debug" id="ethna-debug-logwindow">';
        echo '<div class="ethna-debug-title">Log</div>';
        foreach ($this->log_array as $log) {
            echo $log;
        }
        echo '</div>';

        $this->log_array = array();
    }

    public function __destruct()
    {
        if (!empty($this->log_array)) {
            echo "<h1>Script shutdown unexpectedly</h1>";
            if (is_array($this->log_array)) foreach ($this->log_array as $log) {
                echo $log;
            }
        }
    }

    /**
     *  ログ出力箇所の情報(関数名/ファイル名等)を取得する
     *
     *  @access private
     *  @return array   ログ出力箇所の情報
     */
    function _getBacktrace()
    {
        $skip_method_list = array(
            array('ethna', 'raise'),
            array(null, 'raiseerror'),
            array(null, 'handleerror'),
            array('ethna_logger', null),
            array('ethna_plugin_logwriter', null),
            array('ethna_error', null),
            array('ethna_apperror', null),
            array('ethna_actionerror', null),
            array('ethna_backend', 'log'),
            array(null, 'ethna_error_handler'),
            array(null, 'trigger_error'),
            array('ether_plugin_logwriter', null), // この1行を足すためにメソッドまるごとコピーですよ・・・
        );

        if ($this->have_backtrace == false) {
            return null;
        }

        $bt = debug_backtrace();
        $i = 0;
        while ($i < count($bt)) {
            if (isset($bt[$i]['class']) == false) {
                $bt[$i]['class'] = null;
            }
            $skip = false;

            // メソッドスキップ処理
            foreach ($skip_method_list as $method) {
                $class = $function = true;
                if ($method[0] != null) {
                    $class = preg_match("/^$method[0]/i", $bt[$i]['class']);
                }
                if ($method[1] != null) {
                    $function = preg_match("/^$method[1]/i", $bt[$i]['function']);
                }
                if ($class && $function) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                $i++;
            } else {
                break;
            }
        }


        $c = Ethna_Controller::getInstance();
        $basedir = $c->getBasedir();

        $function = sprintf("%s.%s", isset($bt[$i]['class']) ? $bt[$i]['class'] : 'global', $bt[$i]['function']);

        if (!isset($bt[$i])) {
            return null;
        }
        else {
            $file = $bt[$i]['file'];
        }
        $orig_file = $file;
        if (strncmp($file, $basedir, strlen($basedir)) == 0) {
            $file = substr($file, strlen($basedir));
        }
        if (strncmp($file, ETHNA_BASE, strlen(ETHNA_BASE)) == 0) {
            $file = preg_replace('#^/+#', '', substr($file, strlen(ETHNA_BASE)));
        }
        $line = $bt[$i]['line'];
        return array('function' => $function, 'pos' => sprintf('%s:%s', $file, $line), 'file' => $orig_file);
    }
}

