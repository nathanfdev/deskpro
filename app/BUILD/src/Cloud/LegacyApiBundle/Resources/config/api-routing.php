<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('api_dpc_call_ping', [
    'path'       => '/dpc-call/ping',
    'controller' => 'CloudLegacyApiBundle:CloudCall:ping',
    'methods'    => ['GET'],
]);

$collection->create('api_dpc_call_resetpass', [
    'path'       => '/dpc-call/reset-password/{person_id}',
    'controller' => 'CloudLegacyApiBundle:CloudCall:resetPassword',
    'methods'    => ['GET', 'POST'],
]);

$collection->create('api_dpc_call_resetpass', [
    'path'       => '/dp_license/cloud/billing-login-token',
    'controller' => 'CloudLegacyApiBundle:License:getBillingLoginToken',
    'methods'    => ['GET'],
]);

$collection->create('api_cloud_urlsettings_setup_custom', [
    'path'       => '/settings/cloud/setup-custom-domain',
    'controller' => 'CloudLegacyApiBundle:Settings:setupCustomDomain',
    'methods'    => ['POST'],
]);

$collection->create('api_cloud_urlsettings', [
    'path'       => '/settings/cloud/url-settings',
    'controller' => 'CloudLegacyApiBundle:Settings:getUrlSettings',
    'methods'    => ['GET'],
]);

$collection->create('api_cloud_urlsettings_save', [
    'path'       => '/settings/cloud/url-settings',
    'controller' => 'CloudLegacyApiBundle:Settings:saveUrlSettings',
    'methods'    => ['POST'],
]);

return $collection;
