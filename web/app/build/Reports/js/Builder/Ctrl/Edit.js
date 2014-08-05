(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(ReportsBaseCtrl) {
    var Reports_Builder_Ctrl_Edit;
    Reports_Builder_Ctrl_Edit = (function(_super) {
      __extends(Reports_Builder_Ctrl_Edit, _super);

      function Reports_Builder_Ctrl_Edit() {
        return Reports_Builder_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Reports_Builder_Ctrl_Edit.CTRL_ID = 'Reports_Builder_Ctrl_Edit';

      Reports_Builder_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Reports_Builder_Ctrl_Edit.DEPS = ['$stateParams', '$sce', 'Api', '$window', '$http'];

      Reports_Builder_Ctrl_Edit.prototype.init = function() {
        if (this.$stateParams.type === 'builtIn') {
          this.reportData = this.DataService.get('ReportBuilderBuiltIn');
          this.customList = this.DataService.get('ReportBuilderCustom');
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
        this.show_query_editor = false;
        this.editor_mode = 'builder';
        return this.query_parts_synced = true;
      };

      Reports_Builder_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.reportData.loadEditReportData(this.$stateParams.id || null, this.$stateParams.params || null).then((function(_this) {
          return function(data) {
            _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result || '');
            _this.group_params = _this.$scope.$parent.ListCtrl.group_params;
            _this.query_parts = data.query_parts;
            _this.report = data.report;
            _this.form = data.form;
            return _this.query_parts_synced = true;
          };
        })(this));
        return promise;
      };


      /*
      		 * Shows / hides query editor
       */

      Reports_Builder_Ctrl_Edit.prototype.toggleQueryEditor = function() {
        return this.show_query_editor = !this.show_query_editor;
      };


      /*
      		 * This method is called when user clicks button named 'Test' in query builder form
       */

      Reports_Builder_Ctrl_Edit.prototype.testReport = function() {
        var run;
        this.startSpinner('builder_loading');
        this.startSpinner('query_loading');
        run = (function(_this) {
          return function() {
            var promise;
            promise = _this.Api.sendPostJson('/reports/builder/test/' + _this.report.id, {
              parts: _this.query_parts
            });
            return promise.success(function(data) {
              if (data.error) {
                _this.query_error = data.error;
              }
              if (data.rendered_result) {
                _this.query_error = null;
                _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result || '');
              }
              _this.stopSpinner('builder_loading', true);
              return _this.stopSpinner('query_loading', true);
            });
          };
        })(this);
        if (this.query_parts_synced) {
          return run();
        } else {
          return this.syncQueryParts().then(run);
        }
      };


      /*
      		 * This method is called when user clicks on 'Query' tab inside query builder form
       */

      Reports_Builder_Ctrl_Edit.prototype.switchToQuery = function() {
        var promise;
        this.startSpinner('query_loading');
        this.editor_mode = 'query';
        this.query_parts_synced = false;
        promise = this.Api.sendPostJson('/reports/builder/parse', {
          currentType: 'builder',
          inputType: 'builder',
          newType: 'query',
          query: this.report.query,
          parts: this.query_parts
        });
        return promise.success((function(_this) {
          return function(data) {
            if (data.error) {
              _this.query_error = data.error;
            }
            if (data.query) {
              _this.query_error = null;
              _this.report.query = data.query;
            }
            return _this.stopSpinner('query_loading', true);
          };
        })(this));
      };


      /*
      		 * This method is called when user clicks on 'Builder' tab inside query builder form
       */

      Reports_Builder_Ctrl_Edit.prototype.switchToBuilder = function() {
        this.startSpinner('builder_loading');
        this.editor_mode = 'builder';
        return this.syncQueryParts().then((function(_this) {
          return function() {
            return _this.stopSpinner('builder_loading', true);
          };
        })(this));
      };


      /*
        	 * Syncs the report query with the query parts form
       */

      Reports_Builder_Ctrl_Edit.prototype.syncQueryParts = function() {
        var promise;
        promise = this.Api.sendPostJson('/reports/builder/parse', {
          currentType: 'query',
          inputType: 'query',
          newType: 'builder',
          query: this.report.query,
          parts: this.query_parts
        });
        promise.success((function(_this) {
          return function(data) {
            if (data.error) {
              _this.query_error = data.error;
            }
            if (data.parts) {
              _this.query_error = null;
              _this.query_parts = data.parts;
            }
            return _this.query_parts_synced = true;
          };
        })(this));
        return promise;
      };


      /*
      		 * This method is called when user clicks on 'CSV' button
       */

      Reports_Builder_Ctrl_Edit.prototype.downloadCsv = function() {
        return window.open(this.$http.formatApiUrl('/reports/builder/download/' + this.report.id + '/csv', {
          params: this.$stateParams.params
        }));
      };


      /*
      		 * This method is called when user clicks on 'PDF' button
       */

      Reports_Builder_Ctrl_Edit.prototype.downloadPdf = function() {
        return window.open(this.$http.formatApiUrl('/reports/builder/download/' + this.report.id + '/pdf', {
          params: this.$stateParams.params
        }));
      };


      /*
      		 * This method is called when user clicks on 'print' button
       */

      Reports_Builder_Ctrl_Edit.prototype.print = function() {
        return window.print();
      };


      /*
      		 * Saving report
       */

      Reports_Builder_Ctrl_Edit.prototype.saveReport = function() {
        var is_new, run;
        if (!this.report.is_custom) {
          throw new Error('Only custom reports could be saved');
        }
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.report.id;
        run = (function(_this) {
          return function() {
            var promise;
            promise = _this.reportData.saveFormModel(_this.report, _this.form, _this.query_parts);
            _this.startSpinner('builder_loading');
            _this.startSpinner('query_loading');
            _this.startSpinner('saving');
            return promise.then(function(res) {
              var data;
              data = res.data;
              if (data.error) {
                _this.stopSpinner('builder_loading', true);
                _this.stopSpinner('query_loading', true);
                _this.stopSpinner('saving', true);
                _this.query_error = data.error;
                if (!_this.show_query_editor) {
                  _this.show_query_editor = true;
                }
                return;
              }
              if (data.rendered_result) {
                _this.query_error = null;
                _this.rendered_result = _this.$sce.trustAsHtml(data.rendered_result || '');
              }
              _this.skipDirtyState();
              if (is_new) {
                _this.$state.go('builder');
              }
              _this.stopSpinner('builder_loading', true);
              _this.stopSpinner('query_loading', true);
              return _this.stopSpinner('saving', true).then(function() {
                return _this.Growl.success("Saved");
              });
            });
          };
        })(this);
        if (this.query_parts_synced) {
          return run();
        } else {
          return this.syncQueryParts().then(run);
        }
      };


      /*
      		 * Cloning the report
       */

      Reports_Builder_Ctrl_Edit.prototype.saveToClone = function() {
        var run;
        this.startSpinner('builder_loading');
        this.startSpinner('query_loading');
        this.startSpinner('saving');
        run = (function(_this) {
          return function() {
            var promise;
            promise = _this.Api.sendPostJson('/reports/builder/clone/' + _this.report.id, {
              parts: _this.query_parts,
              title: _this.form.title,
              description: _this.form.description
            });
            return promise.success(function(data) {
              var proms;
              proms = [_this.reportData.loadList(true)];
              if (_this.customList) {
                proms.push(_this.customList.loadList(true));
              }
              return _this.$q.all(proms).then(function() {
                _this.stopSpinner('builder_loading', true);
                _this.stopSpinner('query_loading', true);
                return _this.stopSpinner('saving', true).then(function() {
                  _this.Growl.success("Cloning Done");
                  return _this.$state.go('builder.edit', {
                    type: 'custom',
                    id: data.id,
                    params: ''
                  });
                });
              });
            });
          };
        })(this);
        if (this.query_parts_synced) {
          return run();
        } else {
          return this.syncQueryParts().then(run);
        }
      };

      return Reports_Builder_Ctrl_Edit;

    })(ReportsBaseCtrl);
    return Reports_Builder_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
