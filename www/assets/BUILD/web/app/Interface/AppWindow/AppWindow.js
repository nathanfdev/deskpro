// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
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
], function(
  angular
) {
  const AppWindow = angular.module('DeskPRO.InterfaceApp.AppWindow', []);
  AppWindow.config(['$stateProvider', function($stateProvider) {}

  ]);

  return AppWindow;
});