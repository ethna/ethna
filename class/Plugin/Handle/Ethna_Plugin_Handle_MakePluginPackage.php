<?php
// vim: foldmethod=marker
/**
 *  Ethna_Plugin_Handle_MakePluginPackage.php
 *
 *  please go to http://ethna.jp/ethna-document-dev_guide-pearchannel.html
 *  for more info.
 *
 *  @author     ICHII Takashi <ichii386@schweetheart.jp>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

require_once ETHNA_BASE . '/class/Ethna_PearWrapper.php';

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
    // {{{ perform()
    /**
     * @access public
     */
    function perform()
    {
        include_once 'PEAR/PackageFileManager.php';
        include_once 'PEAR/PackageFileaManager2.php';
        include_once 'PEAR/PackageFailManager/File.php';
        if (class_exists('PEAR_PackageFileManager2') === false) {
            return Ethna::raiseError(
                'make-plugin-package requires PEAR_PackageFileManager, please install.'
            );
        }

        // 引数の評価
        $args =& $this->_parseArgList();
        if (Ethna::isError($args)) {
            return $args;
        }
        list($ini, $skelfile, $workdir) = $args;

        // パッケージを作るプラグインの target
        $targets = array();
        if ($ini['plugin']['master'] == true) {
            $targets[] = 'master';
        }
        if ($ini['plugin']['local'] == true) {
            $targets[] = 'local';
        }

        // 設定用の配列を用意
        $setting = array();

        // {{{ master と local で共通の設定
        // プラグイン名
        $ptype = $ini['plugin']['type'];
        $pname = $ini['plugin']['name'];

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
        // }}}

        // まるまるコピー :-p
        $setting = array('master' => $setting, 'local' => $setting);

        // {{{ master と local で異なる部分
        // パッケージ名
        $setting['master']['pkgname'] = "Ethna_Plugin_{$ptype}_{$pname}";
        $setting['local'] ['pkgname'] = "App_Plugin_{$ptype}_{$pname}";

        // プラグインのファイル名
        $setting['master']['filename'] = "Ethna_Plugin_{$ptype}_{$pname}.php";
        $setting['local'] ['filename'] = "skel.plugin.{$ptype}_{$pname}.php";

        // 入力ファイルの置換マクロ
        $setting['master']['macro'] = array(
            // package 時に置換
            'application_id'    => 'Ethna',
            'project_id'        => 'Ethna',
            );
        $setting['local']['macro'] = array(
            // install 時に置換
            );

        // setOptins($config) 時に merge する設定
        $setting['master']['config'] = array(
            'baseinstalldir' => "Ethna/class/Plugin/{$ptype}",
            );
        $setting['local']['config'] = array(
            'baseinstalldir' => '.',
            );

        // 任意に $packagexml->doSomething() するための callback
        $setting['master']['callback'] = array(
            // TODO: 2.3.0リリース
            //'addPackageDepWithChannel'
            //    => array('optional', 'ethna', 'pear.ethna.jp', '2.3.0'),
            );
        $setting['local']['callback'] = array(
            // local 用のパッケージを master にインストールさせないための conflict
            'addConflictingPackageDepWithChannel'
                => array('pear', 'pear.php.net'),
            );
        // }}}


        // パッケージ作成
        $this->pear =& new Ethna_PearWrapper();
        $this->pear->init('master');
        foreach ($targets as $target) {
            $this->_makePackage($skelfile, $setting[$target], "$workdir/$target");
        }
    }
    // }}}

    // {{{ _makePackage()
    /**
     * @access private
     */
    function &_makePackage($skelfile, $setting, $workdir)
    {
        if (Ethna_Util::mkdir($workdir, 0755) === false) {
            return Ethna::raiseError("failed making working dir: $workdir.");
        }

        // プラグインの元ファイルを作成
        $rfp = fopen($skelfile, "r");
        if ($rfp == false) {
            return Ethna::raiseError("failed open skelton file: $skelfile.");
        }
        $outputfile = $setting['filename'];
        $wfp = fopen("$workdir/$outputfile", "w");
        if ($rfp == false) {
            fclose($rfp);
            return Ethna::raiseError("failed creating working file: $outputfile.");
        }
        for (;;) {
            $s = fread($rfp, 4096);
            if (strlen($s) == 0) {
                break;
            }
            foreach ($setting['macro'] as $k => $v) {
                $s = preg_replace("/{\\\$$k}/", $v, $s);
            }
            fwrite($wfp, $s);
        }
        fclose($wfp);
        fclose($rfp);

        // package.xml を作る
        $pkgconfig = array(
            'packagedirectory' => $workdir,
            'outputdirectory' => $workdir,
            'ignore' => array('CVS/', '.cvsignore', '.svn/',
                              'package.xml', 'package.ini', $setting['pkgname'].'-*.tgz'),
            'filelistgenerator' => 'file',
            'changelogoldtonew' => false,
            );

        $packagexml =& new PEAR_PackageFileManager2();

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

        $packagexml->setPhpDep('4.1.0');
        $packagexml->setPearinstallerDep('1.3.5');

        $packagexml->generateContents();

        foreach ($setting['callback'] as $method => $params) {
            $r = call_user_func_array(array(&$packagexml, $method), $params);
        }

        $r = $packagexml->writePackageFile();
        if (PEAR::isError($r)) {
            return Ethna::raiseError($r->getMessage, $r->getCode());
        }

        // package を作る
        $r = $this->pear->_run('package', array(), array("$workdir/package.xml"));
        if (PEAR::isError($r)) {
            return Ethna::raiseError($r->getMessage, $r->getCode());
        }

        if (Ethna_Util::purgeDir($workdir) === false) {
            return Ethna::raiseError("failed cleaning up working dir: $workdir.");
        }
    }
    // }}}

    // {{{ _parseArgList()
    /**
     * @access private
     */
    function &_parseArgList()
    {
        $r =& $this->_getopt(array('inifile=', 'skelfile=', 'workdir='));
        if (Ethna::isError($r)) {
            return $r;
        }
        list($opt_list, $arg_list) = $r;

        // inifile
        if (isset($opt_list['inifile'])
            && is_readable(end($opt_list['inifile']))) {
            $ini = parse_ini_file(end($opt_list['inifile']), true);
        } else {
            return Ethna::raiseError('give a valid inifile.');
        }

        // skelfile
        if (isset($opt_list['skelfile'])
            && is_readable(end($opt_list['skelfile']))) {
            $skelfile = end($opt_list['skelfile']);
        } else {
            return Ethna::raiseError('give a valid filename of plugin skelton file.');
        }

        // workdir
        if (isset($opt_list['workdir'])) {
            $workdir = end($opt_list['workdir']);
        } else {
            $workdir = getcwd();
        }

        return array($ini, $skelfile, $workdir);
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
    {$this->id} [-i|--inifile=file] [-s|--skelfile=file] [-w|--workdir=dir]

EOS;
    }
    // }}}
}
// }}}
?>
