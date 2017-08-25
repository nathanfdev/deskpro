define [
  # Controllers
  'Reports/Dashboards/Ctrl/DashboardReport',
  'Reports/Dashboards/Ctrl/DashboardView',

  # Modal controllers
  'Reports/Dashboards/ModalCtrl/EditDashboard',
  'Reports/Dashboards/ModalCtrl/ChooseWidget',
  'Reports/Dashboards/ModalCtrl/AddWidget',
  'Reports/Dashboards/ModalCtrl/EditWidget',

  # Stats controllers
  'Reports/Stats/Ctrl/StatsMain',
  'Reports/Stats/Ctrl/StatsHome',
  'Reports/Stats/Ctrl/WidgetView',
], (
  # Controllers
  Reports_Dashboards_Ctrl_DashboardReport,
  Reports_Dashboards_Ctrl_DashboardView,

  # Modal controllers
  Reports_Dashboards_ModalCtrl_EditDashboard,
  Reports_Dashboards_ModalCtrl_ChooseWidget,
  Reports_Dashboards_ModalCtrl_AddWidget,
  Reports_Dashboards_ModalCtrl_EditWidget,

  # Stats controller
  Reports_Stats_Ctrl_StatsMain,
  Reports_Stats_Ctrl_StatsHome,
  Reports_Stats_Ctrl_WidgetView,
) ->
  return (Module) ->
    Module.controller('Reports.App.DashboardReport',             Reports_Dashboards_Ctrl_DashboardReport)
    Module.controller('Reports.App.DashboardView',               Reports_Dashboards_Ctrl_DashboardView)
    Module.controller('Reports.Dashboards.Modals.EditDashboard', Reports_Dashboards_ModalCtrl_EditDashboard)
    Module.controller('Reports.Dashboards.Modals.ChooseWidget',  Reports_Dashboards_ModalCtrl_ChooseWidget)
    Module.controller('Reports.Dashboards.Modals.AddWidget',     Reports_Dashboards_ModalCtrl_AddWidget)
    Module.controller('Reports.Dashboards.Modals.EditWidget',    Reports_Dashboards_ModalCtrl_EditWidget)

    Module.controller('Reports.TicketSatisfaction', () -> )
    Module.controller('Reports.AgentActivity', () -> )
    Module.controller('Reports.AgentHours', () -> )

    Module.controller('Reports.Stats.StatsMain',                 Reports_Stats_Ctrl_StatsMain)
    Module.controller('Reports.Stats.StatsHome',                 Reports_Stats_Ctrl_StatsHome)
    Module.controller('Reports.Stats.WidgetView',                Reports_Stats_Ctrl_WidgetView)
