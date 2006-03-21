<?php
error_reporting(E_ALL);
include_once '{$basedir}/app/{$project_id}_Controller.php';

{$project_id}_Controller::main('{$project_id}_Controller', array(
    '__ethna_unittest__',
    )
);
?>
