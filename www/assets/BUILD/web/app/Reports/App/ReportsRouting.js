define(() => function (States) {
  //----------------------------------------
  // MAIN
  //----------------------------------------

  States.add('reports')
    .setUrl('')
    .setTpl('AgentBundle:ReportsInterface:main-frame.html')
    .setCtrl('Reports.App.New.Nav');

  States.when('', '/dashboards/');
  States.when('/', '/dashboards/');
  States.when('/dashboards', '/dashboards/');
  States.when(/^\/dashboards\/[0-9]+\/?$/, ['$stateParams', $stateParams => `/dashboards/${$stateParams.dashboard_id}/`]);

  //----------------------------------------
  // DASHBOARDS
  //----------------------------------------

  States.add('reports.dashboards')
    .setUrl('/dashboards')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboards.html')
    .setAbstract();

  States.add('reports.dashboards.index')
    .setUrl('/')
    .setCtrl(['$state', 'DashboardsInfo', ($state, DashboardsInfo) =>
      DashboardsInfo.getDashboardList().then((dbs) => {
        const db = dbs[0];

        if ((db.reports != null ? db.reports.length : undefined) > 0) {
          return $state.go('reports.dashboards.view.report', { dashboard_id: db.id, report_id: db.reports[0].id });
        }
        return $state.go('reports.dashboards.view.index', { dashboard_id: db.id });
      })

    ]);

  States.add('reports.dashboards.view')
    .setUrl('/{dashboard_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardView')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-view.html')
    .setAbstract();

  States.add('reports.dashboards.view.index')
    .setUrl('/')
    .setCtrl(['$state', '$stateParams', 'DashboardsInfo', ($state, $stateParams, DashboardsInfo) =>
      DashboardsInfo.getReportsList($stateParams.dashboard_id).then((reports) => {
        if (!reports.length) {
          return $state.go('reports.dashboards.view.empty');
        }
        return $state.go('reports.dashboards.view.report', { dashboard_id: $stateParams.dashboard_id, report_id: reports[0].id });
      }
      , () => $state.go('reports'))

    ]);

  States.add('reports.dashboards.view.empty')
    .setUrl('/empty-dashboard');

  States.add('reports.dashboards.view.report')
    .setUrl('/{report_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardReport')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-report.html');

  //----------------------------------------
  // STATS
  //----------------------------------------

  States.when('/stats/', '/stats');

  States.add('reports.stats')
    .setUrl('/stats')
    .setCtrl('Reports.Stats.StatsMain')
    .setTpl('ReportsInterfaceBundle:Stats:main.html');

  States.add('reports.stats.edit')
    .setUrl('/edit/{reportId:[0-9]+}')
    .setCtrl('Reports.Stats.StatsMain')
    .setTpl('ReportsInterfaceBundle:Stats:main.html');

  States.add('reports.stats.new')
    .setUrl('/new')
    .setCtrl('Reports.Stats.StatsMain')
    .setTpl('ReportsInterfaceBundle:Stats:main.html');

  //----------------------------------------
  // Agent Activity
  //----------------------------------------

  States.add('reports.agent_activity')
    .setUrl('/agent_activity')
    .setCtrl('Reports.AgentActivity')
    .setTpl('ReportsInterfaceBundle:AgentActivity:widget.html');

  //----------------------------------------
  // Agent Hours
  //----------------------------------------

  States.add('reports.agent_hours')
    .setUrl('/agent_hours')
    .setCtrl('Reports.AgentHours')
    .setTpl('ReportsInterfaceBundle:AgentHours:widget.html');

  //----------------------------------------
  // Ticket Satisfaction
  //----------------------------------------

  States.add('reports.ticket_satisfaction')
    .setUrl('/ticket_satisfaction')
    .setCtrl('Reports.TicketSatisfaction')
    .setTpl('ReportsInterfaceBundle:TicketSatisfaction:widget.html');


  // open agent links in parent window when clicking in iframe
  $(document).on('click', (e) => {
    const href = $(e.target).attr('href');
    if (!href || (href.indexOf('/agent/#') !== 0)) { return; }
    e.preventDefault();
    const event = new CustomEvent('dpHashChange', { detail: { hash: href.replace(/.+(#.+)/, '$1') } });
    return window.parent.document.dispatchEvent(event);
  });

  //----------------------------------------
  // View mode
  //----------------------------------------

  return States.add('reports.dashboard_view.view')
    .setUrl('/{authcode:\w+}')
    .setCtrl('Reports.App.DashboardView')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-view.html')
    .setAbstract();
}
 );
