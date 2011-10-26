<?php
// vim: foldmethod=marker
/**
 *  PearWrapper.php
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once 'PEAR.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/Command.php';
require_once 'PEAR/PackageFile.php';

// {{{ Ethna_PearWrapper
/**
 *  wrapper class for PEAR_Command
 *  This class should be instantiated in ethna handler.
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_PearWrapper
{
    // {{{ properties
    /**#@+
     *  @access     private
     */

    /** @protected    string  channel url of ethna repositry */
    protected $channel;

    /** @protected    string  target, 'master' or 'local' */
    protected $target;

    /** @protected    object  controller object collesponding to the target */
    protected $target_ctl;

    /** @protected    object  PEAR_Config     PEAR_Config object */
    protected $config;

    /** @protected    object  PEAR_Registry   PEAR_Registry object */
    protected $registry;

    /** @protected    object  PEAR_Frontend   PEAR_Frontend(_CLI) object */
    protected $ui;

    /** @protected    array   options for pearcmd */
    protected $_pearopt;

    /**#@-*/
    // }}}

    // {{{ constructor, initializer
    /**
     *  Ethna_PearWrapper constructor
     *
     *  @access public
     */
    public function __construct()
    {
        $this->channel = null;
        $this->config = null;
        $this->registry = null;
        $this->ui = null;
        $this->target = null;
        $this->target_ctl = null;
    }

    /**
     *  setup PEAR_Config and so on.
     *
     *  @param  string      $target     whether 'master' or 'local'
     *  @param  string|null $app_dir    local application directory.
     *  @param  string|null $channel    channel for the package repository.
     *  @return true|Ethna_Error
     */
    public function init($target, $app_dir = null, $channel = null)
    {
        $true = true;
        if ($target == 'master') {
            $this->target = 'master';
        } else {
            // default target is 'local'.
            $this->target = 'local';
        }

        // setup PEAR_Frontend
        PEAR_Command::setFrontendType('CLI');
        $this->ui = PEAR_Command::getFrontendObject();

        // set PEAR's error handling
        // TODO: if PEAR/Command/Install.php is newer than 1.117, displayError goes well.
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this->ui, 'displayFatalError'));

        // set channel
        $master_setting = Ethna_Handle::getMasterSetting('repositry');
        if ($channel !== null) {
            $this->channel = $channel;
        } else if (isset($master_setting["channel_{$target}"])) {
            $this->channel = $master_setting["channel_{$target}"];
        } else {
            $this->channel = 'pear.ethna.jp';
        }

        // set target controller
        if ($target == 'master') {
            $this->target_ctl = Ethna_Handle::getEthnaController();
        } else {
            $this->target_ctl = Ethna_Handle::getAppController($app_dir);
        }
        if (Ethna::isError($this->target_ctl)) {
            return $this->target_ctl;
        }

        // setup PEAR_Config
        if ($target == 'master') {
            $ret = $this->_setMasterConfig();
        } else {
            $ret = $this->_setLocalConfig();
        }
        if (Ethna::isError($ret)) {
            return $ret;
        }
        $this->ui->setConfig($this->config);

        // setup PEAR_Registry
        $this->registry = $this->config->getRegistry();

        return $true;
    }

    /**
     *  config for master.
     *
     *  @return true|Ethna_Error
     *  @access private 
     */
    private function _setMasterConfig()
    {
        $true = true;

        // setup config
        $this->config = PEAR_Config::singleton();

        // setup channel
        $reg = $this->config->getRegistry();
        if ($reg->channelExists($this->channel) == false) {
            $ret = $this->doChannelDiscover();
            if (Ethna::isError($ret)) {
                return $ret;
            }
        }

        return $true;
    }

    /**
     *  config for local.
     *
     *  @return true|Ethna_Error
     *  @access protected
     */
    protected function _setLocalConfig()
    {
        $true = true;

        // determine dirs
        $base = $this->target_ctl->getBaseDir();
        $bin  = $this->target_ctl->getDirectory('bin');
        $tmp  = $this->target_ctl->getDirectory('tmp');
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
                Ethna_Util::mkdir($dir, 0755);
            }
        }

        $pearrc = "{$base}/skel/.pearrc";
        $this->config = PEAR_Config::singleton($pearrc);

        // read local .pearrc if exists.
        if (is_file($pearrc) && is_readable($pearrc)) {
            $this->config->readConfigFile($pearrc);
        }

        // set dirs to config
        foreach ($dirs as $key => $dir) {
            $this->config->set($key, $dir);
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
        $this->config->writeConfigFile();

        return $true;
    }
    // }}}

    // {{{ doClearCache
    /**
     *  do clear-cache
     *
     *  @return true|Ethna_Error
     */
    public function doClearCache()
    {
        $true = true;
        $r = $this->_run('clear-cache', array(), array());
        if (PEAR::isError($r)) {
            return $r;
        }
        return $true;
    }
    // }}}

    // {{{ doChannelDiscover
    /**
     *  do channel-discover
     *
     *  @return true|Ethna_Error
     */
    public function doChannelDiscover()
    {
        $true = true;
        $r = $this->_run('channel-discover', array(), array($this->channel));
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
     */
    public function isChannelExists()
    {
        return $this->registry->channelExists($this->channel);
    }
    // }}}

    // {{{ doChannelUpdate
    /**
     *  do channel-update
     *
     *  @return true|Ethna_Error
     */
    public function doChannelUpdate()
    {
        $true = true;
        if ($this->isChannelExists() == false) {
            $r = $this->doChannelDiscover();
            if (PEAR::isError($r)) {
                return $r;
            }
        }
        $r = $this->_run('channel-update', array(), array($this->channel));
        if (PEAR::isError($r)) {
            return $r;
        }
        return $true;
    }
    // }}}

    // {{{ _doInstallOrUpgrade
    /**
     *  do install
     *
     *  @param  string  $command    'install' or 'upgrade'
     *  @param  string  $package    package string
     *  @return true|Ethna_Error
     *  @access private 
     */
    private function _doInstallOrUpgrade($command, $package)
    {
        $true = true;
        $r = $this->_run($command, array(), array($package));
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
     *  @param  string  $pkg_name   package name.
     *  @param  string  $state      package state.
     *  @return true|Ethna_Error
     */
    public function doInstall($pkg_name, $state = null)
    {
        $pkg = "{$this->channel}/{$pkg_name}";
        if ($state !== null) {
            $pkg = "{$pkg}-{$state}";
        }
        $r = $this->_doInstallOrUpgrade('install', $pkg); 
        return $r;
    }
    // }}}

    // {{{ doInstallFromTgz
    /**
     *  do install from local tgz file
     *
     *  @param  string  $pkg_file   package filename
     *  @return true|Ethna_Error
     */
    public function doInstallFromTgz($pkg_file)
    {
        $r = $this->_doInstallOrUpgrade('install', $pkg_file); 
        return $r;
    }
    // }}}

    // {{{ doUpgrade
    /**
     *  do upgrade
     *
     *  @param  string  $pkg_name   package name.
     *  @param  string  $state      package state.
     *  @return true|Ethna_Error
     */
    public function doUpgrade($pkg_name, $state = null)
    {
        $pkg = "{$this->channel}/{$pkg_name}";
        if ($state !== null) {
            $pkg = "{$pkg}-{$state}";
        }
        $r = $this->_doInstallOrUpgrade('upgrade', $pkg);
        return $r;
    }
    // }}}

    // {{{ doUpgradeFromTgz
    /**
     *  do upgrade from local tgz file
     *
     *  @param  string  $pkg_file   package filename
     *  @return true|Ethna_Error
     */
    public function doUpgradeFromTgz($pkg_file)
    {
        $r = $this->_doInstallOrUpgrade('upgrade', $pkg_file); 
        return $r;
    }
    // }}}

    // {{{ isInstalled
    /**
     *  check package installed
     *
     *  @param  string  $package package name
     *  @return bool
     */
    public function isInstalled($package)
    {
        return $this->registry->packageExists($package, $this->channel);
    }
    // }}}

    // {{{ getVersion
    /**
     *  get package version
     *
     *  @param  string  $package package name
     *  @return string  version string
     */
    public function getVersion($package)
    {
        $pobj = $this->registry->getPackage($package, $this->channel);
        return $pobj->getVersion();
    }
    // }}}

    // {{{ getState
    /**
     *  get package version
     *
     *  @param  string  $package package name
     *  @return string  version string
     */
    public function getState($package)
    {
        $pobj = $this->registry->getPackage($package, $this->channel);
        return $pobj->getState();
    }
    // }}}

    // {{{ doUninstall
    /**
     *  do uninstall (packages installed with ethna command)
     *
     *  @return true|Ethna_Error
     */
    public function doUninstall($package)
    {
        $true = true;
        if ($this->isInstalled($package) == false) {
            return Ethna::raiseNotice("{$this->channel}/{$package} is not installed.");
        }
        $r = $this->_run('uninstall', array(), array("{$this->channel}/{$package}"));
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
     *  @static
     */
    public function getPackageNameFromTgz($filename)
    {
        $config = PEAR_Config::singleton();
        $packagefile = new PEAR_PackageFile($config);
        $info = $packagefile->fromTgzFile($filename, PEAR_VALIDATE_NORMAL);
        if (PEAR::isError($info)) {
            return $info;
        }
        $info_array = $info->toArray();
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
    public function getCanonicalPackageName($package)
    {
        if ($this->isInstalled($package) == false) {
            return Ethna::raiseNotice("{$this->channel}/{$package} is not installed.");
        }
        $pobj = $this->registry->getPackage($package, $this->channel);
        $cname = $pobj->getName();
        return $cname;
    }
    // }}}

    // {{{ getInstalledPackageList
    /**
     *  get installed package list
     *
     *  @return array   installed package list
     *  @access public
     */
    public function getInstalledPackageList()
    {
        $ret = array();
        foreach ($this->registry->listPackages($this->channel) as $pkg) {
            $ret[] = $this->getCanonicalPackageName($pkg);
        }
        return $ret;
    }
    // }}}

    // {{{ doInfo
    /**
     *  do info (packages installed with ethna command)
     *
     *  @param  string  $package    package name.
     *  @return true|Ethna_Error
     */
    public function doInfo($package)
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
     */
    public function doRemoteInfo($package)
    {
        return $this->_run('remote-info', array(), array("{$this->channel}/{$package}"));
    }
    // }}}

    // {{{ doUpgradeAll
    /**
     *  do upgrade-all
     *
     *  @return true|Ethna_Error
     */
    public function doUpgradeAll()
    {
        return $this->_run('upgrade-all', array('channel' => "{$this->channel}"), array());
    }
    // }}}

    // {{{ doList
    /**
     *  do list (packages installed with ethna command)
     *
     *  @return true|Ethna_Error
     */
    public function doList()
    {
        return $this->_run('list', array('channel' => $this->channel), array());
    }
    // }}}

    // {{{ doRemoteList
    /**
     *  do remote-list (packages installable with ethna command)
     *
     *  @return true|Ethna_Error
     */
    public function doRemoteList()
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
    protected function _run($command, $options, $params)
    {
        if ($this->config === null) {
            return Ethna::raiseError('configuration not initialized.');
        }

        $true = true;

        $cmd = PEAR_Command::factory($command, $this->config);
        if (PEAR::isError($cmd)) {
            return $cmd;
        }

        // pear command options
        if (is_array($this->_pearopt) && count($this->_pearopt) > 0) {
            $pearopts = $this->_getPearOpt($cmd, $command, $this->_pearopt);
            $options = array_merge($pearopts, $options);
        }

        $ret = $cmd->run($command, $options, $params);
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
    public function confirmDialog($message, $default = 'yes')
    {
        $ret = $this->ui->userConfirm($message);
        return $ret;
    }

    /**
     *  provide table layout
     *
     *  @param  array   $headline   headline
     *  @param  array   $rows       rows which have the same size as headline's.
     *  @access public
     */
    public function displayTable($caption, $headline, $rows)
    {
        // spacing
        foreach (array_keys($headline) as $k) {
            $headline[$k] = sprintf('% -8s', $headline[$k]);
        }

        $data = array('caption'  => $caption,
                      'border'   => true,
                      'headline' => $headline,
                      'data'     => $rows);
        $this->ui->outputData($data);
    }

    /**
     *  (experimental)
     *  @access public
     */
    public function setPearOpt($pearopt)
    {
        $this->_pearopt = $pearopt;
    }

    /**
     *  (experimental)
     *  @return array
     */
    private function _getPearOpt($cmd_obj, $cmd_str, $opt_array)
    {
        $short_args = $long_args = null;
        PEAR_Command::getGetOptArgs($cmd_str, $short_args, $long_args);
        $opt = new Ethna_Getopt();
        $opt_arg = $opt->getopt($opt_array, $short_args, $long_args);
        if (Ethna::isError($opt_arg)) return array();
        $opts = array();
        foreach ($opt_arg[0] as $tmp) {
            list($opt, $val) = $tmp;
            if ($val === null) $val = true;
            if (strlen($opt) == 1) {
                $cmd_opts = $cmd_obj->getOptions($cmd_str);
                foreach ($cmd_opts as $o => $d) {
                    if (isset($d['shortopt']) && $d['shortopt'] == $opt) {
                        $opts[$o] = $val;
                    }
                }
            } else {
                if (substr($opt, 0, 2) == '--') $opts[substr($opt, 2)] = $val;
            }
        }
        return $opts;
    }
                

    // }}}
}
// }}}

