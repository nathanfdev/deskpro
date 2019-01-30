/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(() => States =>
//----------------------------------------
// MAIN
//----------------------------------------

  States.add('headless-dashboard')
    .setUrl('/')
    .setTpl('AgentBundle:ReportsInterface:headless-dashboard-frame.html')
    .setCtrl('Reports.App.New.HeadlessDashboard')

 );
