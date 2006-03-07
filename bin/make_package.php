<?php
/**
 * This is the package.xml generator for Ethna
 *
 * @category pear
 */
 
// http://pear.php.net/package/PEAR_PackageFileManager/docs/1.6.0a4/PEAR_PackageFileManager/PEAR_PackageFileManager2.html#methodaddReplacement
require_once('PEAR/PackageFileManager2.php');

$description = 'Ethna is PHP WebApplication Framework Package.';
$varsion = '0.2.0';

$config = array(
    'baseinstalldir' => 'Ethna',
    'packagedirectory' => dirname(dirname(__FILE__)),
    'filelistgenerator' => 'file',
    'ignore' => array('CVS/', 'make_package.php', 'package.xml'),
    'changelogoldtonew' => false,
    'description' => $description,
    'simpleoutput' => true,
    );
 
print("Start Script\n");
$packagexml = new PEAR_PackageFileManager2;
$packagexml->setOptions($config);
$packagexml->setPackage('Ethna');
$packagexml->setSummary('Ethna PHP Framework Package');
$packagexml->setDescription($description);
$packagexml->setLicense('BSD', 'http://sourceforge.jp/projects/ethna/');
$packagexml->setReleaseVersion($varsion);
$packagexml->setAPIVersion($varsion);
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setNotes('Ethna Dev');

$packagexml->setPackageType('php');
$packagexml->addRole('tpl', 'php');

$packagexml->addMaintainer('lead', 'halt' , 'halt feits', 'halt.hde@gmail.com');
$packagexml->addMaintainer('contributor', 'cocoitiban', 'Keita Arai', 'cocoiti@comio.info');

$packagexml->setPhpDep('4.1.0');
$packagexml->setPearinstallerDep('1.3.5');
$packagexml->setPackageType('php');

//$packagexml->setChannel('channel.php.gr.jp');
$packagexml->setChannel('pear.php.net');

$packagexml->generateContents();


// note use of debugPackageFile() - this is VERY important
if (isset($_GET['make']) || $_SERVER['argv'][1] == 'make') {
    debug_print("writePackageFile\n");
    $result = $packagexml->writePackageFile();
} else {
    $result = $packagexml->debugPackageFile();
    debug_print("debugPackageFile\n");
}

if (PEAR::isError($result)) {
    debug_print($result->getMessage()."\n");
    exit();
}
debug_print("End Script\n");

function debug_print($message)
{
    return print($message);
}
?>
