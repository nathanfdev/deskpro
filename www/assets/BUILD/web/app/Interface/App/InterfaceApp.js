define([
  'angular',

  // Helpers
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',

  'Reports/App/ReportsRouting',
  'DeskPRO/App/SetupServices',
  'Interface/App/SetupControllers',
  'Interface/App/SetupServices',
  'Interface/App/SetupDirectives',

  // angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angularUiSortable',
  'angularUiAce',
  'angular-moment',
  'angularOcLazyLoad',
  'aceEditor',

  'ngFileUpload',
  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',

  // global deps
  'angularGridster',

  'jquery',
  'moment',
  'momentTimezone',
  'DeskPRO/Directive/DpDateTimePicker',
  'customEventPolyfill',
  'toastr'
], function(
  angular,

  StateCollection,
  StateConfig,
  ReportsRouting,

  SetupDeskPROService,
  SetupControllers,
  SetupServices,
  SetupDirectives
) {
  const InterfaceApp = angular.module('DeskPRO.InterfaceApp', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.ace',
    'ui.select2',
    'ui.sortable',
    'angularMoment',
    'oc.lazyLoad',
    'gridster',
    'dp.datetimepicker'
  ]);

  SetupDeskPROService(InterfaceApp);
  SetupServices(InterfaceApp);
  SetupControllers(InterfaceApp);
  SetupDirectives(InterfaceApp);

  let isDone = false;
  InterfaceApp.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
    if (isDone) { return; }
    isDone = true;

    $urlRouterProvider.otherwise("/");

    const reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.InterfaceApp'));
    ReportsRouting(reportStates);
    for (let w of Array.from(reportStates.whens)) {
      $urlRouterProvider.when(w[0], w[1]);
    }
    return Array.from(reportStates.routes).map((r) =>
      r.applyToStateProvider($stateProvider));
  }
  ]);

  // IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
  document.domain = document.domain;

  InterfaceApp.run(['uiSelect2Config', uiSelect2Config => uiSelect2Config.dropdownAutoWidth = true
  ]);

  if (window.parent) {
    const event = new CustomEvent('dpIframeLoaded', { 'detail': { id: 'reports-interface' } });
    if (window && window.parent && window.parent.document && window.parent.document.dispatchEvent) {
      window.parent.document.dispatchEvent(event);
    }
  }

  try {
    if (__guard__(window.parent != null ? window.parent.DP_FRAME_OVERLAYS : undefined, x => x['reports-interface'])) {
      InterfaceApp.run(['$rootScope', $rootScope =>
        $rootScope.$on('$locationChangeSuccess', function() {
          if (window.parent.DP_FRAME_OVERLAYS['reports-interface'].opened) {
            return window.parent.DP_FRAME_OVERLAYS['reports-interface'].setHash(window.location.hash);
          }
        })

      ]);
    }
  } catch (e) {
    console.log(e);
  }

  return InterfaceApp;
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
