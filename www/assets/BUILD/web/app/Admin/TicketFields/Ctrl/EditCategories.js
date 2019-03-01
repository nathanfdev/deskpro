define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
  class Admin_TicketFields_Ctrl_EditCategories extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketFields_Ctrl_EditCategories';
      this.CTRL_AS = 'TicketCats';
      this.DEPS    = [];
    }

    init() {
      this.cats             = [];
      this.default_id       = 0;
      this.agent_required   = false;
      this.user_required    = false;
      this.cat_parent_list  = [];

      this.$scope.$watchCollection('TicketCats.cats', () => this.updateCatParentList()
      , true);
    }

    updateCatParentList() {
      this.cat_parent_list = [];

      const flat = Arrays.analyzeFlatCatStructure(this.cats);
      const valid_ids = [];
      for (const cat of Array.from(flat)) {
        if (!cat.child_ids.length) {
          valid_ids.push(cat.id);
          this.cat_parent_list.push({
            id:    cat.id,
            title: cat.full_title
          });
        }
      }

      if (valid_ids.indexOf(this.default_id) === -1) {
        return this.default_id = 0;
      }
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        info:    '/ticket_cats',
        layouts: '/ticket_layouts/fields/category'
      }).then((res) => {
        this.cats           = res.data.info.categories;
        this.default_id     = res.data.info.default_id;
        this.agent_required = res.data.info.agent_required;
        this.user_required  = res.data.info.user_required;
        this.enabled        = res.data.info.enabled;

        this.user_layouts  = res.data.layouts.user_layouts;
        this.agent_layouts = res.data.layouts.agent_layouts;

        return this.updateCatParentList();
      });

      return data_promise;
    }

    save() {
      let promise;
      if (!this.cats || !this.cats.length) {
        this.enabled = false;
      }

      const postData = {
        categories:     this.cats,
        default_id:     this.default_id,
        user_required:  this.user_required,
        agent_required: this.agent_required,
        enabled:        this.enabled
      };

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/ticket_cats', postData).success(() => {
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x => x.saveLayoutData('category', this.user_layouts, this.agent_layouts));
        __guard__(this.$scope.$parent != null ? this.$scope.$parent.TicketFieldsList : undefined, x1 => x1.setFieldEnabled('category', this.enabled));
        this.settings = angular.copy(this.$scope.settings);

        return this.stopSpinner('saving').then(() => this.Growl.success(this.getRegisteredMessage('saved_settings')));
      }).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }

    showConvert(type) {
      const self = this;
      return this.$modal.open({
        templateUrl: this.getTemplatePath('TicketFields/convert-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.type = 'Category';
          $scope.plural_type = 'categories';
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doConvert = function () {
            $scope.is_loading = true;
            return self.Api.sendPost('/ticket_fields/convert/categories').then(
              (res) => {
                $scope.is_loading = false;
                $scope.dismiss();
                if (__guard__(res.data != null ? res.data.field : undefined, x => x.id) != null) {
                  const ds = self.DataService.get('TicketFields');
                  ds.mergeDataModel(res.data.field);
                  return self.$state.go('tickets.fields.edit', { id: res.data.field.id });
                }
              },
              () => $scope.is_loading = false);
          };
        }
        ]
      });
    }
  }
  Admin_TicketFields_Ctrl_EditCategories.initClass();

  return Admin_TicketFields_Ctrl_EditCategories.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
