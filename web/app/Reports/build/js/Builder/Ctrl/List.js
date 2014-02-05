(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
    var Reports_Builder_Ctrl_List, _ref;
    Reports_Builder_Ctrl_List = (function(_super) {
      __extends(Reports_Builder_Ctrl_List, _super);

      function Reports_Builder_Ctrl_List() {
        _ref = Reports_Builder_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_Builder_Ctrl_List.CTRL_ID = 'Reports_Builder_Ctrl_List';

      Reports_Builder_Ctrl_List.CTRL_AS = 'ListCtrl';

      Reports_Builder_Ctrl_List.DEPS = ['Api'];

      Reports_Builder_Ctrl_List.prototype.init = function() {
        this.customData = this.DataService.get('ReportBuilderCustom');
        return this.builtInData = this.DataService.get('ReportBuilderBuiltIn');
      };

      /*
      		# Loads 2 lists - first with custom reports, second with built-in reports
      */


      Reports_Builder_Ctrl_List.prototype.initialLoad = function() {
        var built_in_promise, custom_promise, group_params_promise,
          _this = this;
        custom_promise = this.customData.loadList().then(function(list) {
          return _this.custom_data_list = list;
        });
        built_in_promise = this.builtInData.loadList().then(function(list) {
          return _this.built_in_data_list = list;
        });
        group_params_promise = this.Api.sendGet('/reports/builder/group-params').then(function(data) {
          return _this.group_params = data.data;
        });
        return this.$q.all([custom_promise, built_in_promise, group_params_promise]);
      };

      return Reports_Builder_Ctrl_List;

    })(ReportsBaseCtrl);
    return Reports_Builder_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/