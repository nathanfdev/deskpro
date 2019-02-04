define([
  'angular',

  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularTree',
  'angularUiAce',
  'angularUiRouter',
  'angularUiSortable',
  'angular-moment',
  'angularFileUpload',
  'angularSlider',
  'angularSelectize',
  'angularGrid',
  'angularScrollGlue',
  'ngFileUpload',
  'angularSpectrumColorpicker',

  'moment',
  'momentTimezone',
  'aceEditor',

  'jquery',
  'jqueryUi',
  'underscore',
  'stacktrace',

  'microplugin',
  'sifter',
  'selectize',

  'bootstrapTooltip',

  'select2',
  'toastr',

  'semanticAccordion',

  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',
  'DeskPRO/Directive/DpDateTimePicker'
], (angular
) => {
  // Set path for ace editor
  if (ace) {
    ace.config.set('basePath',   `${DP_ASSET_URL}/bower_components/ace-builds/src-min-noconflict`);
    ace.config.set('modePath',   `${DP_ASSET_URL}/bower_components/ace-builds/src-min-noconflict`);
    ace.config.set('themePath',  `${DP_ASSET_URL}/bower_components/ace-builds/src-min-noconflict`);
    ace.config.set('workerPath', `${DP_ASSET_URL}/bower_components/ace-builds/src-min-noconflict`);
  }

  const AdminModule = angular.module('Admin_App', [
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ui.bootstrap',
    'ui.select2',
    'ui.tree',
    'ui.sortable',
    'ui.ace',
    'angularMoment',
    'blueimp.fileupload',
    'angularFileUpload',
    'selectize',
    'ngGrid',
    'deskpro.option_builder',
    'deskpro.category_builder',
    'dp.datetimepicker',
    'luegg.directives',
    'angularSpectrumColorpicker'
  ]);

  AdminModule.config(['datepickerConfig', 'datepickerPopupConfig', function (datepickerConfig, datepickerPopupConfig) {
    datepickerConfig.showWeeks = false;
    datepickerPopupConfig.showWeeks = false;
    return datepickerPopupConfig.dateFormat = 'dd MMMM yyyy';
  }
  ]);

  AdminModule.run(['uiSelect2Config', uiSelect2Config => uiSelect2Config.dropdownAutoWidth = true
  ]);

  return AdminModule;
});
