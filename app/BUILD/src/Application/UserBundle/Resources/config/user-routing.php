<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

$collection->create('user', array(
    'path'       => '/',
    'controller' => 'UserBundle:Portal:portal',
    'defaults'   => array('_locale' => 'en'),
));

$collection->create('user_login', array(
    'path'       => '/login',
    'controller' => 'UserBundle:Login:index',
));

$collection->create('portal_login_usersource_sso', array(
    'path'         => '/login/usersource-sso/{usersource_id}',
    'controller'   => 'UserBundle:Login:usersourceSso',
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_usersource_sso', array(
    'path'         => '/login/usersource-sso/{usersource_id}',
    'controller'   => 'UserBundle:Login:usersourceSso',
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('user_logout', array(
    'path'       => '/logout/{auth}',
    'controller' => 'UserBundle:Login:logout',
));

$collection->create('user_saml_sls', array(
    'path'       => '/saml/sls/{usersource_id}',
    'controller' => 'UserBundle:Login:samlSingleLogoutService',
));

$collection->create('user_saml_metadata', array(
    'path'       => '/saml/metadata/{usersource_id}.xml',
    'controller' => 'UserBundle:Login:samlMetadata',
));

$collection->create('user_login_authenticate_local', array(
    'path'       => '/login/authenticate-password',
    'controller' => 'UserBundle:Login:authenticateLocal',
    'defaults'   => array('usersource_id' => 0),
));

$collection->create('portal_login_authenticate', array(
    'path'         => '/login/authenticate/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticate',
    'defaults'     => array('usersource_id' => 0),
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_authenticate', array(
    'path'         => '/login/authenticate/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticate',
    'defaults'     => array('usersource_id' => 0),
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('portal_login_callback', array(
    'path'         => '/login/authenticate-callback/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticateCallback',
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_callback', array(
    'path'         => '/login/authenticate-callback/{usersource_id}',
    'controller'   => 'UserBundle:Login:authenticateCallback',
    'requirements' => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_resetpass', array(
    'path'       => '/login/reset-password',
    'controller' => 'UserBundle:Login:resetPassword',
));

$collection->create('user_login_resetpass_send', array(
    'path'       => '/login/reset-password/send.{_format}',
    'controller' => 'UserBundle:Login:sendResetPassword',
    'defaults'   => array('_format' => 'html'),
));

$collection->create('user_login_resetpass_newpass', array(
    'path'         => '/login/reset-password/{code}',
    'controller'   => 'UserBundle:Login:resetPasswordNewPass',
    'requirements' => array('code' => '[A-Za-z0-9\\-]{17,}'),
));

$collection->create('user_login_agentlogin', array(
    'path'       => '/login/agent-login/{code}',
    'controller' => 'UserBundle:Login:authAgentLogin',
));

return $collection;
