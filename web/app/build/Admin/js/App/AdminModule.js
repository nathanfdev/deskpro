(function() {
  define(['angular', 'ZeroClipboard', 'angularAnimate', 'angularSanitize', 'angularBootstrap', 'angularSelect2', 'angularUiAce', 'angularUiRouter', 'angularUiSortable', 'angular-moment', 'angularFileUpload', 'angularSlider', 'angularSelectize', 'angularGrid', 'ngFileUpload', 'angularUiDatetime', 'moment', 'momentTimezone', 'aceEditor', 'jquery', 'jqueryUi', 'underscore', 'stacktrace', 'microplugin', 'sifter', 'selectize', 'ZeroClipboard', 'ngClip', 'bootstrapTooltip', 'select2', 'toastr', 'DeskPRO/OptionBuilder/Module', 'DeskPRO/CategoryBuilder/Module'], function(angular, ZeroClipboard) {
    var AdminModule;
    window.ZeroClipboard = ZeroClipboard;
    if (ace) {
      ace.config.set("basePath", DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
      ace.config.set("modePath", DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
      ace.config.set("themePath", DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
      ace.config.set("workerPath", DP_ASSET_URL + "/app/bower_components/ace-builds/src-min-noconflict");
    }
    AdminModule = angular.module('Admin_App', ['ngAnimate', 'ngSanitize', 'ngClipboard', 'ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'ui.ace', 'angularMoment', 'blueimp.fileupload', 'angularFileUpload', 'selectize', 'ngGrid', 'deskpro.option_builder', 'deskpro.category_builder', 'ui.datetime']);
    AdminModule.config([
      'datepickerConfig', 'datepickerPopupConfig', function(datepickerConfig, datepickerPopupConfig) {
        datepickerConfig.showWeeks = false;
        datepickerPopupConfig.showWeeks = false;
        return datepickerPopupConfig.dateFormat = 'dd MMMM yyyy';
      }
    ]);
    AdminModule.run([
      'uiSelect2Config', function(uiSelect2Config) {
        return uiSelect2Config.dropdownAutoWidth = true;
      }
    ]);
    return AdminModule;
  });

}).call(this);

//# sourceMappingURL=AdminModule.js.map
