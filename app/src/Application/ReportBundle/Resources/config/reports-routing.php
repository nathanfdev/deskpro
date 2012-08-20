<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('report_login', new Route(
	'/login',
	array('_controller' => 'ReportBundle:Login:index'),
	array(),
	array()
));

$collection->add('report_logout', new Route(
	'/logout/{auth}',
	array('_controller' => 'ReportBundle:Login:logout'),
	array(),
	array()
));

$collection->add('report_login_authenticate_local', new Route(
	'/login/authenticate-password',
	array('_controller' => 'ReportBundle:Login:authenticateLocal', 'usersource_id' => 0),
	array(),
	array()
));

################################################################################
# Trends
################################################################################

$collection->add('report', new Route(
	'/',
	array('_controller' => 'ReportBundle:Overview:index'),
	array(),
	array()
));

$collection->add('report_overview_update_stat', new Route(
	'/overview/update-stat/{type}',
	array('_controller' => 'ReportBundle:Overview:updateStat'),
	array(),
	array()
));

$collection->add('report_trend_index', new Route(
	'/trends',
	array('_controller' => 'ReportBundle:Trend:index'),
	array(),
	array()
));

$collection->add('report_trend_new', new Route(
	'/trends/new',
	array('_controller' => 'ReportBundle:Trend:new'),
	array(),
	array()
));

$collection->add('report_trend_edit', new Route(
	'/trends/{stat_id}/edit',
	array('_controller' => 'ReportBundle:Trend:edit'),
	array('stat_id' => '\\d+'),
	array()
));

$collection->add('report_trend_clone', new Route(
	'/trends/{stat_id}/clone',
	array('_controller' => 'ReportBundle:Trend:clone'),
	array('stat_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_index', new Route(
	'/trends/dashboards',
	array('_controller' => 'ReportBundle:Dashboard:index'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_view', new Route(
	'/trends/dashboards/{dashboard_id}',
	array('_controller' => 'ReportBundle:Dashboard:view'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_new', new Route(
	'/trends/dashboards/new',
	array('_controller' => 'ReportBundle:Dashboard:new'),
	array(),
	array()
));

$collection->add('report_trend_dashboard_edit', new Route(
	'/trends/dashboards/{dashboard_id}/edit',
	array('_controller' => 'ReportBundle:Dashboard:edit'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_updateorders', new Route(
	'/trends/dashboards/update-orders.json',
	array('_controller' => 'ReportBundle:Dashboard:ajaxUpdateOrders'),
	array(),
	array()
));

$collection->add('report_trend_dashboard_delete', new Route(
	'/trends/dashboards/{dashboard_id}/delete',
	array('_controller' => 'ReportBundle:Dashboard:delete'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_newwidget', new Route(
	'/trends/dashboards/{dashboard_id}/widget/new',
	array('_controller' => 'ReportBundle:Dashboard:newWidget'),
	array(),
	array()
));

$collection->add('report_trend_dashboard_stat_new', new Route(
	'/trends/dashboards/{dashboard_id}/new-stat/{stat_type}',
	array('_controller' => 'ReportBundle:Dashboard:dashboardStatNew'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_stat_edit', new Route(
	'/trends/dashboards/{dashboard_id}/edit-stat/{dashboard_stat_id}',
	array('_controller' => 'ReportBundle:Dashboard:dashboardStatEdit'),
	array('dashboard_id' => '\\d+', 'dashboard_stat_id' => '\\d+'),
	array()
));

$collection->add('report_trend_dashboard_remove_stat', new Route(
	'/trends/dashboards/{dashboard_id}/remove-stat/{dashboard_stat_id}',
	array('_controller' => 'ReportBundle:Dashboard:removeStat'),
	array('dashboard_id' => '\\d+', 'dashboard_stat_id' => '\\d+'),
	array()
));

$collection->add('report_dashboard_ajaxfetchwidgets', new Route(
	'/trends/dashboards/{dashboard_id}/ajax-fetch-widget',
	array('_controller' => 'ReportBundle:Dashboard:ajaxFetchWidgets'),
	array('dashboard_id' => '\\d+'),
	array()
));

$collection->add('report_dashboard_ajaxcreatewidget', new Route(
	'/trends/dashboards/{dashboard_id}/ajax-create-widget/{stat_id}',
	array('_controller' => 'ReportBundle:Dashboard:ajaxCreateWidget'),
	array('dashboard_id' => '\\d+', 'stat_id' => '\\d+'),
	array()
));

$collection->add('report_dashboard_ajaxeditwidget', new Route(
	'/trends/dashboards/{dashboard_id}/ajax-edit-widget/{dashboard_stat_id}',
	array('_controller' => 'ReportBundle:Dashboard:ajaxEditWidget'),
	array('dashboard_id' => '\\d+', 'dashboard_stat_id' => '\\d+'),
	array()
));

$collection->add('report_dashboard_ajaxdeletewidget', new Route(
	'/trends/dashboards/{dashboard_id}/ajax-delete-widget/{dashboard_stat_id}',
	array('_controller' => 'ReportBundle:Dashboard:ajaxDeleteWidget'),
	array('dashboard_id' => '\\d+', 'dashboard_stat_id' => '\\d+'),
	array()
));

$collection->add('report_chart_get', new Route(
	'/chart/{dashboard_stat_id}',
	array('_controller' => 'ReportBundle:Chart:getChart'),
	array('dashboard_stat_id' => '\\d+'),
	array()
));

$collection->add('report_chart_get_settings', new Route(
	'/chart/{dashboard_stat_id}/settings.{_format}',
	array('_controller' => 'ReportBundle:Chart:getChartSettings'),
	array('dashboard_stat_id' => '\\d+', '_format' => 'xml'),
	array()
));

$collection->add('report_chart_get_fullscreen_details', new Route(
	'/chart/{dashboard_stat_id}/full-screen',
	array('_controller' => 'ReportBundle:Chart:getChartFullscreenDetails'),
	array('dashboard_stat_id' => '\\d+'),
	array()
));

################################################################################
# Agent Hours
################################################################################

$collection->add('report_agent_hours_index', new Route(
    '/agent-hours',
    array('_controller' => 'ReportBundle:AgentHours:index'),
    array(),
    array()
));

$collection->add('report_agent_hours_list_date', new Route(
    '/agent-hours/{date}',
    array('_controller' => 'ReportBundle:AgentHours:list'),
    array(),
    array()
));

################################################################################
# Agent Activity
################################################################################

$collection->add('report_agent_activity_index', new Route(
    '/agent-activity',
    array('_controller' => 'ReportBundle:AgentActivity:index'),
    array(),
    array()
));

$collection->add('report_agent_activity_list', new Route(
    '/agent-activity/list/{agent_id}/{date}',
    array('_controller' => 'ReportBundle:AgentActivity:list'),
    array(),
    array()
));

################################################################################
# Agent Feedback
################################################################################

$collection->add('report_agent_feedback_feed', new Route(
	'/agent-feedback/{page}',
	array('_controller' => 'ReportBundle:AgentFeedback:feed', 'page' => '0'),
	array(),
	array()
));

$collection->add('report_agent_feedback_summary', new Route(
    '/agent-feedback/summary/{date}',
    array('_controller' => 'ReportBundle:AgentFeedback:summary'),
    array(),
    array()
));

################################################################################
# Publish
################################################################################

$collection->add('report_publish', new Route(
	'/publish',
	array('_controller' => 'ReportBundle:ReportBuilder:index'),
	array(),
	array()
));

################################################################################
# Report Builder
################################################################################

$collection->add('report_builder', new Route(
	'/report-builder',
	array('_controller' => 'ReportBundle:ReportBuilder:index'),
	array(),
	array()
));

$collection->add('report_builder_parse', new Route(
	'/report-builder/parse',
	array('_controller' => 'ReportBundle:ReportBuilder:parse'),
	array(),
	array()
));

$collection->add('report_builder_new', new Route(
	'/report-builder/new',
	array('_controller' => 'ReportBundle:ReportBuilder:edit', 'report_builder_id' => 0),
	array(),
	array()
));

$collection->add('report_builder_report', new Route(
	'/report-builder/{report_builder_id}/',
	array('_controller' => 'ReportBundle:ReportBuilder:report'),
	array('report_builder_id' => '\\d+'),
	array()
));

$collection->add('report_builder_edit', new Route(
	'/report-builder/{report_builder_id}/edit',
	array('_controller' => 'ReportBundle:ReportBuilder:edit'),
	array('report_builder_id' => '\\d+'),
	array()
));

$collection->add('report_builder_delete', new Route(
	'/report-builder/{report_builder_id}/delete',
	array('_controller' => 'ReportBundle:ReportBuilder:delete'),
	array('report_builder_id' => '\\d+'),
	array()
));

$collection->add('report_builder_favorite', new Route(
	'/report-builder/{report_builder_id}/favorite',
	array('_controller' => 'ReportBundle:ReportBuilder:favorite'),
	array('report_builder_id' => '\\d+'),
	array()
));


return $collection;