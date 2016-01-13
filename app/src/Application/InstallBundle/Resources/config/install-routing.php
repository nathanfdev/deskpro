<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('install_checks', array(
    'path'       => '/server-checks',
    'controller' => 'InstallBundle:Install:index',
));

$collection->create('install_check_urls', array(
    'path'       => '/url-rewriting-check',
    'controller' => 'InstallBundle:Install:installRewriteCheck',
));

$collection->create('install_license', array(
    'path'       => '/',
    'controller' => 'InstallBundle:Install:license',
));

$collection->create('install_configedit', array(
    'path'       => '/config-editor',
    'controller' => 'InstallBundle:Install:configEditor',
));

$collection->create('install', array(
    'path'       => '/',
    'controller' => 'InstallBundle:Install:license',
));

$collection->create('install_verify_files', array(
    'path'       => '/verify-files',
    'controller' => 'InstallBundle:Install:verifyFiles',
));

$collection->create('install_verify_files_do', array(
    'path'       => '/verify-files/do/{batch}',
    'controller' => 'InstallBundle:Install:doVerifyFiles',
    'defaults'   => array('batch' => 0),
));

$collection->create('install_create_tables', array(
    'path'       => '/install-database',
    'controller' => 'InstallBundle:Install:createTables',
));

$collection->create('install_create_tables_do', array(
    'path'       => '/install-database/do/{batch}',
    'controller' => 'InstallBundle:Install:doCreateTables',
    'defaults'   => array('batch' => 0),
));

$collection->create('install_install_done', array(
    'path'       => '/install-done',
    'controller' => 'InstallBundle:Install:installDone',
));

return $collection;
