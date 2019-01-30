// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
  class Admin_ImportCsv_Ctrl_ImportCsv extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID = 'Admin_ImportCsv_Ctrl_ImportCsv';
      this.CTRL_AS = 'Ctrl';
      this.DEPS = ['Api', 'Growl', '$http', '$interval'];
    }



    init() {
      this.$scope.fileUploadOptions = {url: this.$http.formatApiUrl('/import_csv_upload'), disabled: true};
      this.$scope.fileUploadResults = null;
      this.$scope.fileSelected = false;
      this.$scope.processStarted = false;
      this.$scope.importErrors = {};
      this.$scope.importStarted = false;
      this.$scope.logs = [];

      this.$scope.delimeter = 'comma';
      this.$scope.enclosure = 'none';
      this.options = {};

      this.$scope.importSettings = {fieldMappings: [], additionalMappings: [], skipFirst: 1, updateIfExists: 1, welcomeEmail: false, showExtraMappings: {}};
      this.showExtraMappingsCases = [
        'organization', 'phone', 'website', 'im', 'twitter', 'linkedin', 'facebook', 'address1', 'address2', 'city',
        'state', 'zip', 'country', 'new_custom', 'language'
      ];

      for (let key of Array.from(this.showExtraMappingsCases)) {
        this.$scope.importSettings.showExtraMappings[key] = [];
      }

      this.$scope.$on('dp-status-update', (e, data) => {
        if (data.status === 'disabled_on_demo') {
          this.$scope.disabledOnDemo = true;
        } else {
          this.$scope.fileUploadOptions = {url: this.$http.formatApiUrl('/import_csv_upload'), disabled: false};
        }
        this.$scope.log = data.log;
        return this.updateLogs();
      });

      this.$scope.$on('$destroy', () => this.interval && this.$interval.cancel(this.interval));

      return this.setupUploadListeners();
    }



    initialLoad() {
      this.interval = this.$interval((() => this.updateLogs()), 5000);
      return this.updateLogs();
    }



    setupUploadListeners() {
      this.$scope.$on('fileuploaddone', (e, data) => {
        this.$scope.fileUploadResults = data.result;
        this.$scope.fileSelected = false;
        if (this.$scope.fileUploadResults.error) { this.$scope.fileUploadResults.upload_failed = true; }

        if (!this.$scope.fileUploadResults.upload_failed) {
          this.$scope.processStarted = true;
          return this.$scope.$apply(() => {
            return Array.from(this.$scope.fileUploadResults.columns).map((key, idx) =>
              (this.$scope.importSettings.additionalMappings[idx] = {
                title: 'Custom Field',
                handler_class: 'text'
              }));
        });
        }
      });

      this.$scope.$on('fileuploadfail', (e, data) => {
        this.$scope.fileUploadResults = {};
        this.$scope.fileUploadResults.upload_failed = true;
        return this.$scope.fileSelected = false;
      });

      return this.$scope.$on('fileuploadchange', (e, data) => {
        return this.$scope.fileSelected = true;
      });
    }



    startImport() {
      const field_maps = [];

      // construct field mappings

      for (let key = 0; key < this.$scope.importSettings.fieldMappings.length; key++) {

        const value = this.$scope.importSettings.fieldMappings[key];
        const obj = {map: value};
        for (let key2 of Object.keys(this.$scope.importSettings.additionalMappings[key] || {})) {
          const value2 = this.$scope.importSettings.additionalMappings[key][key2];
          obj[key2] = value2;
        }

        field_maps.push(obj);
      }

      // construct other needed variables

      const { user_filename } = this.$scope.fileUploadResults;
      const skip_first = this.$scope.importSettings.skipFirst;
      const welcome_email = this.$scope.importSettings.welcomeEmail;
      const { filename } = this.$scope.fileUploadResults;
      const { options } = this.$scope.fileUploadResults;

      // sending the request and doing other actions like showing / hiding indicators etc.

      this.startSpinner('saving');

      return this.Api.sendPostJson('import_csv_import', {

        field_maps,
        user_filename,
        skip_first,
        update_if_exists: this.$scope.importSettings.updateIfExists,
        welcome_email: welcome_email ? 1 : 0,
        filename,
        options

      }).then(result => {
        return this.stopSpinner('saving', true).then(() => {
          if (result.data.error) {
            if (result.data.error === 'no_email') { this.$scope.importErrors.no_email = true; }
            if (result.data.error === 'no_move') { this.$scope.importErrors.no_move = true; }
          }

          if (result.data.success) {
            this.$scope.importStarted = true;
            this.$scope.importErrors = {};
            return this.Growl.success("Importing started");
          }
        });
      });
    }



    /*
     * Handler for selection of field mapping
     * Shows / hides appropriate extra mapping for mappings table, could add extra functionality here later
     *
     * @param {Integer} column_id - id of column from the table with mapping
     * @param {String} selected_field - name of field sent by 'ng-change'
     */
    selectMapping(column_id, selected_field) {
      for (let key of Object.keys(this.$scope.importSettings.showExtraMappings || {})) {
        this.$scope.importSettings.showExtraMappings[key][column_id] = false;
      }

      if (this.showExtraMappingsCases.indexOf(selected_field) > -1) {
        return this.$scope.importSettings.showExtraMappings[selected_field][column_id] = true;
      }
    }



    updateLogs() {
      return this.Api.sendGet('import_csv_logs').then(res => {
        this.$scope.logs.length = 0;
        if (!(res.data != null ? res.data.length : undefined)) { return; }
        return res.data.map(item => {
          item.date = new Date(item.data.started * 1000);
          item.time = item.data.finished ? moment(item.data.finished * 1000).from(item.data.started * 1000, true) : '-';
          return this.$scope.logs.push(item);
        });
      });
    }



    startDeleteUsers(name) {
      const deleteUsers = () => {
        return this.Api.sendDelete('import_csv_clean', {ref: name.replace('csv_import.', '')});
      };

      const message = this.getRegisteredMessage('delete_users_prompt');
      const update = () => this.updateLogs();

      return this.$modal.open({
        templateUrl: this.getTemplatePath('Index/modal-confirm.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.message = message;

          return $scope.confirm = options =>
            deleteUsers().then(function() {
              $modalInstance.dismiss();
              return update();
            })
          ;
        }
        ]
      });
    }
  }
  Admin_ImportCsv_Ctrl_ImportCsv.initClass();



  return Admin_ImportCsv_Ctrl_ImportCsv.EXPORT_CTRL();
});
