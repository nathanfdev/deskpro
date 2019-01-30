/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
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
  class Admin_ApiKeys_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.updateChildren = this.updateChildren.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID = 'Admin_ApiKeys_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS = ['$stateParams'];
    }

    init() {
      this.agents = [];
      this.form = {isSuperUser: false, flags: []};

      this.service = {
        keys:   this.DataService.get('ApiKeys'),
        agents: this.DataService.get('Agents'),
        tags:   this.DataService.get('ApiTags')
      };

      this.$scope.replayLogEntry = entry => {
        if (((entry != null ? entry.id : undefined) == null)) { return; }
        entry.response = null;
        return this.service.keys.replayLogEntry(entry).then(
          data => { return entry.response = data; },
          () => { return entry.response = {status: null, content: null};
         });
      };

      this.$scope.toggle = scope => scope.toggle();

      this.$scope.enable = node => {
        node.value = 1;
        if (node.nodes) { this.updateChildren(node.nodes, node.value); }
        return this.service.tags.updateTags(node.path, node.value, this.form.id);
      };


      this.$scope.default = node => {
        node.value = 0;
        if (node.nodes) { this.updateChildren(node.nodes, node.value); }
        return this.service.tags.updateTags(node.path, node.value, this.form.id);
      };

      return this.$scope.disable = node => {
        node.value = -1;
        if (node.nodes) { this.updateChildren(node.nodes, node.value); }
        return this.service.tags.updateTags(node.path, node.value, this.form.id);
      };
    }

    updateChildren(nodes, value) {
      return (() => {
        const result = [];
        for (let node of Array.from(nodes)) {
          node.value = value;
          if (node.nodes) { result.push(this.updateChildren(node.nodes, value)); } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }

    initialLoad() {
      this.form.daily_limit = this.service.keys.limits.daily_limit;
      this.form.hourly_limit = this.service.keys.limits.hourly_limit;

      const p1 = this.service.keys.get(this.$stateParams.id || null).then(model => {
        if ((model == null)) { return; }
        this.form = angular.copy(model);
        this.form.flags = this.form.flags || [];
        this.form.isSuperUser = this.form.flags.indexOf('super') > -1;
        this.form.isAdminManage = this.form.flags.indexOf('admin_manage') > -1;
        if (!this.form.daily_limit) { this.form.daily_limit = this.service.keys.limits.daily_limit; }
        if (!this.form.hourly_limit) { return this.form.hourly_limit = this.service.keys.limits.hourly_limit; }
      });


      const p2 = this.service.agents.all().then(agents => { return this.agents = agents; });

      // Load logs separately
      if (this.$stateParams.id) {
        this.service.tags.getTags(this.$stateParams.id).then(data => {
          return this.tags = data.join(',');
        });

        this.service.keys.getLogs({id: this.$stateParams.id}).then(data => {
          return this.logs = data.logs;
        });
      } else {
        this.tags = '*';
      }


      return this.$q.all([p1, p2]);
    }



    saveForm() {
      const is_new = !this.form.id;
      this.form.flags = [];

      if (this.form.isSuperUser) {
        this.form.flags.push('super');
      }
      if (this.form.isAdminManage) {
        this.form.flags.push('admin_manage');
      }

      this.startSpinner('saving');
      return this.service.keys.set(this.form).then(
        data => {
          this.form = data;
          this.form.flags = this.form.flags || [];
          this.form.isSuperUser = this.form.flags.indexOf('super') > -1;
          this.form.isAdminManage = this.form.flags.indexOf('admin_manage') > -1;
          return this.form;
        },
        () => {
          this.stopSpinner('saving', true);
          return this.Growl.error('Error');
      }).then(
        form => {
          return this.service.tags.updateTags(this.tags, form.id).then(
            data => {
              this.stopSpinner('saving', true);
              this.Growl.success('Saved');
              this.skipDirtyState();
              if (is_new) { return this.$state.go('apps.api_keys.gocreate'); }
            },
            reason => {
              this.stopSpinner('saving', true);
              return this.Growl.error('Error');
          });
      });
    }



    /*
     * Show the delete dlg
     */
    startDelete(for_key_id) {
      return this.service.keys.get(for_key_id).then(key => {
        if ((key == null)) { return; }

        const inst = this.$modal.open({
          templateUrl: this.getTemplatePath('ApiKeys/delete-modal.html'),
          controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
            $scope.confirm = () => $modalInstance.close();

            return $scope.dismiss = () => $modalInstance.dismiss();
          }
          ]
        });

        return inst.result.then(() => {
          return this.service.keys.remove(key).then(
            () => {
              return this.$state.go('apps.api_keys');
            },
            data => {
              return this.applyErrorResponseToView(data);
          });
        });
      });
    }



    regenerateApiKey() {
      return this.service.keys.regenerateApiKey(this.form).success(() => {
        return this.Growl.success("API Key regenerated");
      });
    }
  }
  Admin_ApiKeys_Ctrl_Edit.initClass();

  return Admin_ApiKeys_Ctrl_Edit.EXPORT_CTRL();
});