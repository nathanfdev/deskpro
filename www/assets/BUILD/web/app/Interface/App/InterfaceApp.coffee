define [
  'angular',

  # Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  'Reports/App/ReportsRouting',
  'DeskPRO/App/SetupServices',
  'Interface/App/SetupControllers',
  'Interface/App/SetupServices',
  'Interface/App/SetupDirectives',

  # angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angularUiAce',
  'angular-moment',
  'angularOcLazyLoad',
  'aceEditor',

  'ngFileUpload',
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',

  # global deps
  'angularGridster',

  'jquery',
  'moment',
  'momentTimezone',
  'DeskPRO/Directive/DpDateTimePicker'
], (
  angular,

  StateCollection,
  StateConfig,
  ReportsRouting

  SetupDeskPROService,
  SetupControllers,
  SetupServices,
  SetupDirectives,
) ->
  InterfaceApp = angular.module('DeskPRO.InterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.ace',
    'ui.select2',
    'angularMoment',
    'oc.lazyLoad',
    'gridster',
  ])

  SetupDeskPROService(InterfaceApp)
  SetupServices(InterfaceApp)
  SetupControllers(InterfaceApp)
  SetupDirectives(InterfaceApp)

  isDone = false
  InterfaceApp.config(['$stateProvider', '$urlRouterProvider', ($stateProvider, $urlRouterProvider) ->
    return if isDone
    isDone = true

    $urlRouterProvider.otherwise("/")

    reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.InterfaceApp'))
    ReportsRouting(reportStates)
    for w in reportStates.whens
      $urlRouterProvider.when(w[0], w[1])
    for r in reportStates.routes
      r.applyToStateProvider($stateProvider)
  ])

  InterfaceApp.run(['uiSelect2Config', (uiSelect2Config) ->
    uiSelect2Config.dropdownAutoWidth = true
  ])

  return InterfaceApp