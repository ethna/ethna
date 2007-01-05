<?php
/**
 *  ethna_make_package.php
 *
 *  package.xml generator for Ethna
 *
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */
require_once 'PEAR.php';
require_once 'Console/Getopt.php';
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';   // avoid bugs

// args
$getopt =& new Console_Getopt();
$arg_list = $getopt->readPHPArgv();
$state = "stable";
$is_old_package = false;
$get_version = false;

$r = $getopt->getopt($arg_list, "abov", array("alpha", "beta", "old-package", "version"));
foreach ($r[0] as $opt) {
    if ($opt[0] == "a" || $opt[0] == "--alpha") {
        $state = "alpha";
    }
    if ($opt[0] == "b" || $opt[0] == "--beta") {
        $state = "beta";
    }
    if ($opt[0] == "o" || $opt[0] == "--old-package") {
        $is_old_package = true;
    }
    if ($opt[0] == "v" || $opt[0] == "--version") {
        $get_version = true;
    }
}

$description = 'Ethna Web Application Framework';
$package = 'Ethna';

// x.0.y -> beta
// x.1.y -> stable
$major_version = "2.3";
$minor_version = "1";

if ($state == 'alpha' || $state == 'beta') {
    $version = $major_version . strftime('.%Y%m%d%H');
} else {
    $version = $major_version . "." . $minor_version;
}

if ($get_version) {
    print $version;
    exit();
}

$config = array(
    'baseinstalldir' => 'Ethna',
    'packagedirectory' => dirname(dirname(__FILE__)),
    'filelistgenerator' => 'file',
    'ignore' => array('CVS/', 'package.xml', 'ethna_make_package.php', 'ethna_make_package.sh'),
    'changelogoldtonew' => false,
    'exceptions' => array('README' => 'doc', 'LICENSE' => 'doc', 'CHANGES' => 'doc'),
    'description' => $description,
    'exceptions' => array('bin/ethna.sh' => 'script', 'bin/ethna.bat' => 'script'),
    'installexceptions' => array('bin/ethna.sh' => '/', 'bin/ethna.bat' => '/'),
    'installas' => array('bin/ethna.sh' => 'ethna', 'bin/ethna.bat' => 'ethna.bat'),
);
 
$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions($config);
$packagexml->setPackage($package);
$packagexml->setSummary('Ethna PHP Framework Package');
$packagexml->setDescription($description);
$packagexml->setChannel('pear.ethna.jp');
$packagexml->setAPIVersion($version);
$packagexml->setReleaseVersion($version);
$packagexml->setReleaseStability($state);
$packagexml->setAPIStability($state);
$packagexml->setNotes('Ethna PHP Web Application Framework');
$packagexml->setPackageType('php');

$packagexml->addRole('*', 'php');

$packagexml->setPhpDep('4.1.0');
$packagexml->setPearinstallerDep('1.3.5');

$packagexml->addMaintainer('lead', 'fujimoto' , 'Masaki Fujimoto', 'fujimoto@php.net');
$packagexml->addMaintainer('lead', 'halt' , 'halt feits', 'halt.feits@gmail.com');
$packagexml->addMaintainer('lead', 'cocoitiban', 'Keita Arai', 'cocoiti@comio.info');
$packagexml->addMaintainer('lead', 'ichii386', 'ICHII Takashi', 'ichii386@schweetheart.jp');

$packagexml->setLicense('The BSD License', 'http://www.opensource.org/licenses/bsd-license.php');

$packagexml->addReplacement('bin/ethna.bat', 'pear-config', '@PEAR-DIR@', 'php_dir');
$packagexml->addReplacement('bin/ethna.bat', 'pear-config', '@PHP-BIN@', 'bin_dir');
$packagexml->addReplacement('bin/ethna.sh', 'pear-config', '@PEAR-DIR@', 'php_dir');
$packagexml->addReplacement('bin/ethna.sh', 'pear-config', '@PHP-BIN@', 'bin_dir');

$packagexml->addRelease();
$packagexml->setOSInstallCondition('windows');
$packagexml->addInstallAs('bin/ethna.bat', 'ethna.bat');
$packagexml->addIgnoreToRelease('bin/ethna.sh');
$packagexml->addRelease();
$packagexml->addInstallAs('bin/ethna.sh', 'ethna');
$packagexml->addIgnoreToRelease('bin/ethna.bat');

$packagexml->generateContents();

if ($is_old_package) {
    $pkg =& $packagexml->exportCompatiblePackageFile1();
    $pkg->writePackageFile();
} else {
    $packagexml->writePackageFile();
}
?>
