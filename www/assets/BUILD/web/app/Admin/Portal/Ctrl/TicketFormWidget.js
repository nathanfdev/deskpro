// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions', 'jquery'], function(Admin_Ctrl_Base) {
  class Admin_Portal_Ctrl_TicketFormWidget extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Portal_Ctrl_TicketFormWidget';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = ['$http'];
    }

    init() {
      this.$scope.code = '';
      this.$scope.language = '';
      this.$scope.department = '';
      this.$scope.width = 500;
      this.$scope.departments = [];
      return this.$scope.languages = [];
    }

    initialLoad() {
      this.Api2.sendGet('/ticket_departments?selectable=1').then(({data}) => {
        return this.$scope.departments = data.data || [];
    });
      return this.Api2.sendGet('/languages').then(({data}) => {
        this.$scope.languages = data.data;
        return this.$scope.language = data.data[0].locale;
      });
    }

    getCode() {
      const params = {language: this.$scope.language, department: this.$scope.department, width: this.$scope.width};
      if (params.department) {
        params.hide_department = 1;
      }
      const httpParams = {
        transformResponse: undefined
      };
      return this.Api2.sendGet('/ticket-form-widget/code', params, httpParams).then(response => {
        return this.$scope.code = response.data;
      });
    }
  }
  Admin_Portal_Ctrl_TicketFormWidget.initClass();

  return Admin_Portal_Ctrl_TicketFormWidget.EXPORT_CTRL();
});
