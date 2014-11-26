<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('install_checks', array(
    'path'        => '/server-checks',
    'controller'  => 'InstallBundle:Install:index',
));

$collection->create('install_check_urls', array(
    'path'        => '/url-rewriting-check',
    'controller'  => 'InstallBundle:Install:installRewriteCheck',
));

$collection->create('install_license', array(
    'path'        => '/',
    'controller'  => 'InstallBundle:Install:license',
));

$collection->create('install_configedit', array(
    'path'        => '/config-editor',
    'controller'  => 'InstallBundle:Install:configEditor',
));

$collection->create('install', array(
    'path'        => '/',
    'controller'  => 'InstallBundle:Install:license',
));

$collection->create('install_verify_files', array(
    'path'        => '/verify-files',
    'controller'  => 'InstallBundle:Install:verifyFiles',
));

$collection->create('install_verify_files_do', array(
    'path'        => '/verify-files/do/{batch}',
    'controller'  => 'InstallBundle:Install:doVerifyFiles',
    'defaults'    => array('batch' => 0),
));

$collection->create('install_create_tables', array(
    'path'        => '/install-database',
    'controller'  => 'InstallBundle:Install:createTables',
));

$collection->create('install_create_tables_do', array(
    'path'        => '/install-database/do/{batch}',
    'controller'  => 'InstallBundle:Install:doCreateTables',
    'defaults'    => array('batch' => 0),
));

$collection->create('install_install_data', array(
    'path'        => '/install-data',
    'controller'  => 'InstallBundle:Install:installData',
));

$collection->create('install_install_data_save', array(
    'path'        => '/install-data/save',
    'controller'  => 'InstallBundle:Install:installDataSave',
));

$collection->create('install_install_done', array(
    'path'        => '/install-done',
    'controller'  => 'InstallBundle:Install:installDone',
));

$collection->create('install_send_install_report_error', array(
    'path'        => '/install-report-error',
    'controller'  => 'InstallBundle:Install:sendInstallReportError',
));

return $collection;
