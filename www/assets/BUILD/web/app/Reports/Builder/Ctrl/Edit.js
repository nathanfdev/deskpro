define([
  'Reports/Main/Ctrl/Base'
], (
  ReportsBaseCtrl
) => {
  class Reports_Builder_Ctrl_Edit extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_Builder_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams', '$sce', 'Api', '$window', '$http'];
    }

    init() {
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
    }

    initialLoad() {
      const promise = this.reportData.loadEditReportData(this.$stateParams.id || null, this.$stateParams.params || null).then((data) => {
        this.rendered_result = this.$sce.trustAsHtml(data.rendered_result || '');
        this.group_params = this.$scope.$parent.ListCtrl.group_params;
        this.query_parts = data.query_parts;
        this.report  = data.report;
        this.form = data.form;
        return this.query_parts_synced = true;
      });
      return promise;
    }


    /*
     * Shows / hides query editor
     */
    toggleQueryEditor() {
      return this.show_query_editor = !this.show_query_editor;
    }


    /*
     * This method is called when user clicks button named 'Test' in query builder form
     */
    testReport() {
      this.startSpinner('builder_loading');
      this.startSpinner('query_loading');

      const run = () => {
        const promise = this.Api.sendPostJson(`/reports/builder/test/${this.report.id}`, {
          parts:  this.query_parts,
          params: this.$stateParams.params
        });

        return promise.success((data) => {
          if (data.error) {
            this.query_error = data.error;
          } else if (data.rendered_result) {
            this.query_error = null;
            this.rendered_result = this.$sce.trustAsHtml(data.rendered_result || '');
          } else {
            this.query_error = null;
            this.rendered_result = this.$sce.trustAsHtml(data.rendered_result || '');
          }

          this.stopSpinner('builder_loading', true);
          return this.stopSpinner('query_loading', true);
        });
      };

      if (this.query_parts_synced) {
        return run();
      }
      return this.syncQueryParts().then(run);
    }


    /*
     * This method is called when user clicks on 'Query' tab inside query builder form
     */
    switchToQuery() {
      this.startSpinner('query_loading');

      this.editor_mode = 'query';
      this.query_parts_synced = false;
      const promise = this.Api.sendPostJson('/reports/builder/parse', {
        currentType: 'builder',
        inputType:   'builder',
        newType:     'query',
        query:       this.report.query,
        parts:       this.query_parts
      });

      return promise.success((data) => {
        if (data.error) { this.query_error = data.error; }

        if (data.query) {
          this.query_error = null;
          this.report.query = data.query;
        }

        return this.stopSpinner('query_loading', true);
      });
    }


    /*
     * This method is called when user clicks on 'Builder' tab inside query builder form
     */
    switchToBuilder() {
      this.startSpinner('builder_loading');

      this.editor_mode = 'builder';
      return this.syncQueryParts().then(() => this.stopSpinner('builder_loading', true));
    }

    /*
      * Syncs the report query with the query parts form
      */
    syncQueryParts() {
      const promise = this.Api.sendPostJson('/reports/builder/parse', {
        currentType: 'query',
        inputType:   'query',
        newType:     'builder',
        query:       this.report.query,
        parts:       this.query_parts
      });

      promise.success((data) => {
        if (data.error) { this.query_error = data.error; }

        if (data.parts) {
          this.query_error = null;
          this.query_parts = data.parts;
        }

        return this.query_parts_synced = true;
      });

      return promise;
    }

    /*
     * This method is called when user clicks on 'CSV' button
     */
    downloadCsv() {
      return window.open(this.$http.formatApiUrl(`/reports/builder/download/${this.report.id}/csv`, { params: this.$stateParams.params }));
    }


    /*
     * This method is called when user clicks on 'PDF' button
     */
    downloadPdf() {
      return window.open(this.$http.formatApiUrl(`/reports/builder/download/${this.report.id}/pdf`, { params: this.$stateParams.params }));
    }


    /*
     * This method is called when user clicks on 'print' button
     */
    print() {
      return window.print();
    }


    /*
     * Saving report
     */
    saveReport() {
      if (!this.report.is_custom) { throw new Error('Only custom reports could be saved'); }

      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.report.id;

      const run = () => {
        const promise = this.reportData.saveFormModel(this.report, this.form, this.query_parts);

        this.startSpinner('builder_loading');
        this.startSpinner('query_loading');
        this.startSpinner('saving');

        return promise.then((res) => {
          const { data } = res;

          if (data.error) {
            this.stopSpinner('builder_loading', true);
            this.stopSpinner('query_loading', true);
            this.stopSpinner('saving', true);
            this.query_error = data.error;
            if (!this.show_query_editor) { this.show_query_editor = true; }
            return;
          }

          if (data.rendered_result) {
            this.query_error = null;
            this.rendered_result = this.$sce.trustAsHtml(data.rendered_result || '');
          }

          if (is_new) {
            this.$state.go('builder.edit', { id: data.id, type: 'custom', params: '' });
          }

          this.stopSpinner('builder_loading', true);
          this.stopSpinner('query_loading', true);
          return this.stopSpinner('saving', true).then(() => this.Growl.success('Saved'));
        });
      };

      if (this.query_parts_synced) {
        return run();
      }
      return this.syncQueryParts().then(run);
    }


    /*
     * Cloning the report
     */
    saveToClone() {
      this.startSpinner('builder_loading');
      this.startSpinner('query_loading');
      this.startSpinner('saving');

      const run = () => {
        const promise = this.Api.sendPostJson(`/reports/builder/clone/${this.report.id}`, {
          parts:       this.query_parts,
          title:       this.form.title,
          description: this.form.description
        });

        return promise.success((data) => {
          const proms = [this.reportData.loadList(true)];

          if (this.customList) {
            proms.push(this.customList.loadList(true));
          }

          return this.$q.all(proms).then(() => {
            this.stopSpinner('builder_loading', true);
            this.stopSpinner('query_loading', true);
            return this.stopSpinner('saving', true).then(() => {
              this.Growl.success('Cloning Done');
              return this.$state.go('builder.edit', { type: 'custom', id: data.id, params: '' });
            });
          });
        });
      };

      if (this.query_parts_synced) {
        return run();
      }
      return this.syncQueryParts().then(run);
    }
  }
  Reports_Builder_Ctrl_Edit.initClass();

  return Reports_Builder_Ctrl_Edit.EXPORT_CTRL();
});
