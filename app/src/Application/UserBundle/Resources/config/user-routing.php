<?php if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

// for email templates and the like in the DpKernel, we need a temporary place for the new routes. They are REPLICATED
// here, from the new portal's .yml files.

//TODO #portal remove these routes

$collection->create('portal_index', array(
    'path' => '/',
));
$collection->create('portal_news_view', array(
    'path' => '/news/posts/{slug}',
));
$collection->create('portal_news_unsubscribe_all', array(
    'path' => '/news/posts/subscriptions/unsubscribe',
));
$collection->create('portal_downloads_view', array(
    'path' => '/downloads/files/{slug}',
));
$collection->create('portal_downloads_unsubscribe_all', array(
    'path' => '/downloads/files/subscriptions/unsubscribe',
));
$collection->create('portal_kb_view', array(
    'path' => '/kb/articles/{slug}',
));
$collection->create('portal_kb_unsubscribe_all', array(
    'path' => '/kb/articles/subscriptions/unsubscribe',
));
$collection->create('portal_agent_login', array(
    'path' => ' /impersonate/agent-login/{code}',
));
$collection->create(
    'portal_feedback_view',
    array(
        'path' => '/feedback/view/{slug}',
    )
);

$collection->create('user', array(
    'path'        => '/',
    'controller'  => 'UserBundle:Portal:portal',
    'defaults'    => array('_locale' => 'en'),
));

$collection->create('user_jstell_login', array(
    'path'        => '/login/jstell/{jstell}/{security_token}/{usersource_id}',
    'controller'  => 'UserBundle:Login:jstellLogin',
));

$collection->create('user_login', array(
    'path'        => '/login',
    'controller'  => 'UserBundle:Login:index',
));

$collection->create('user_login_inline', array(
    'path'        => '/login/inline-login',
    'controller'  => 'UserBundle:Login:inlineLogin',
));

$collection->create('portal_login_usersource_sso', array(
    'path'          => '/login/usersource-sso/{usersource_id}',
    'controller'    => 'UserBundle:Login:usersourceSso',
    'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_usersource_sso', array(
    'path'          => '/login/usersource-sso/{usersource_id}',
    'controller'    => 'UserBundle:Login:usersourceSso',
    'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_logout', array(
    'path'        => '/logout/{auth}',
    'controller'  => 'UserBundle:Login:logout',
));

$collection->create('user_saml_sls', array(
    'path'        => '/saml/sls/{usersource_id}',
    'controller'  => 'UserBundle:Login:samlSingleLogoutService',
));

$collection->create('user_saml_metadata', array(
    'path'        => '/saml/metadata/{usersource_id}.xml',
    'controller'  => 'UserBundle:Login:samlMetadata',
));

$collection->create('user_login_authenticate_local', array(
    'path'        => '/login/authenticate-password',
    'controller'  => 'UserBundle:Login:authenticateLocal',
    'defaults'    => array('usersource_id' => 0),
));

$collection->create('portal_login_authenticate', array(
    'path'          => '/login/authenticate/{usersource_id}',
    'controller'    => 'UserBundle:Login:authenticate',
    'defaults'      => array('usersource_id' => 0),
    'requirements'                           => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_authenticate', array(
    'path'          => '/login/authenticate/{usersource_id}',
    'controller'    => 'UserBundle:Login:authenticate',
    'defaults'      => array('usersource_id' => 0),
    'requirements'                           => array('usersource_id' => '\\d+'),
));

$collection->create('portal_login_callback', array(
    'path'          => '/login/authenticate-callback/{usersource_id}',
    'controller'    => 'UserBundle:Login:authenticateCallback',
    'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_callback', array(
    'path'          => '/login/authenticate-callback/{usersource_id}',
    'controller'    => 'UserBundle:Login:authenticateCallback',
    'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_resetpass', array(
    'path'        => '/login/reset-password',
    'controller'  => 'UserBundle:Login:resetPassword',
));

$collection->create('user_login_resetpass_send', array(
    'path'        => '/login/reset-password/send.{_format}',
    'controller'  => 'UserBundle:Login:sendResetPassword',
    'defaults'    => array('_format' => 'html'),
));

$collection->create('user_login_resetpass_newpass', array(
    'path'          => '/login/reset-password/{code}',
    'controller'    => 'UserBundle:Login:resetPasswordNewPass',
    'requirements'  => array('code' => '[A-Za-z0-9\\-]{17,}'),
));

$collection->create('user_login_agentlogin', array(
    'path'        => '/login/agent-login/{code}',
    'controller'  => 'UserBundle:Login:authAgentLogin',
));

return $collection;
