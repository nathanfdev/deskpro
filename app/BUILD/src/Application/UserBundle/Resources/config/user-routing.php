<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('user', [
    'path'       => '/',
    'controller' => 'UserBundle:Portal:portal',
    'defaults'   => ['_locale' => 'en'],
]);

$collection->create('user_login', [
    'path'       => '/login',
    'controller' => 'UserBundle:Login:index',
]);

$collection->create('portal_login_usersource_sso', [
    'path'         => '/login/usersource-sso/{usersource_id}',
    'controller'   => 'UserBundle:Login:usersourceSso',
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('user_login_usersource_sso', [
    'path'         => '/login/usersource-sso/{usersource_id}',
    'controller'   => 'UserBundle:Login:usersourceSso',
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('user_saml_sls', [
    'path'       => '/saml/sls/{usersource_id}',
    'controller' => 'UserBundle:Login:samlSingleLogoutService',
]);

$collection->create('user_saml_metadata', [
    'path'       => '/saml/metadata/{usersource_id}.xml',
    'controller' => 'UserBundle:Login:samlMetadata',
]);

$collection->create('user_login_authenticate_local', [
    'path'       => '/login/authenticate-password',
    'controller' => 'UserBundle:Login:authenticateLocal',
    'defaults'   => ['usersource_id' => 0],
]);

$collection->create('portal_login_authenticate', [
    'path'         => '/login/authenticate/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticate',
    'defaults'     => ['usersource_id' => 0],
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('user_login_authenticate', [
    'path'         => '/login/authenticate/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticate',
    'defaults'     => ['usersource_id' => 0],
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('portal_login_callback', [
    'path'         => '/login/authenticate-callback/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticateCallback',
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('user_login_callback', [
    'path'         => '/login/authenticate-callback/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticateCallback',
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('user_login_resetpass', [
    'path'       => '/login/reset-password',
    'controller' => 'UserBundle:Login:resetPassword',
]);

$collection->create('user_login_resetpass_send', [
    'path'       => '/login/reset-password/send.{_format}',
    'controller' => 'UserBundle:Login:sendResetPassword',
    'defaults'   => ['_format' => 'html'],
]);

$collection->create('user_login_resetpass_newpass', [
    'path'         => '/login/reset-password/{code}',
    'controller'   => 'UserBundle:Login:resetPasswordNewPass',
    'requirements' => ['code' => '[A-Za-z0-9\\-]{17,}'],
]);

$collection->create('user_login_agentlogin', [
    'path'       => '/login/agent-login/{code}',
    'controller' => 'UserBundle:Login:authAgentLogin',
]);

return $collection;
