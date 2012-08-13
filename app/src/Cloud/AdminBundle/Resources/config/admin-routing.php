<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

################################################################################
# Dashboard
################################################################################

$collection->add('admin', new Route(
	'/',
	array('_controller' => 'CloudAdminBundle:Main:index'),
	array(),
	array()
));

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


################################################################################
# Server related stuff
################################################################################

$collection->add('admin_server_cron', new Route(
	'/server/cron',
	array('_controller' => 'CloudAdminBundle:Cron:list'),
	array(),
	array()
));

$collection->add('admin_server_cron_logs', new Route(
	'/server/cron/logs',
	array('_controller' => 'CloudAdminBundle:Cron:logs'),
	array(),
	array()
));

$collection->add('admin_server_cron_logs_clear', new Route(
	'/server/cron/logs/clear',
	array('_controller' => 'CloudAdminBundle:Cron:clearLogs'),
	array(),
	array()
));

$collection->add('admin_server_checks', new Route(
	'/server/checks',
	array('_controller' => 'CloudAdminBundle:Server:serverChecks'),
	array(),
	array()
));

$collection->add('admin_server_file_checks', new Route(
	'/server/file-integrity-checks',
	array('_controller' => 'CloudAdminBundle:Server:fileChecks'),
	array(),
	array()
));

$collection->add('admin_server_file_checks_do', new Route(
	'/server/file-integrity-checks/do/{batch}',
	array('_controller' => 'CloudAdminBundle:Server:fileChecksDo', 'batch' => '0'),
	array(),
	array()
));

$collection->add('admin_server_phpinfo', new Route(
	'/server/phpinfo',
	array('_controller' => 'CloudAdminBundle:Server:phpinfo'),
	array(),
	array()
));

$collection->add('admin_server_mysqlinfo', new Route(
	'/server/mysqlinfo',
	array('_controller' => 'CloudAdminBundle:Server:mysqlinfo'),
	array(),
	array()
));

$collection->add('admin_server_mysqlstatus', new Route(
	'/server/mysqlstatus',
	array('_controller' => 'CloudAdminBundle:Server:mysqlstatus'),
	array(),
	array()
));

$collection->add('admin_server_error_logs', new Route(
	'/server/error-logs',
	array('_controller' => 'CloudAdminBundle:Server:errorLogs'),
	array(),
	array()
));

$collection->add('admin_server_error_logs_clear', new Route(
	'/server/error-logs/clear-all',
	array('_controller' => 'CloudAdminBundle:Server:errorLogsClearAll'),
	array(),
	array()
));

$collection->add('admin_server_error_logs_view', new Route(
	'/server/error-logs/{log_id}',
	array('_controller' => 'CloudAdminBundle:Server:viewErrorLog'),
	array(),
	array()
));

$collection->add('admin_server_attach', new Route(
	'/server/attachments',
	array('_controller' => 'CloudAdminBundle:Server:attachments'),
	array(),
	array()
));

$collection->add('admin_server_attach_switch', new Route(
	'/server/attachments/switch',
	array('_controller' => 'CloudAdminBundle:Server:attachmentsSwitch'),
	array(),
	array()
));


return $collection;