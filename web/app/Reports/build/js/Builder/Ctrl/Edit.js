(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(ReportsBaseCtrl) {
    var Reports_Builder_Ctrl_Edit, _ref;
    Reports_Builder_Ctrl_Edit = (function(_super) {
      __extends(Reports_Builder_Ctrl_Edit, _super);

      function Reports_Builder_Ctrl_Edit() {
        _ref = Reports_Builder_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_Builder_Ctrl_Edit.CTRL_ID = 'Reports_Builder_Ctrl_Edit';

      Reports_Builder_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Reports_Builder_Ctrl_Edit.DEPS = ['$stateParams', '$sce', 'Api', '$window'];

      Reports_Builder_Ctrl_Edit.prototype.init = function() {
        if (this.$stateParams.type === 'builtIn') {
          this.reportData = this.DataService.get('ReportBuilderBuiltIn');
          this.reportType = 'builtIn';
        }
        if (this.$stateParams.type === 'custom') {
          this.reportData = this.DataService.get('ReportBuilderCustom');
          this.reportType = 'custom';
        }
        this.report = null;
        this.query_parts = null;
        this.rendered_result = null;
        this.query_error = null;
        return this.show_query_editor = false;
      };

      /*
       	#
      */


      Reports_Builder_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.reportData.loadEditReportData(this.$stateParams.id || null, this.$stateParams.params || null).then(function(data) {
          _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result);
          _this.group_params = _this.$scope.$parent.ListCtrl.group_params;
          _this.query_parts = data.query_parts;
          _this.report = data.report;
          return _this.form = data.form;
        });
        return promise;
      };

      /*
       	# Shows / hides query editor
      */


      Reports_Builder_Ctrl_Edit.prototype.toggleQueryEditor = function() {
        return this.show_query_editor = !this.show_query_editor;
      };

      /*
       	# This method is called when user clicks button named 'Test' in query builder form
      */


      Reports_Builder_Ctrl_Edit.prototype.testReport = function() {
        var promise,
          _this = this;
        this.startSpinner('builder_loading');
        this.startSpinner('query_loading');
        promise = this.Api.sendPostJson('/reports/builder/test/' + this.report.id, {
          parts: this.query_parts
        });
        return promise.success(function(data) {
          if (data.error) {
            _this.query_error = data.error;
          }
          if (data.rendered_result) {
            _this.query_error = null;
            _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result);
          }
          _this.stopSpinner('builder_loading', true);
          return _this.stopSpinner('query_loading', true);
        });
      };

      /*
      		# This method is called when user clicks on 'Query' tab inside query builder form
      */


      Reports_Builder_Ctrl_Edit.prototype.switchToQuery = function() {
        var promise,
          _this = this;
        this.startSpinner('query_loading');
        promise = this.Api.sendPostJson('/reports/builder/parse', {
          currentType: 'builder',
          inputType: 'builder',
          newType: 'query',
          query: this.report.query,
          parts: this.query_parts
        });
        return promise.success(function(data) {
          if (data.error) {
            _this.query_error = data.error;
          }
          if (data.query) {
            _this.query_error = null;
            _this.report.query = data.query;
          }
          return _this.stopSpinner('query_loading', true);
        });
      };

      /*
      		# This method is called when user clicks on 'Builder' tab inside query builder form
      */


      Reports_Builder_Ctrl_Edit.prototype.switchToBuilder = function() {
        var promise,
          _this = this;
        this.startSpinner('builder_loading');
        promise = this.Api.sendPostJson('/reports/builder/parse', {
          currentType: 'query',
          inputType: 'query',
          newType: 'builder',
          query: this.report.query,
          parts: this.query_parts
        });
        return promise.success(function(data) {
          if (data.error) {
            _this.query_error = data.error;
          }
          if (data.parts) {
            _this.query_error = null;
            _this.query_parts = data.parts;
          }
          return _this.stopSpinner('builder_loading', true);
        });
      };

      /*
       	# This method is called when user clicks on 'CSV' button
      */


      Reports_Builder_Ctrl_Edit.prototype.downloadCsv = function() {
        return this.$window.location.href = window.DP_BASE_API_URL + '/reports/builder/download/' + this.report.id + '/csv?API-TOKEN=' + window.DP_API_TOKEN;
      };

      /*
      		# This method is called when user clicks on 'PDF' button
      */


      Reports_Builder_Ctrl_Edit.prototype.downloadPdf = function() {
        return this.$window.location.href = window.DP_BASE_API_URL + '/reports/builder/download/' + this.report.id + '/pdf?API-TOKEN=' + window.DP_API_TOKEN;
      };

      /*
      		# This method is called when user clicks on 'print' button
      */


      Reports_Builder_Ctrl_Edit.prototype.print = function() {
        return window.print();
      };

      /*
      		# Saving report
      */


      Reports_Builder_Ctrl_Edit.prototype.saveReport = function() {
        var is_new, promise,
          _this = this;
        if (!this.report.is_custom) {
          throw new Error('Only custom reports could be saved');
        }
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.report.id;
        promise = this.reportData.saveFormModel(this.report, this.form, this.query_parts);
        this.startSpinner('builder_loading');
        this.startSpinner('query_loading');
        this.startSpinner('saving');
        return promise.then(function(data) {
          if (data.error) {
            _this.query_error = data.error;
          }
          if (data.rendered_result) {
            _this.query_error = null;
            _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result);
          }
          _this.skipDirtyState();
          if (is_new) {
            _this.$state.go('builder.create');
          }
          _this.stopSpinner('builder_loading', true);
          _this.stopSpinner('query_loading', true);
          return _this.stopSpinner('saving', true).then(function() {
            return _this.Growl.success("Saved");
          });
        });
      };

      return Reports_Builder_Ctrl_Edit;

    })(ReportsBaseCtrl);
    return Reports_Builder_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/