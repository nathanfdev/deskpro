define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
  class Admin_TicketFields_Ctrl_EditWorkflows extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketFields_Ctrl_EditWorkflows';
      this.CTRL_AS = 'TicketWorks';
      this.DEPS    = [];
    }

    init() {
      this.works            = [];
      this.default_id       = 0;
      this.agent_required   = false;
      this.user_required    = false;
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        'info': '/ticket_works',
        'layouts': '/ticket_layouts/fields/workflow'
      }).then( res => {
        this.works          = res.data.info.workflows;
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
      if (!this.works || !this.works.length) {
        this.enabled = false;
      }

      const postData = {
        workflows:      this.works,
        default_id:     this.default_id,
        user_required:  this.user_required,
        agent_required: this.agent_required,
        enabled:        this.enabled
      };

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/ticket_works', postData).success( () => {
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x => x.saveLayoutData('workflow', this.user_layouts, this.agent_layouts));
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x1 => x1.setFieldEnabled('workflow', this.enabled));
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
          $scope.type = 'Workflow';
          $scope.plural_type = 'workflows';
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doConvert = function() {
            $scope.is_loading = true;
            return self.Api.sendPost('/ticket_fields/convert/workflows').then(
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
  Admin_TicketFields_Ctrl_EditWorkflows.initClass();

  return Admin_TicketFields_Ctrl_EditWorkflows.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}