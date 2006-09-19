<?php
// vim: foldmethod=marker
/**
 *  Ethna_PearWrapper.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

include_once('PEAR.php');
include_once('PEAR/Config.php');
include_once('PEAR/Command.php');
include_once('PEAR/PackageFile.php');

// {{{ Ethna_PearWrapper
/**
 *  wrapper class for PEAR_Command
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_PearWrapper
{
    /**#@+
     *  @access     private
     */

    /** @var    string  channel url of ethna repositry */
    var $channel;

    /** @var    object  PEAR_Config     PEAR_Config object */
    var $config;

    /** @var    object  PEAR_Registry   PEAR_Registry object */
    var $registry;

    /** @var    object  PEAR_Frontend   PEAR_Frontend(_CLI) object */
    var $ui;

    /**#@-*/

    // {{{ constructor, initializer
    /**
     *  Ethna_PearWrapper constructor
     *
     *  @access public
     */
    function Ethna_PearWrapper()
    {
        $this->channel = '';
        $this->config = null;
        $this->registry = null;
        $this->ui = null;
    }

    /**
     *  setup PEAR_Config and so on.
     *
     *  @param  string      $target     whether 'master' or 'local'
     *  @param  string|null $app_dir    if $target == 'local', specify the local
     *                                  application directory.
     *  @return true|Ethna_Error
     *  @access private
     */
    function &init($target = 'master', $app_dir = null, $channel = null)
    {
        $true = true;

        // setup PEAR_Frontend
        PEAR_Command::setFrontendType('CLI');
        $this->ui =& PEAR_Command::getFrontendObject();

        // PEAR's error handling rule
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$this->ui, 'displayFatalError'));
        set_error_handler('ethna_error_handler_skip_pear');

        // set channel
        $master_setting = Ethna_Handle::getMasterSetting('repositry');
        if ($channel !== null) {
            $this->channel = $channel;
        } else if (isset($master_setting["channel_{$target}"])) {
            $this->channel = $master_setting["channel_{$target}"];
        } else {
            $this->channel = 'pear.ethna.jp';
        }

        // setup PEAR_Config
        if ($target == 'master') {
            $ret =& $this->_setMasterConfig();
        } else {
            $ret =& $this->_setLocalConfig($app_dir);
        }
        if (Ethna::isError($ret)) {
            return $ret;
        }
        $this->ui->setConfig($this->config);

        // setup PEAR_Registry
        $this->registry =& $this->config->getRegistry();

        // Ethna's error handling rule
        Ethna::clearErrorCallback();
        set_error_handler('ethna_error_handler_skip_pear');

        return $true;
    }

    /**
     *  config for master.
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &_setMasterConfig()
    {
        $true = true;

        // setup config
        $this->config =& PEAR_Config::singleton();

        // setup channel
        $reg =& $this->config->getRegistry();
        if ($reg->channelExists($this->channel) == false) {
            $ret =& $this->doChannelDiscover();
            if (Ethna::isError($ret)) {
                return $ret;
            }
        }

        return $true;
    }

    /**
     *  config for local.
     *
     *  @param  string|null $app_dir    local application directory.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &_setLocalConfig($app_dir)
    {
        $true = true;

        // get application controller
        $app_ctl =& Ethna_Handle::getAppController($app_dir);
        if (Ethna::isError($app_ctl)) {
            return $app_ctl;
        }

        // determine dirs
        $base = $app_ctl->getBaseDir();
        $bin  = $app_ctl->getDirectory('bin');
        $tmp  = $app_ctl->getDirectory('tmp');
        $dirs = array(
                'php_dir'       => "{$base}/skel",
                'bin_dir'       => "{$bin}",
                'cache_dir'     => "{$tmp}/.pear/cache",
                'download_dir'  => "{$tmp}/.pear/download",
                'temp_dir'      => "{$tmp}/.pear/temp",
                'doc_dir'       => "{$tmp}/.pear/doc",
                'ext_dir'       => "{$tmp}/.pear/ext",
                'data_dir'      => "{$tmp}/.pear/data",
                'test_dir'      => "{$tmp}/.pear/test",
                );

        // mkdir
        foreach ($dirs as $key => $dir) {
            if (is_dir($dir) == false) {
                Ethna_Handle::mkdir($dir, 0755);
            }
        }

        $pearrc = "{$base}/skel/.pearrc";
        $this->config =& PEAR_Config::singleton($pearrc);

        // return if local .pearrc exists.
        if (is_file($pearrc) && is_readable($pearrc)) {
            $this->config->readConfigFile($pearrc);
            return $true;
        }

        // set dirs to config
        foreach ($dirs as $key => $dir) {
            $this->config->set($key, $dir);
        }

        // setup channel
        $reg =& $this->config->getRegistry();
        if ($reg->channelExists($this->channel) == false) {
            $ret =& $this->doChannelDiscover();
            if (Ethna::isError($ret)) {
                return $ret;
            }
        }
        $this->config->set('default_channel', $this->channel);

        // write local .pearrc
        $this->config->writeConfigFile();

        return $true;
    }
    // }}}

    // {{{ doClearCache
    /**
     *  do clear-cache (for local) 
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doClearCache()
    {
        $true = true;
        $r =& $this->_run('clear-cache', array(), array());
        if (PEAR::isError($r)) {
            return $r;
        }
        return $true;
    }
    // }}}

    // {{{ doChannelDiscover
    /**
     *  do channel-discover (for local) 
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doChannelDiscover()
    {
        $true = true;
        $r =& $this->_run('channel-discover', array(), array($this->channel));
        if (PEAR::isError($r)) {
            return $r;
        }
        return $true;
    }
    // }}}

    // {{{ isChannelExists
    /**
     *  whether channel discovered or not
     *
     *  @return bool
     *  @access private 
     */
    function isChannelExists()
    {
        return $this->registry->channelExists($this->channel);
    }
    // }}}

    // {{{ doChannelUpdate
    /**
     *  do channel-update (for local) 
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doChannelUpdate()
    {
        $true = true;
        if ($this->isChannelExists() == false) {
            $r =& $this->doChannelDiscover();
            if (PEAR::isError($r)) {
                return $r;
            }
        }
        $r =& $this->_run('channel-update', array(), array($this->channel));
        if (PEAR::isError($r)) {
            return $r;
        }
        return $true;
    }
    // }}}

    // {{{ doInstall
    /**
     *  do install
     *
     *  @param  string  $package    package name.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doInstall($package)
    {
        $true = true;
        if ($this->isInstalled($package)) {
            return Ethna::raiseNotice("{$this->channel}/{$package} already installed.");
        }
        $r =& $this->_run('install', array(), array("{$this->channel}/{$package}"));
        if (PEAR::isError($r)) {
            return $r;
        }
        if ($this->isInstalled($package) == false) {
            return Ethna::raiseError("install failed (check permission etc): {$this->channel}/{$package}");
        }
        return $true;
    }
    // }}}

    // {{{ doInstallFromTgz
    /**
     *  do install from local tgz file
     *
     *  @param  string  $pkg_file   local package filename
     *  @param  string  $pkg_name   package name.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doInstallFromTgz($pkg_file, $pkg_name)
    {
        $true = true;
        $r =& $this->_run('install', array(), array($pkg_file));
        if (PEAR::isError($r)) {
            return $r;
        }
        if ($this->isInstalled($pkg_name) == false) {
            return Ethna::raiseError("install failed (check permission etc): {$pkg_name}");
        }
        return $true;
    }
    // }}}

    // {{{ isInstalled
    /**
     *  check package installed
     *
     *  @param  string  $package package name
     *  @return bool
     *  @access private 
     */
    function isInstalled($package)
    {
        return $this->registry->packageExists($package, $this->channel);
    }
    // }}}

    // {{{ doUninstall
    /**
     *  do uninstall (packages installed with ethna command)
     *
     *  @param  string|null $app_dir    local application directory.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doUninstall($package)
    {
        $true = true;
        if ($this->isInstalled($package) == false) {
            return Ethna::raiseNotice("{$this->channel}/{$package} is not installed.");
        }
        $r =& $this->_run('uninstall', array(), array("{$this->channel}/{$package}"));
        if (PEAR::isError($r)) {
            return $r;
        }
        if ($this->isInstalled($package)) {
            return Ethna::raiseNotice("uninstall failed: {$this->channel}/{$package}");
        }
        return $true;
    }
    // }}}

    // {{{ getPackageNameFromTgz
    /**
     *  get package info from tar/tgz file.
     *
     *  @param  string  $filename   package file name.
     *  @return string  package name
     *  @access public
     */
    function &getPackageNameFromTgz($filename)
    {
        $packagefile =& new PEAR_PackageFile($this->config);
        $info =& $packagefile->fromTgzFile($filename, PEAR_VALIDATE_NORMAL);
        if (Ethna::isError($info)) {
            return $info;
        }
        $info_array =& $info->toArray();
        return $info_array['name'];
    }
    // }}}

    // {{{ getCanonicalPackageName
    /**
     *  get canonical package name (case sensitive)
     *
     *  @param  string  $package    package name.
     *  @return string  canonical name
     *  @access public
     */
    function &getCanonicalPackageName($package)
    {
        if ($this->isInstalled($package) == false) {
            return Ethna::raiseNotice("{$this->channel}/{$package} is not installed.");
        }
        $pobj =& $this->registry->getPackage($package, $this->channel);
        $cname = $pobj->getName();
        return $cname;
    }
    // }}}

    // {{{ doUpgrade
    /**
     *  do upgrade (packages installed with ethna command)
     *
     *  @param  string  $package    package name.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doUpgrade($package)
    {
        return $this->_run('upgrade', array(), array("{$this->channel}/{$package}"));
    }
    // }}}

    // {{{ doInfo
    /**
     *  do info (packages installed with ethna command)
     *
     *  @param  string  $package    package name.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doInfo($package)
    {
        return $this->_run('info', array(), array("{$this->channel}/{$package}"));
    }
    // }}}

    // {{{ doRemoteInfo
    /**
     *  do info (packages installable with ethna command)
     *
     *  @param  string  $package    package name.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doRemoteInfo($package)
    {
        return $this->_run('remote-info', array(), array("{$this->channel}/{$package}"));
    }
    // }}}

    // {{{ doUpgradeAll
    /**
     *  do upgrade-all
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doUpgradeAll()
    {
        return $this->_run('upgrade-all', array('channel' => "{$this->channel}"), array());
    }
    // }}}

    // {{{ doList
    /**
     *  do list (packages installed with ethna command)
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doList()
    {
        return $this->_run('list', array('channel' => $this->channel), array());
    }
    // }}}

    // {{{ doRemoteList
    /**
     *  do remote-list (packages installable with ethna command)
     *
     *  @param  string|null $app_dir    local application directory.
     *  @return true|Ethna_Error
     *  @access private 
     */
    function &doRemoteList()
    {
        return $this->_run('remote-list', array('channel' => $this->channel), array());
    }
    // }}}

    // {{{ subroutines.
    /**
     *  run PEAR_Command.
     *
     *  @param  string  $command    command name
     *  @param  array   $options    options
     *  @param  array   $params     parameters
     *  @return true|Ethna_Error
     *  @access private 
     *  @see PEAR_Command_Common::run, etc.
     */
    function &_run($command, $options, $params)
    {
        if ($this->config === null) {
            return Ethna::raiseError('configuration not initialized.');
        }

        $true = true;

        $cmd =& PEAR_Command::factory($command, $this->config);
        if (PEAR::isError($cmd)) {
            return $cmd;
        }
        $ret =& $cmd->run($command, $options, $params);
        if (PEAR::isError($ret)) {
            return $ret;
        }

        return $true;
    }

    /**
     *  provide yes-or-no dialog.
     *
     *  @return bool
     *  @access public
     */
    function confirmDialog($message, $default = 'yes')
    {
        $ret = $this->ui->userConfirm($message);
        return $ret;
    }
    // }}}

}
// }}}

// {{{ ethna_error_handler_skip_pear
/**
 *  skip error messages raised with '@expr' in PEAR codes.
 */
function ethna_error_handler_skip_pear($errno, $errstr, $errfile, $errline)
{
    if (defined('PEAR_CONFIG_DEFAULT_PHP_DIR') === false
        || strpos($errfile, PEAR_CONFIG_DEFAULT_PHP_DIR . '/PEAR')   !== 0
        && strpos($errfile, PEAR_CONFIG_DEFAULT_PHP_DIR . '/System') !== 0) {
        ethna_error_handler($errno, $errstr, $errfile, $errline);
    }
}
// }}}

?>
