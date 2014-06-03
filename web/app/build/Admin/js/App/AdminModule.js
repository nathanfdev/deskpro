(function() {
  define(['angular'], function(angular) {
    var AdminModule;
    AdminModule = angular.module('Admin_App', ['ngAnimate', 'ngSanitize', 'ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'ui.ace', 'angularMoment', 'blueimp.fileupload', 'angularFileUpload', 'uiSlider', 'ngGrid', 'deskpro.option_builder', 'deskpro.category_builder']);
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
