(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
    var Reports_Builder_Ctrl_List;
    Reports_Builder_Ctrl_List = (function(_super) {
      __extends(Reports_Builder_Ctrl_List, _super);

      function Reports_Builder_Ctrl_List() {
        return Reports_Builder_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Reports_Builder_Ctrl_List.CTRL_ID = 'Reports_Builder_Ctrl_List';

      Reports_Builder_Ctrl_List.CTRL_AS = 'ListCtrl';

      Reports_Builder_Ctrl_List.DEPS = ['Api'];

      Reports_Builder_Ctrl_List.prototype.init = function() {
        this.customData = this.DataService.get('ReportBuilderCustom');
        return this.builtInData = this.DataService.get('ReportBuilderBuiltIn');
      };


      /*
      		 * Loads 2 lists - first with custom reports, second with built-in reports
       */

      Reports_Builder_Ctrl_List.prototype.initialLoad = function() {
        var built_in_promise, custom_promise, group_params_promise;
        custom_promise = this.customData.loadList().then((function(_this) {
          return function(list) {
            return _this.custom_data_list = list;
          };
        })(this));
        built_in_promise = this.builtInData.loadList().then((function(_this) {
          return function(list) {
            return _this.built_in_data_list = list;
          };
        })(this));
        group_params_promise = this.Api.sendGet('/reports/builder/group-params').then((function(_this) {
          return function(data) {
            return _this.group_params = data.data;
          };
        })(this));
        return this.$q.all([custom_promise, built_in_promise, group_params_promise]);
      };


      /*
      		 * Show the delete dlg
       */

      Reports_Builder_Ctrl_List.prototype.startDelete = function(for_report_id) {
        var inst, report;
        report = this.customData.findListModelById(for_report_id);
        if (!report.is_custom) {
          throw new Error('Report you are going to delete should be custom report');
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Builder/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.confirm = function() {
                return $modalInstance.close();
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {
            return _this.deleteReport(report);
          };
        })(this));
      };


      /*
      		 * Actually do the delete
       */

      Reports_Builder_Ctrl_List.prototype.deleteReport = function(for_report) {
        return this.customData.deleteReportById(for_report.id).success((function(_this) {
          return function() {
            if (_this.$state.current.name === 'builder.edit' && parseInt(_this.$state.params.id) === for_report.id) {
              return _this.$state.go('builder');
            }
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Reports_Builder_Ctrl_List;

    })(ReportsBaseCtrl);
    return Reports_Builder_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
