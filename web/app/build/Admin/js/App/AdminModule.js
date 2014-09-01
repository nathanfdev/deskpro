(function() {
  define(['angular', 'ZeroClipboard'], function(angular, ZeroClipboard) {
    var AdminModule;
    window.ZeroClipboard = ZeroClipboard;
    AdminModule = angular.module('Admin_App', ['ngAnimate', 'ngSanitize', 'ngClipboard', 'ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'ui.ace', 'angularMoment', 'blueimp.fileupload', 'angularFileUpload', 'uiSlider', 'selectize', 'ngGrid', 'deskpro.option_builder', 'deskpro.category_builder']);
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
