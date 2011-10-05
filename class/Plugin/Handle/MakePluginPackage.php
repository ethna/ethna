<?php
// vim: foldmethod=marker
/**
 *  MakePluginPackage.php
 *
 *  please go to http://ethna.jp/ethna-document-dev_guide-pearchannel.html
 *  for more info.
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/PearWrapper.php';

// {{{ Ethna_Plugin_Handle_MakePluginPackage
/**
 *  make-plugin-package handler.
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Handle_MakePluginPackage extends Ethna_Plugin_Handle
{
    // {{{ _parseArgList()
    /**
     * @access private
     */
    function _parseArgList()
    {
        $r = $this->_getopt(
            array(
                'basedir=',
                'workdir=',
                'ini-file-path='
            )
        );
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        //  plugin directory path 
        $plugin_dir = array_shift($arg_list);
        if (empty($plugin_dir)) {
            return Ethna::raiseError('plugin directory path is not set.', 'usage');
        }

        //  basedir
        if (isset($opt_list['basedir'])) {
            $basedir = realpath(end($opt_list['basedir']));
        } else {
            $basedir = getcwd();
        }
        $plugin_dir = "$basedir/${plugin_dir}";

        // inifile
        $inifile = end($opt_list['ini-file_path']);
        if (empty($inifile)) {
            $inifiles = glob("${plugin_dir}/*.ini");
            if (empty($inifiles)) {
                return Ethna::raiseError(
                    "ERROR: no inifile found on ${plugin_dir}\n"
                  . ' please specify ini file path with [-i|--ini-file-path] option',
                    'usage'
                );
            }
            if (count($inifile) > 1) {
                return Ethna::raiseError(
                    'more than 2 .ini file found.'
                  . ' please specify [-i|--ini-file-path] option',
                    'usage'
                );
            }

            $inifile = array_shift($inifiles);
            $ini = parse_ini_file($inifile, true);
            if (empty($ini)) {
                return Ethna::raiseError(
                    "invalid ini file: $inifile"
                );
            }
        }

        return array($ini, $plugin_dir);
    }
    // }}}


    // {{{ perform()
    /**
     * @access public
     */
    function perform()
    {
        //    required package check.
        if (!file_exists_ex('PEAR/PackageFileManager2.php')
         || !file_exists_ex('PEAR/PackageFileManager/File.php')) {
            return Ethna::raiseError(
                "ERROR: PEAR_PackageFileManager2 is not installed! please install it.\n"
              . "usage: pear install -a pear/PackageFileManager2 "
            );
        }
 
        require_once 'PEAR/PackageFileManager2.php';
        require_once 'PEAR/PackageFileManager/File.php';

        // 引数の評価
        $args = $this->_parseArgList();
        if (Ethna::isError($args)) {
            return $args;
        }
        list($ini, $plugin_dir) = $args;

        // 設定用の配列を用意
        $setting = array();

        // プラグイン名
        $setting['pkgname'] = $ini['plugin']['name'];

        // パッケージの説明
        $setting['channel']     = $ini['package']['channel'];
        $setting['summary']     = $ini['package']['summary'];
        $setting['description'] = $ini['package']['description'];

        // リリースの説明
        $setting['version']     = $ini['release']['version'];
        $setting['state']       = $ini['release']['state'];
        $setting['notes']       = $ini['release']['notes'];

        // メンテナ
        $mnts = $ini['maintainers'];
        for ($i = 1; ; $i++) {
            if (isset($mnts["name$i"]) == false
                || isset($mnts["user$i"]) == false
                || isset($mnts["email$i"]) == false) {
                break;
            }
            $setting['maintainers'][] = array(
                'user'   => $mnts["user$i"],
                'name'   => $mnts["name$i"],
                'email'  => $mnts["email$i"],
                'role'   => isset($mnts["role$i"]) ? $mnts["role$i"] : 'lead',
                'active' => isset($mnts["active$i"]) ? $mnts["active$i"] == true: 'yes',
                );
        }

        // ライセンス
        $setting['license']['name'] = $ini['license']['name'];
        if (isset($ini['license']['uri'])) {
            $setting['license']['uri'] = $ini['license']['uri'];
        }

        // インストールディレクトリ
        $setting['config'] = array(
            'baseinstalldir' => 'Ethna/extlib/Plugin',
            );

        // 任意に $packagexml->doSomething() するための callback
        $setting['callback'] = array(
            'addPackageDepWithChannel'
                => array('optional', 'ethna', 'pear.ethna.jp', '2.6.0'),
            );

        // パッケージ作成
        $this->_makePackage($setting, $plugin_dir);
    }
    // }}}

    // {{{ _makePackage()
    /**
     * @access private
     */
    function _makePackage($setting, $workdir)
    {
        // package.xml を作る
        $pkgconfig = array(
            'packagedirectory' => $workdir,
            'outputdirectory' => $workdir,
            'ignore' => array('CVS/', '.cvsignore', '.svn/',
                              'package.xml', '*.ini', $setting['pkgname'].'-*.tgz'),
            'filelistgenerator' => 'file',
            'changelogoldtonew' => false,
            );

        $packagexml = new PEAR_PackageFileManager2();

        $pkgconfig = array_merge($pkgconfig, $setting['config']);
        $packagexml->setOptions($pkgconfig);

        $packagexml->setPackage($setting['pkgname']);
        $packagexml->setSummary($setting['summary']);
        $packagexml->setNotes($setting['notes']);
        $packagexml->setDescription($setting['description']);
        $packagexml->setChannel($setting['channel']);
        $packagexml->setAPIVersion($setting['version']);
        $packagexml->setReleaseVersion($setting['version']);
        $packagexml->setReleaseStability($setting['state']);
        $packagexml->setAPIStability($setting['state']);
        $packagexml->setPackageType('php');
        foreach ($setting['maintainers'] as $m) {
            $packagexml->addMaintainer($m['role'], $m['user'], $m['name'],
                                       $m['email'], $m['active']);
        }
        $packagexml->setLicense($setting['license']['name'],
                                $setting['license']['uri']);

        $packagexml->addRole('css', 'php');
        $packagexml->addRole('tpl', 'php');
        $packagexml->addRole('ethna', 'php');
        $packagexml->addRole('sh', 'script');
        $packagexml->addRole('bat', 'script');

        $packagexml->setPhpDep('4.3.0');
        $packagexml->setPearinstallerDep('1.3.5');

        $packagexml->generateContents();

        foreach ($setting['callback'] as $method => $params) {
            $r = call_user_func_array(array($packagexml, $method), $params);
        }

        $r = $packagexml->writePackageFile();
        if (PEAR::isError($r)) {
            return Ethna::raiseError($r->getMessage, $r->getCode());
        }

        //  finally make package
        PEAR_Command::setFrontendType('CLI');
        $ui = PEAR_Command::getFrontendObject();
        $config = PEAR_Config::singleton();
        $ui->setConfig($config);
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ui, 'displayFatalError'));
        $cmd = PEAR_Command::factory('package', $config);
        if (PEAR::isError($cmd)) {
            return Ethna::raiseError($cmd->getMessage, $cmd->getCode());
        }
        $r = $cmd->run('package', array(), array("$workdir/package.xml"));
        if (PEAR::isError($r)) {
            return Ethna::raiseError($r->getMessage, $r->getCode());
        }
    }
    // }}}

    // {{{ getUsage()
    /**
     *  @access public
     */
    function getUsage()
    {
        return <<<EOS
    {$this->id} [-b|--basedir=dir] [-i|--ini-file-path=file] [plugin_directory_path] 
EOS;
    }
    // }}}

    // {{{ getDescription()
    /**
     *  @access public
     */
    function getDescription()
    {
        return <<<EOS
make plugin package:
    {$this->id} [-b|--basedir=dir] [-i|--ini-file-path=file] [plugin_directory_path] 
EOS;
    }
    // }}}
}
// }}}
