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

$collection->create('admin', array(
    'path'       => '/',
    'controller' => 'AdminInterfaceBundle:Index:interface',
));

$collection->create('admin_tpl_loadmulti', array(
    'path'       => '/load-view/multi',
    'controller' => 'AdminInterfaceBundle:Interface:multiLoadView',
));

$collection->create('admin_tpl_load', array(
    'path'         => '/load-view/{view_name}',
    'controller'   => 'AdminInterfaceBundle:Interface:loadView',
    'requirements' => array('view_name' => '.+'),
));

$collection->create('admin_lang_load', array(
    'path'       => '/load-lang.{_format}',
    'controller' => 'AdminInterfaceBundle:Interface:loadLang',
));

$collection->create('admin_apps_download_package', array(
    'path'       => '/apps/download-package/{name}',
    'controller' => 'AdminInterfaceBundle:Apps:downloadPackage',
    'methods'    => array('GET'),
));

########################################################################################################################
# Start
########################################################################################################################

$collection->create('admin_start_index', array(
    'path'       => '/start',
    'controller' => 'AdminInterfaceBundle:Start:index',
));

########################################################################################################################
# Upgrade
########################################################################################################################

$collection->create('admin_upgrade_index', array(
    'path'       => '/upgrade',
    'controller' => 'AdminInterfaceBundle:Upgrade:index',
));

########################################################################################################################
# JIRA
########################################################################################################################

$collection->create('jira_token', array(
    'path'       => '/jira/request_token',
    'controller' => 'AdminInterfaceBundle:Jira:token',
));

########################################################################################################################
# Download authcoded files
########################################################################################################################

$collection->create('admin_download_export_file', array(
    'path'       => '/export/download/{code}',
    'controller' => 'AdminInterfaceBundle:Interface:downloadExportFile',
    'methods'    => array('GET'),
));

return $collection;
