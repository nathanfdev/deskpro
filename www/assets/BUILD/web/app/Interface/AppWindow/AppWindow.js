define([
  'angular',

  // Initial loader
  'Interface/',

  // angular modules
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angular-moment',

  // global deps
  'jquery',
  'moment',
  'momentTimezone',
], (
  angular
) => {
  const AppWindow = angular.module('DeskPRO.InterfaceApp.AppWindow', []);
  AppWindow.config(['$stateProvider', function ($stateProvider) {}

  ]);

  return AppWindow;
});
