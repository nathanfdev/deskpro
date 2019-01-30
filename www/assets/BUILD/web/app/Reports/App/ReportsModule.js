/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'angular',
  'DeskPRO/Directive/DpDateTimePicker'
], function(
  angular
) {
  const ReportsModule = angular.module('Reports_App', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.select2',
    'ui.sortable',
    'angularMoment',
    'blueimp.fileupload',
    'deskpro.option_builder',
    'deskpro.category_builder',
    'dp.datetimepicker'
  ]);

  ReportsModule.config(['datepickerConfig', 'datepickerPopupConfig', function(datepickerConfig, datepickerPopupConfig) {
    datepickerConfig.showWeeks = false;
    datepickerPopupConfig.showWeeks = false;
    return datepickerPopupConfig.dateFormat = 'dd MMMM yyyy';
  }
  ]);

  return ReportsModule;
});