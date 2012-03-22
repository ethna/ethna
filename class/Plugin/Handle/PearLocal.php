<?php
// vim: foldmethod=marker
/**
 *  PearLocal.php
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'PEAR/Config.php';
require_once ETHNA_BASE . '/class/PearWrapper.php';

// {{{ Ethna_PearConfig_Local
/**
 *  Special Class for Pear Install Handler.
 *  This class should be instantiated by ONLY Ethna_Plugin_Handle_PearLocal.
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     private
 *  @package    Ethna
 */
class Ethna_PearConfig_Local extends Ethna_PearWrapper
{

    // {{{ _setLocalConfig
    /**
     *  config for local.
     *
     *  @return true|Ethna_Error
     *  @access private
     */
    protected function _setLocalConfig()
    {
        $true = true;

        // determine dirs
        $base = $this->target_ctl->getBaseDir();
        $bin  = $this->target_ctl->getDirectory('bin');
        $tmp  = $this->target_ctl->getDirectory('tmp');
        $lib  = "{$base}/lib";
        $dirs = array(
                'php_dir'       => "$lib",
                'bin_dir'       => "{$base}/bin",
                'cache_dir'     => "{$tmp}/.pear/cache",
                'download_dir'  => "{$lib}/.pear/download",
                'temp_dir'      => "{$lib}/.pear/temp",
                'doc_dir'       => "{$lib}/.pear/doc",
                'ext_dir'       => "{$lib}/.pear/ext",
                'data_dir'      => "{$lib}/.pear/data",
                'test_dir'      => "{$lib}/.pear/test",
                );

        $default_pearrc = "{$base}"
                        . DIRECTORY_SEPARATOR
                        . "lib"
                        . DIRECTORY_SEPARATOR
                        . "pear.conf";
        $app_config = $this->target_ctl->getConfig();
        $app_pearrc = $app_config->get('app_pear_local_config');
        $pearrc = (empty($app_pearrc))
                ? $default_pearrc
                : "{$base}/$app_pearrc";
        $this->conf_file = $pearrc;
        $this->config = PEAR_Config::singleton($pearrc);

        // read local .pearrc if exists.
        if (is_file($pearrc) && is_readable($pearrc)) {
            $this->config->readConfigFile($pearrc);
        }

        // set dirs to config
        foreach ($dirs as $key => $dir) {
            $_dir = $this->config->get($key, 'user');
            if (!isset($_dir)) {
                if (is_dir($dir) == false) {
                    Ethna_Util::mkdir($dir, 0755);
                }
                $this->config->set($key, $dir);
            }
        }

        if ($this->channel == 'dummy') {
            $default_channel = $this->config->get('default_channel', 'user');
            $this->channel = (empty($default_channel))
                           ? 'pear.php.net'
                           : $default_channel;
        }

        // setup channel
        $reg = $this->config->getRegistry();
        if ($reg->channelExists($this->channel) == false) {
            $ret = $this->doChannelDiscover();
            if (Ethna::isError($ret)) {
                return $ret;
            }
        }
        $this->config->set('default_channel', $this->channel);

        // write local .pearrc
        $this->config->writeConfigFile($pearrc);

        return $true;
    }
    // }}}

    // {{{ getConfFile
    /**
     *    return local config filename.
     */
     function getConfFile()
     {
         return $this->conf_file;

     }
     // }}}
}
// }}}

// {{{ Ethna_Plugin_Handle_PearLocal
/**
 *  pear package install handler
 *
 *  @author     Yoshinari Takaoka <takaoka@beatcraft.com>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_PearLocal extends Ethna_Plugin_Handle
{
    // {{{ _parseArgList()
    /**
     * @access private
     */
    function _parseArgList()
    {
        $r = $this->_getopt(array('basedir=', 'channel='));
        if (Ethna::isError($r)) {
            return $r;
        }

        list($opt_list, $arg_list) = $r;
        $ret = array();

        // options
        if (isset($opt_list['basedir'])) {
            $ret['basedir'] = end($opt_list['basedir']);
        }
        if (isset($opt_list['channel'])) {
            $ret['channel'] = end($opt_list['channel']);
        }

        // arguments
        $ret['pear_args'] = $arg_list;

        return $ret;
    }
    // }}}

    // {{{ perform()
    /**
     *  @access public
     *  @todo   deal with the package including some plugins.
     */
    public function perform()
    {
        $true = true;

        //   check arguments.
        $args = $this->_parseArgList();
        if (Ethna::isError($args)) {
            return Ethna::raiseError(
                $args->getMessage(),
                'usage'
            );
        }

        $basedir = isset($args['basedir']) ? realpath($args['basedir']) : getcwd();
        $channel = isset($args['channel']) ? $args['channel'] : 'dummy';

        $pear_local = new Ethna_PearConfig_Local();
        $r = $pear_local->init('local', $basedir, $channel);
        if (Ethna::isError($r)) {
            return $r;
        }

        //    build command string.
        $pear_cmds = $args['pear_args'];
        $pear_bin = (ETHNA_OS_WINDOWS)
                  ? getenv('PHP_PEAR_BIN_DIR') . DIRECTORY_SEPARATOR . 'pear.bat'
                  : (PHP_BINDIR . DIRECTORY_SEPARATOR . 'pear');
        $local_conf_file = $pear_local->getConfFile();
        array_unshift(
            $pear_cmds,
            $pear_bin,
            '-c',
            $local_conf_file
        );
        if (ETHNA_OS_WINDOWS) {
            foreach($pear_cmds as $key => $value) {
                $pear_cmds[$key] = (strpos($value, ' ') !== false)
                                 ? ('"' . $value . '"')
                                 : $value;
            }
        }
        $command_str = implode(' ', $pear_cmds);

        //   finally exec pear command.
        if (ETHNA_OS_WINDOWS) {
            $tmp_dir_name ="ethna_tmp_dir";
            Ethna_Util::mkdir($tmp_dir_name, 0777);
            $tmpnam = tempnam($tmp_dir_name, "temp") .'.bat';
            $fp = fopen($tmpnam, 'w');
            fwrite($fp, "@echo off\r\n");
            fwrite($fp, $command_str . " 2>&1");
            fclose ($fp);
            system($tmpnam);
            Ethna_Util::purgeDir($tmp_dir_name);
        } else {
            system($command_str);
        }

        return $true;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    public function getDescription()
    {
        return <<<EOS
install pear package to {base_dir}/lib, {base_dir}/bin ... :
    {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [pear command ...]
    for more pear command information, see "pear help"

EOS;
    }
    // }}}

    // {{{ getUsage()
    /**
     *  @access public
     */
    public function getUsage()
    {
        return <<<EOS
ethna {$this->id} [-c|--channel=channel] [-b|--basedir=dir] [pear command ...]
    for more pear command information, see "pear help"

EOS;
    }
    // }}}
}
// }}}
