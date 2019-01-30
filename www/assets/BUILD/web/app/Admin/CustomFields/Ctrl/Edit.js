// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'angular'
], function(
  Admin_Ctrl_Base,
  angular
) {
  class Admin_CustomFields_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = [];
    }



    init() {
      const { data } = this.$state.current;
      this.service = this.DataService.get('CustomFields', data.owner, data.context);
      this.$scope.context = data.context;

      this.$scope.definition = {
        form_type: 'contextual_choice',
        context_class: data.context,
        options: {}
      };

      this.options = {
        expanded: 1,
        multiple: 2
      };

      return this.$scope.options =
        {choices: 0};
    }



    initialLoad() {
      if (!this.$stateParams.id) { return; }

      return this.service.get(parseInt(this.$stateParams.id)).then(model => {
        if ((model == null)) { return; }

        if ('[object Array]' === Object.prototype.toString.call( model.options )) { model.options = {}; }
        this.$scope.definition = angular.copy(model);

        const multiple = this.$scope.definition.options.multiple ? this.options.multiple : 0;
        const expanded = this.$scope.definition.options.expanded ? this.options.expanded : 0;
        return this.$scope.options.choices = this.$scope.options.choices | multiple | expanded;
      });
    }



    saveForm() {
      const is_new = !this.$scope.definition.id;

      this.$scope.definition.options.multiple = this.options.multiple === (this.$scope.options.choices & this.options.multiple);
      this.$scope.definition.options.expanded = this.options.expanded === (this.$scope.options.choices & this.options.expanded);

      const promise = this.service.set(this.$scope.definition);
      this.startSpinner('saving');

      return promise.then(
        () => {
          this.stopSpinner('saving', true).then(() => this.Growl.success('Saved'));
          this.skipDirtyState();
          if (is_new) {
            return this.$state.go(this.$state.current.name.replace(/\.(edit|create)$/, '.gocreate'));
          }
        },
        (info, code) => {
          this.stopSpinner('saving', true);
          return this.applyErrorResponseToView(info);
      });
    }



    startDelete() {
      const doDelete = () => this.service.remove(this.$scope.definition);

      return this.$modal.open({
        templateUrl: this.getTemplatePath('CustomField/delete-modal.html'),
        controller: ['$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
          $scope.confirm = () =>
            $scope.is_loading =
              doDelete().then(function() {
                const baseParts = $state.current.name.split('.');
                $state.go(baseParts[0] + '.' + baseParts[1]);
                return $modalInstance.dismiss();
              })
          ;

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });
    }
  }
  Admin_CustomFields_Ctrl_Edit.initClass();



  return Admin_CustomFields_Ctrl_Edit.EXPORT_CTRL();
});