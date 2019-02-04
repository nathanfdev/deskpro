define(() => States =>
//----------------------------------------
// MAIN
//----------------------------------------

  States.add('headless-dashboard')
    .setUrl('/')
    .setTpl('AgentBundle:ReportsInterface:headless-dashboard-frame.html')
    .setCtrl('Reports.App.New.HeadlessDashboard')

 );
