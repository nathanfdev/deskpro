define(() => States =>
//----------------------------------------
// MAIN
//----------------------------------------

  States.add('headless-reports')
    .setUrl('/')
    .setTpl('AgentBundle:ReportsInterface:headless-frame.html')
    .setCtrl('Reports.App.New.Headless')

 );
