<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

################################################################################
# Email Gatewayss
################################################################################

$collection->add('admin_emailgateways', new Route(
	'/email/incoming',
	array('_controller' => 'CloudAdminBundle:EmailGateways:list'),
	array(),
	array()
));

$collection->add('admin_emailgateways_new', new Route(
	'/email/incoming/new',
	array('_controller' => 'CloudAdminBundle:EmailGateways:editAccount', 'id' => 0),
	array(),
	array()
));

$collection->add('admin_emailgateways_newcloud', new Route(
	'/email/incoming/new-cloud',
	array('_controller' => 'CloudAdminBundle:EmailGateways:newCloudEmail'),
	array(),
	array()
));

$collection->add('admin_emailgateways_edit', new Route(
	'/email/incoming/accounts/{id}/edit',
	array('_controller' => 'CloudAdminBundle:EmailGateways:editAccount'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_emailgateways_quicktoggle', new Route(
	'/email/incoming/accounts/{id}/quick-toggle.json',
	array('_controller' => 'CloudAdminBundle:EmailGateways:quickToggle'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_emailgateways_del', new Route(
	'/email/incoming/accounts/{id}/delete/{security_token}',
	array('_controller' => 'CloudAdminBundle:EmailGateways:delete'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_emailgateways_testaccount', new Route(
	'/email/incoming/accounts/test-account.json',
	array('_controller' => 'CloudAdminBundle:EmailGateways:ajaxTest'),
	array('id' => '\\d+'),
	array()
));


################################################################################
# Email Transports
################################################################################

$collection->add('admin_emailtrans_set_default_from', new Route(
	'/email/outgoing/update-default-from',
	array('_controller' => 'CloudAdminBundle:EmailTransports:setDefaultFrom'),
	array(),
	array()
));

$collection->add('admin_emailtrans_list', new Route(
	'/email/outgoing',
	array('_controller' => 'CloudAdminBundle:EmailTransports:list'),
	array(),
	array()
));

$collection->add('admin_emailtrans_setup', new Route(
	'/setup/default-smtp',
	array('_controller' => 'CloudAdminBundle:EmailTransports:setup'),
	array(),
	array()
));

$collection->add('admin_emailtrans_newaccount', new Route(
	'/email/outgoing/accounts/new',
	array('_controller' => 'CloudAdminBundle:EmailTransports:editAccount', 'id' => 0),
	array(),
	array()
));

$collection->add('admin_emailtrans_editaccount', new Route(
	'/email/outgoing/accounts/{id}/edit',
	array('_controller' => 'CloudAdminBundle:EmailTransports:editAccount'),
	array(),
	array()
));

$collection->add('admin_emailtrans_del', new Route(
	'/email/outgoing/accounts/{id}/delete/{security_token}',
	array('_controller' => 'CloudAdminBundle:EmailTransports:delete'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_emailtrans_testaccount', new Route(
	'/email/outgoing/accounts/test-account.json',
	array('_controller' => 'CloudAdminBundle:EmailTransports:ajaxTest'),
	array(),
	array()
));

return $collection;