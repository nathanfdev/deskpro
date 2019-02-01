define([
  // DASHBOARDS SPECIFIC DIRECTIVES
  'Reports/Dashboards/Directive/DashboardAmcharts',
  'Reports/Dashboards/Directive/DashboardStat',
  'Reports/Dashboards/Directive/DashboardTable',
  'Reports/Dashboards/Directive/DashboardWidget',
  'Reports/Dashboards/Directive/DpReportVariables',

  'Reports/Directive/ReportsOverview',
  'Reports/Directive/AgentPerformance',
  'Reports/Directive/TicketSatisfaction',

  // LEGACY DIRECTIVES
  'Reports/Directive/DpReportBuilderSelectBox',
  'Reports/Directive/DpReportBillingSelectBox',
  'Reports/Directive/DpReportBuilderTitle',

  // DP DIRECTIVES
  'DeskPRO/Directive/DpDropdown',
  'DeskPRO/Directive/DpShowSpinning',
  'DeskPRO/Directive/DpHideSpinning',
  'DeskPRO/Directive/DpTabBody',
  'DeskPRO/Directive/DpTabBtn',
  'DeskPRO/Directive/DpClipboard'
], (

  // DASHBOARDS SPECIFIC DIRECTIVES
  Reports_Dashboards_Directive_DashboardAmcharts,
  Reports_Dashboards_Directive_DashboardStat,
  Reports_Dashboards_Directive_DashboardTable,
  Reports_Dashboards_Directive_DashboardWidget,
  Reports_Dashboards_Directive_DpReportVariables,

  Reports_Directive_ReportsOverview,
  Reports_Directive_AgentPerformance,
  Reports_Directive_TicketSatisfaction,

  Reports_Directive_DpReportBuilderSelectBox,
  Reports_Directive_DpReportBillingSelectBox,
  Reports_Directive_DpReportBuilderTitle,

  // DP DIRECTIVES
  Reports_App_Directive_DpDropdown,
  Reports_App_Directive_DpShowSpinning,
  Reports_App_Directive_DpHideSpinning,
  Reports_App_Directive_DpTabBody,
  Reports_App_Directive_DpTabBtn,
  DeskPRO_Directive_DpClipboard
) =>
  function(Module) {
    /*
     * Dashboards specific directives
     */
    Module.directive('dashboardAmcharts', Reports_Dashboards_Directive_DashboardAmcharts);
    Module.directive('dashboardTable', Reports_Dashboards_Directive_DashboardTable);
    Module.directive('dashboardStat', Reports_Dashboards_Directive_DashboardStat);
    Module.directive('dashboardWidget', Reports_Dashboards_Directive_DashboardWidget);
    Module.directive('dpReportVariables', Reports_Dashboards_Directive_DpReportVariables);

    Module.directive('reportsOverview', Reports_Directive_ReportsOverview);
    Module.directive('agentPerformance', Reports_Directive_AgentPerformance);
    Module.directive('ticketSatisfaction', Reports_Directive_TicketSatisfaction);

    /*
     * Legacy directives
     */
    Module.directive('dpReportBuilderSelectBox', Reports_Directive_DpReportBuilderSelectBox);
    Module.directive('dpReportBillingSelectBox', Reports_Directive_DpReportBillingSelectBox);
    Module.directive('dpReportBuilderTitle', Reports_Directive_DpReportBuilderTitle);

    /*
     * DeskPRO directives
     */
    Module.directive('dpDropdown', Reports_App_Directive_DpDropdown);
    Module.directive('dpShowSpinning', Reports_App_Directive_DpShowSpinning);
    Module.directive('dpHideSpinning', Reports_App_Directive_DpHideSpinning);
    Module.directive('dpTabBody', Reports_App_Directive_DpTabBody);
    Module.directive('dpTabBtn', Reports_App_Directive_DpTabBtn);
    return Module.directive('dpClipboard', DeskPRO_Directive_DpClipboard);
  }
);
