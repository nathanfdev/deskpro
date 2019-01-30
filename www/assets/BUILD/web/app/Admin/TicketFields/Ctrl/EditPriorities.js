// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketFields_Ctrl_EditPriorities extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketFields_Ctrl_EditPriorities';
      this.CTRL_AS = 'TicketPris';
      this.DEPS    = [];
    }

    init() {
      this.pris             = [];
      this.default_id       = 0;
      this.agent_required   = false;
      this.user_required    = false;
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        'info': '/ticket_pris',
        'layouts': '/ticket_layouts/fields/priority'
      }).then( res => {
        this.pris           = res.data.info.priorities;
        this.default_id     = res.data.info.default_id;
        this.agent_required = res.data.info.agent_required;
        this.user_required  = res.data.info.user_required;
        this.enabled        = res.data.info.enabled;

        this.user_layouts  = res.data.layouts.user_layouts;
        return this.agent_layouts = res.data.layouts.agent_layouts;
      });

      return data_promise;
    }

    save() {
      let promise;
      if (!this.pris || !this.pris.length) {
        this.enabled = false;
      }

      // pris dont have display order, but they have numeric 'priority' that works the same
      for (let p of Array.from(this.pris)) {
        p.priority = p.display_order;
      }

      const postData = {
        priorities:     this.pris,
        default_id:     this.default_id,
        user_required:  this.user_required,
        agent_required: this.agent_required,
        enabled:        this.enabled
      };

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/ticket_pris', postData).success( () => {
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x => x.saveLayoutData('priority', this.user_layouts, this.agent_layouts));
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x1 => x1.setFieldEnabled('priority', this.enabled));
        this.settings = angular.copy(this.$scope.settings);

        return this.stopSpinner('saving').then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }).error( (info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }

    showConvert(type) {
      const self = this;
      return this.$modal.open({
        templateUrl: this.getTemplatePath('TicketFields/convert-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.type = 'Priority';
          $scope.plural_type = 'priorities';
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doConvert = function() {
            $scope.is_loading = true;
            return self.Api.sendPost('/ticket_fields/convert/priorities').then(
              function(res) {
                $scope.is_loading = false;
                $scope.dismiss();
                if (__guard__(res.data != null ? res.data.field : undefined, x => x.id) != null) {
                  const ds = self.DataService.get('TicketFields');
                  ds.mergeDataModel(res.data.field);
                  return self.$state.go('tickets.fields.edit', {id: res.data.field.id});
                }
              },
              () => $scope.is_loading = false);
          };
        }
        ]
      });
    }
  }
  Admin_TicketFields_Ctrl_EditPriorities.initClass();

  return Admin_TicketFields_Ctrl_EditPriorities.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}