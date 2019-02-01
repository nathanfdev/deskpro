define([
  // Controllers
  'Reports/Main/Ctrl/New/Nav',
  'Reports/Main/Ctrl/New/Headless',
  'Reports/Main/Ctrl/New/HeadlessDashboard',

  'Reports/Dashboards/Ctrl/DashboardReport',
  'Reports/Dashboards/Ctrl/DashboardView',

  // Modal controllers
  'Reports/Dashboards/ModalCtrl/EditDashboard',
  'Reports/Dashboards/ModalCtrl/ChooseWidget',
  'Reports/Dashboards/ModalCtrl/AddWidget',
  'Reports/Dashboards/ModalCtrl/EditWidget',
  'Reports/Dashboards/ModalCtrl/AddReport',
  'Reports/Dashboards/ModalCtrl/EditReport',

  // Stats controllers
  'Reports/Stats/Ctrl/StatsMain',
  'Reports/Stats/Ctrl/StatsHome',
  'Reports/Stats/Ctrl/WidgetView',
], (
  Reports_Main_New_Nav,
  Reports_Main_Ctrl_Headless,
  Reports_Main_Ctrl_HeadlessDashboard,

  // Controllers
  Reports_Dashboards_Ctrl_DashboardReport,
  Reports_Dashboards_Ctrl_DashboardView,

  // Modal controllers
  Reports_Dashboards_ModalCtrl_EditDashboard,
  Reports_Dashboards_ModalCtrl_ChooseWidget,
  Reports_Dashboards_ModalCtrl_AddWidget,
  Reports_Dashboards_ModalCtrl_EditWidget,
  Reports_Dashboards_ModalCtrl_AddReport,
  Reports_Dashboards_ModalCtrl_EditReport,

  // Stats controller
  Reports_Stats_Ctrl_StatsMain,
  Reports_Stats_Ctrl_StatsHome,
  Reports_Stats_Ctrl_WidgetView,
) =>
  function(Module) {
    Module.controller('Reports.App.New.Nav',               Reports_Main_New_Nav);
    Module.controller('Reports.App.New.Headless',          Reports_Main_Ctrl_Headless);
    Module.controller('Reports.App.New.HeadlessDashboard', Reports_Main_Ctrl_HeadlessDashboard);

    Module.controller('Reports.App.DashboardReport', Reports_Dashboards_Ctrl_DashboardReport);
    Module.controller('Reports.App.DashboardView',   Reports_Dashboards_Ctrl_DashboardView);

    Module.controller('Reports.Dashboards.Modals.EditDashboard', Reports_Dashboards_ModalCtrl_EditDashboard);
    Module.controller('Reports.Dashboards.Modals.ChooseWidget',  Reports_Dashboards_ModalCtrl_ChooseWidget);
    Module.controller('Reports.Dashboards.Modals.AddWidget',     Reports_Dashboards_ModalCtrl_AddWidget);
    Module.controller('Reports.Dashboards.Modals.EditWidget',    Reports_Dashboards_ModalCtrl_EditWidget);
    Module.controller('Reports.Dashboards.Modals.AddReport',     Reports_Dashboards_ModalCtrl_AddReport);
    Module.controller('Reports.Dashboards.Modals.EditReport',    Reports_Dashboards_ModalCtrl_EditReport);

    Module.controller('Reports.TicketSatisfaction', function() {} );
    Module.controller('Reports.AgentActivity',      function() {} );
    Module.controller('Reports.AgentHours',         function() {} );

    Module.controller('Reports.Stats.StatsMain',  Reports_Stats_Ctrl_StatsMain);
    Module.controller('Reports.Stats.StatsHome',  Reports_Stats_Ctrl_StatsHome);
    return Module.controller('Reports.Stats.WidgetView', Reports_Stats_Ctrl_WidgetView);
  }
);
