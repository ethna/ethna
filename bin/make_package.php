<?php
/**
 * This is the package.xml generator for Ethna
 *
 * @category pear
 */
require_once('PEAR/PackageFileManager.php');
$config = array(
    'package' => 'Ethna',
    'baseinstalldir' => 'Ethna',
    'version' => '0.2.0',
    'packagedirectory' => dirname(dirname(__FILE__)),
    'summary' => 'Ethna PHP Framework Packages',
    'notes'    => 'Ethna PHP',
    'state' => 'alpha',
    'license' => 'BSD',
    'filelistgenerator' => 'file',
    'ignore' => array('CVS/', 'make_package.php', 'package.xml'),
    'changelogoldtonew' => false,
    'description' => 'Ethna Framework Package',
    'simpleoutput' => true,
    );
 
print("Start Script\n");

$packagexml = new PEAR_PackageFileManager;
$packagexml->setOptions($config);
$packagexml->addRole('tpl', 'php');
$packagexml->addMaintainer('cocoitiban', 'contributor', 'Keita Arai', 'cocoiti@comio.info');
$packagexml->addMaintainer('halt', 'lead', 'halt feits', 'halt.hde@gmail.com');
$packagexml->addDependency('PEAR', '1.3.5', 'ge', 'pkg', false);
$packagexml->addDependency('php', '4.1.0', 'ge', 'php', false);

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
