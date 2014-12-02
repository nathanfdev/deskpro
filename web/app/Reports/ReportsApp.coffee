define [
  'angular',
  'Reports/App/Controller/Main',
], (
  angular,
  Reports_App_Controller_Main
) ->
  ReportsApp = angular.module('DeskPRO.ReportsApp', ['DeskPRO.InterfaceApp'])

  ReportsApp.controller('Reports.App.Main', Reports_App_Controller_Main)

  return ReportsApp
