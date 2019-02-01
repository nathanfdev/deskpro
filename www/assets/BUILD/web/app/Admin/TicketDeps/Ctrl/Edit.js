define([
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util',
  'underscore'
], function(
  Admin_Ctrl_Base,
  Admin_Main_Model_DepAgentPermMatrix,
  Util,
  _
) {
  class Admin_TicketDeps_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.setAvatar = this.setAvatar.bind(this);
      this.selectIcon = this.selectIcon.bind(this);
      this.changeAllPerms = this.changeAllPerms.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_TicketDeps_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$templateCache', 'dpObTypesDefTicketActions', '$location', '$upload', '$http'];
    }

    init() {
      this.actionsTypeDef = this.dpObTypesDefTicketActions;
      this.$scope.actionOptionTypes = [];
      this.$scope.actions_form = {};
      this.$scope.actions_form2 = {};
      this.$scope.usergroups_locked = {};

      this.$scope.icon_image = null;
      this.$scope.$on('icon.selected', (e, path) => this.selectIcon(path));

      this.depId = parseInt(this.$stateParams.id);
      this.depData = this.DataService.get('TicketDeps');
      this.all_perms = {
        user_full:    true,
        agent_assign: true,
        agent_full:   true
      };

      this.$scope.$watch('EditCtrl.form.parent_id', newVal => {
        newVal = parseInt(newVal);
        if (!newVal) {
          this.$scope.show_parent_warning = false;
          return;
        }

        const parent = this.depData.findListModelById(newVal);
        if (parent && !parent.children.length) {
          return this.$scope.show_parent_warning = parent;
        } else {
          return this.$scope.show_parent_warning = false;
        }
      });

      this.$scope.embed_code_type = 'department';

      return this.$scope.embedEditorLoaded = editor => $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
    }

    resetForm() {
      return this.form = Util.clone(this.origForm, true);
    }

    updateCriteriaOptionTypes() {
      const types = ['web', 'web.user'];
      const setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: this.customActions });
      this.$scope.actionOptionTypes.length = 0;
      return Array.from(setActionOptions).map((opt) =>
        this.$scope.actionOptionTypes.push(opt));
    }

    initialLoad() {
      const promise1 = this.depData.getEditDepartmentData(this.depId || null).then( data => {
        this.dep  = data.dep;
        this.form = data.form;
        this.is_custom_layout = this.form.use_custom_layout;
        this.origForm = Util.clone(this.form, true);
        this.layout_info = data.layout_info;

        this.setAvatar(this.dep.avatar);

        if (this.depId) {
          this.layout_info.default = this.layout_info.default.filter(x => { return x.id !== this.depId; });
          this.layout_info.custom = this.layout_info.custom.filter(x => { return x.id !== this.depId; });
        }

        this.usergroups  = data.usergroups;
        this.agentgroups = data.agentgroups;
        this.agents      = data.agents;
        this.brands      = data.brands;

        this.email_accounts  = data.email_accounts;
        this.dep_parent_list = data.dep_parent_list;

        for (let name of ['link', 'win', 'embed', 'phpapi']) {
          const tpl = this.getTemplatePath(`TicketDeps/code-${name}.html`);
          const code = this.$templateCache.get(tpl).replace(/%DEPID%/g, this.dep.id);
          const code_all = this.$templateCache.get(tpl).replace(/%DEPID%/g, 0);
          this.$scope[`code_${name}`] = code;
          this.$scope[`code_all_${name}`] = code_all;
        }

        for (var group of Array.from(this.usergroups)) {
          if ((group.sys_name !== 'everyone') && (group.sys_name !== 'registered')) { continue; }
          this[`group_${group.sys_name}_id`] = group.id;
          (group => {
            this[`group_${group.sys_name}_perm`] = this.form.usergroup_perms[group.id].full;
            return this.$scope.$watch((() => { return this.form.usergroup_perms[group.id].full; }), newVal => {
              this[`group_${group.sys_name}_perm`] = newVal;
              return (() => {
                const result = [];
                for (let id of Object.keys(this.form.usergroup_perms || {})) {
                  const g = this.form.usergroup_perms[id];
                  id = parseInt(id);
                  if (id === this.group_everyone_id) { continue; }
                  g.full = this.group_everyone_perm || this.group_registered_perm || g.full;
                  if (id === this.group_registered_id) { result.push(this.$scope.usergroups_locked[id] = this.group_everyone_perm);
                  } else { result.push(this.$scope.usergroups_locked[id] = this.group_everyone_perm || this.group_registered_perm); }
                }
                return result;
              })();
          });
          }
          )(group);
        }

        for (var id of Object.keys(this.form.agent_perms.groups || {})) {
          group = this.form.agent_perms.groups[id];
          if (!group.perms.assign.locked && !group.perms.assign.state) { this.all_perms.agent_assign = false; }
          if (!group.perms.full.locked && !group.perms.full.state) { this.all_perms.agent_full = false; }
        }

        return (() => {
          const result = [];
          for (id of Object.keys(this.form.agent_perms.agents || {})) {
            const agent = this.form.agent_perms.agents[id];
            if (!agent.perms.assign.locked && !agent.perms.assign.state) { this.all_perms.agent_assign = false; }
            if (!agent.perms.full.locked && !agent.perms.full.state) { result.push(this.all_perms.agent_full = false); } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      });

      const get = {
        customActions: '/ticket_triggers/get-custom-actions'
      };
      if (this.depId) {
        get.trigger = `/ticket_triggers/departments/${this.depId}`;
        get.trigger2 = `/ticket_triggers/departments_changed/${this.depId}`;
      } else {
        get.trigger = "/ticket_triggers/newticket";
        get.trigger2 = "/ticket_triggers/update";
      }

      const promise2 = this.Api.sendDataGet(get).then( result => {
        let action, rowId;
        this.customActions = result.data.customActions.action_defs;

        if (result.data && result.data.trigger) {
          if (result.data.trigger.trigger) {
            this.trigger = result.data.trigger.trigger;
            this.triggerId = this.trigger.id;

            if (__guard__(this.trigger.actions != null ? this.trigger.actions.actions : undefined, x => x.length)) {
              this.$scope.actions_form = {};
              for (action of Array.from(this.trigger.actions.actions)) {
                rowId = _.uniqueId('action');
                this.$scope.actions_form[rowId] = action;
              }
            }
          } else {
            this.trigger = result.data.trigger;
            this.triggerId = 0;
          }
        } else {
          this.trigger = {};
          this.triggerId = 0;
        }

        if (result.data && result.data.trigger2) {
          if (result.data.trigger2.trigger) {
            this.trigger2 = result.data.trigger2.trigger;
            this.trigger2Id = this.trigger2.id;

            if (__guard__(this.trigger2.actions != null ? this.trigger2.actions.actions : undefined, x1 => x1.length)) {
              this.$scope.actions_form2 = {};
              return (() => {
                const result1 = [];
                for (action of Array.from(this.trigger2.actions.actions)) {
                  rowId = _.uniqueId('action');
                  result1.push(this.$scope.actions_form2[rowId] = action);
                }
                return result1;
              })();
            }
          } else {
            this.trigger2 = result.data.trigger2;
            return this.trigger2Id = 0;
          }
        } else {
          this.trigger2 = {};
          return this.trigger2Id = 0;
        }
      });

      const promise3 = this.actionsTypeDef.loadDataOptions();

      const promises = [promise1, promise2, promise3];

      return this.$q.all(promises).then(() => {
        this.updateCriteriaOptionTypes();

        const search = this.$location.search();
        if (search && search.tab) {
          return this.$scope.dp_tab_ids.main = search.tab;
        }
      });
    }

    isDirtyState() {
      return false;
    }
      // should check this
      // return not Util.equals(@form, @origForm)

    /**
     * Save everything
     */
    saveAll() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      // @form is used due to the reason that upon clicking on submit button parent_id still has old value
      if (this.depData.hasChildrenAndChangedParent(this.dep, this.form)) {
        this.showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.");
        return;
      }

      this.startSpinner('saving_dep');

      const deferred2 = this.$q.defer();

      const triggerSaver = () => {
        let act, key;
        if (this.dep.has_children) { return; }
        let postData = {
          actions:       []
        };
        if (this.$scope.actions_form) {
          for (key of Object.keys(this.$scope.actions_form || {})) {
            act = this.$scope.actions_form[key];
            if (act.type) {
              postData.actions.push(act);
            }
          }
        }
        const p1 = this.Api.sendPostJson(`/ticket_triggers/departments/${this.dep.id}`, postData);

        postData = {
          actions:       []
        };
        if (this.$scope.actions_form2) {
          for (key of Object.keys(this.$scope.actions_form2 || {})) {
            act = this.$scope.actions_form2[key];
            if (act.type) {
              postData.actions.push(act);
            }
          }
        }
        const p2 = this.Api.sendPostJson(`/ticket_triggers/departments_changed/${this.dep.id}`, postData);

        return this.$q.all([p1,p2]);
      };

      if (this.form.enable_avatar) {
        this.form.avatar = (this.dep.avatar != null ? this.dep.avatar.id : undefined) || null;
      } else {
        this.form.avatar = null;
      }

      const promise = this.depData.saveFormModel(this.dep, this.form);
      promise.then(() => {
        triggerSaver();

        if (this.dep.has_children) {
          this.successSaving();
          return;
        }

        if (this.form.use_custom_layout) {
          this.Api.sendPostJson(`/ticket_layouts/${this.dep.id}`, {layout: this.form.custom_layout}).then(() => deferred2.resolve());
        } else {
          this.Api.sendPostJson("/ticket_layouts/default", {layout: this.form.default_layout}).then(() => deferred2.resolve());
          this.Api.sendDelete(`/ticket_layouts/${this.dep.id}`);
        }

        return this.is_custom_layout = this.form.use_custom_layout;
      });
      promise.error( (info, code) => {
        this.stopSpinner('saving_dep');
        this.applyErrorResponseToView(info);
        if (info != null ? info.error_message : undefined) { return this.Growl.error(info != null ? info.error_message : undefined); }
      });

      deferred2.promise.then(() => {
        this.origForm = Util.clone(this.form, true);
        return this.successSaving();
      });

      return deferred2.promise;
    }

    successSaving() {
      return this.stopSpinner('saving_dep').then(() => {
        return this.Growl.success(this.getRegisteredMessage('saved_dep'), () => {
          return this.$state.go('tickets.ticket_deps.edit', {id: this.dep.id});
        });
      });
    }

    propogatePermission(obj, perm) {
      if (this._propogatePermission_running) { return; }
      this._propogatePermission_running = true;
      if (obj.type === 'group') {
        this.form.agent_perms.setGroupPerm(obj.model.id, perm, '&');
      } else {
        this.form.agent_perms.setAgentPerm(obj.model.id, perm, '&');
      }
      return this._propogatePermission_running = false;
    }

    /*
     * Open the email editor
     */
    showEmailEditor(template_name, custom_name) {
      const modalInstance = this.$modal.open({
        templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
        controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
        resolve: {
          templateName() {
            return custom_name;
          },

          variantOf() {
            return template_name;
          }
        }
      });

      return modalInstance;
    }



    setAvatar(blob) {
      this.dep.avatar = blob;
      if ((blob == null)) {
        this.$scope.icon_image = null;
        return this.form.enable_avatar = false;
      } else {
        this.$scope.icon_image = blob.thumbnail_url_50;
        return this.form.enable_avatar = true;
      }
    }



    onFileSelect(files) {
      this.$scope.uploading = false;
      const file = files[0];

      return this.$upload.upload({
        url: this.$http.formatApiUrl('/misc/upload'),
        data: { is_image: true },
        file
      }).success( data => {
        this.$scope.uploading = false;
        return this.setAvatar(data.blob);
      }).error( data => {
        this.$scope.uploading = false;
        return this.Growl.error((data != null ? data.error_message : undefined) || 'Error');
      });
    }



    selectIcon(image) {
      if ((image == null)) { setAvatar(null); }

      this.$scope.uploading = true;
      return this.Api.sendPostJson('/misc/upload', {path: image, is_image: true}).then(
        data => {
          this.$scope.uploading = false;
          return this.setAvatar(data.data.blob);
        },
        () => {
          return this.$scope.uploading = false;
      });
    }


    handleBrand(brandId, e) {
      const index = this.form.brands.indexOf(brandId);
      if (index === -1) {
        return this.form.brands.unshift(brandId);
      } else {
        if (this.form.brands.length > 1) {
          return this.form.brands.splice(index, 1);
        } else {
          alert("Departments need to be linked to at least one Brand");
          $(e.target).prop("checked", true);
          return true;
        }
      }
    }




    changeAllPerms(group, perm) {
      let id;
      const _perm = this.all_perms[group + '_' + perm];
      if ('user' === group) {
        for (id of Object.keys(this.form.usergroup_perms || {})) {
          group = this.form.usergroup_perms[id];
          group.full = _perm;
        }
      }
      if ('agent' === group) {
        for (id of Object.keys(this.form.agent_perms.groups || {})) {
          group = this.form.agent_perms.groups[id];
          if (!group.perms[perm].locked && ('agent_all_perms' !== group.model.sys_name) && ('agent_all_safe_perms' !== group.model.sys_name)) { group.perms[perm].state = _perm; }
        }
        return (() => {
          const result = [];
          for (id of Object.keys(this.form.agent_perms.agents || {})) {
            const agent = this.form.agent_perms.agents[id];
            if (!agent.perms[perm].locked) { result.push(agent.perms[perm].state = _perm); } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }
  }
  Admin_TicketDeps_Ctrl_Edit.initClass();




  return Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}