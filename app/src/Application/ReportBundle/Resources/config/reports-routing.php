<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;
use Application\DeskPRO\Routing\Route;

$collection = new RouteCollection();

$collection->create('report_login', array(
	'path'        => '/login',
	'controller'  => 'ReportBundle:Login:index',
));

$collection->create('report_logout', array(
	'path'        => '/logout/{auth}',
	'controller'  => 'ReportBundle:Login:logout',
));

$collection->create('report_login_authenticate_local', array(
	'path'        => '/login/authenticate-password',
	'controller'  => 'ReportBundle:Login:authenticateLocal',
	'defaults'    => array('usersource_id' => 0),
));

$collection->create('report', array(
	'path'        => '/old_home',
	'controller'  => 'ReportBundle:Overview:index',
));

$collection->create('report_overview_update_stat', array(
	'path'        => '/overview/update-stat/{type}',
	'controller'  => 'ReportBundle:Overview:updateStat',
));

$collection->create('report_agent_hours_index', array(
	'path'        => '/agent-hours',
	'controller'  => 'ReportBundle:AgentHours:index',
));

$collection->create('report_agent_hours_list_date', array(
	'path'        => '/agent-hours/{date}/{date2}',
	'controller'  => 'ReportBundle:AgentHours:list',
	'defaults'    => array('date2' => ''),
));

$collection->create('report_agent_activity_index', array(
	'path'        => '/agent-activity',
	'controller'  => 'ReportBundle:AgentActivity:index',
));

$collection->create('report_agent_activity_list', array(
	'path'        => '/agent-activity/list/{agent_or_team_id}/{date}',
	'controller'  => 'ReportBundle:AgentActivity:list',
));

$collection->create('report_agent_feedback_summary', array(
	'path'        => '/agent-feedback/summary/{date}',
	'controller'  => 'ReportBundle:AgentFeedback:summary',
	'defaults'    => array('date' => ''),
));

$collection->create('report_agent_feedback_feed', array(
	'path'        => '/agent-feedback/{page}',
	'controller'  => 'ReportBundle:AgentFeedback:feed',
	'defaults'    => array('page' => '0'),
));

$collection->create('report_publish', array(
	'path'        => '/publish',
	'controller'  => 'ReportBundle:ReportBuilder:index',
));

$collection->create('report_builder', array(
	'path'        => '/report-builder',
	'controller'  => 'ReportBundle:ReportBuilder:index',
));

$collection->create('report_builder_query', array(
	'path'        => '/report-builder/query',
	'controller'  => 'ReportBundle:ReportBuilder:query',
));

$collection->create('report_builder_parse', array(
	'path'        => '/report-builder/parse',
	'controller'  => 'ReportBundle:ReportBuilder:parse',
));

$collection->create('report_builder_new', array(
	'path'        => '/report-builder/new',
	'controller'  => 'ReportBundle:ReportBuilder:edit',
	'defaults'    => array('report_builder_id' => 0),
));

$collection->create('report_builder_report', array(
	'path'          => '/report-builder/{report_builder_id}/',
	'controller'    => 'ReportBundle:ReportBuilder:report',
	'requirements'  => array('report_builder_id' => '\\d+'),
));

$collection->create('report_builder_edit', array(
	'path'          => '/report-builder/{report_builder_id}/edit',
	'controller'    => 'ReportBundle:ReportBuilder:edit',
	'requirements'  => array('report_builder_id' => '\\d+'),
));

$collection->create('report_builder_delete', array(
	'path'          => '/report-builder/{report_builder_id}/delete',
	'controller'    => 'ReportBundle:ReportBuilder:delete',
	'requirements'  => array('report_builder_id' => '\\d+'),
));

$collection->create('report_builder_favorite', array(
	'path'          => '/report-builder/{report_builder_id}/favorite',
	'controller'    => 'ReportBundle:ReportBuilder:favorite',
	'requirements'  => array('report_builder_id' => '\\d+'),
));

$collection->create('report_billing', array(
	'path'        => '/billing',
	'controller'  => 'ReportBundle:Billing:index',
));

$collection->create('report_billing_report', array(
	'path'        => '/billing/{report_id}',
	'controller'  => 'ReportBundle:Billing:report',
));

return $collection;
