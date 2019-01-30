/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util'
], function(
  Admin_Ctrl_Base,
  Admin_Main_Model_DepAgentPermMatrix,
  Util
) {
  class Admin_ChatDeps_Ctrl_Edit extends Admin_Ctrl_Base {
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
      this.CTRL_ID   = 'Admin_ChatDeps_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$upload', '$http'];
    }

    /*
  *
  */

    init() {

      this.depData = this.DataService.get('ChatDeps');
      this.$scope.icon_image = null;
      this.$scope.$on('icon.selected', (e, path) => this.selectIcon(path));
      this.$scope.usergroups_locked = {};

      this.initializeScopeWatching();
      return this.all_perms = {
        user_full:    true,
        agent_full:   true
      };
    }


    /*
     *
   */

    resetForm() {

      return this.form = Util.clone(this.origForm, true);
    }

    /*
  * Loads data that will be used inside the view into scope of controller
  */

    initialLoad() {

      const promise = this.depData.getEditDepartmentData(this.$stateParams.id || null).then( data => {

        this.dep  = data.dep;
        this.form = data.form;
        this.origForm = Util.clone(this.form, true);

        this.usergroups  = data.usergroups;
        this.agentgroups = data.agentgroups;
        this.agents      = data.agents;
        this.brands      = data.brands;
        this.chatQueues  = data.chatQueues;

        this.dep_parent_list = data.dep_parent_list;
        this.setAvatar(this.dep.avatar);

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

        for (var id of Object.keys(this.form.usergroup_perms || {})) {
          group = this.form.usergroup_perms[id];
          if (!group.full) { this.all_perms.user_full = false; }
        }

        for (id of Object.keys(this.form.agent_perms.groups || {})) {
          group = this.form.agent_perms.groups[id];
          if (!group.perms.full.locked && !group.perms.full.state) { this.all_perms.agent_full = false; }
        }

        return (() => {
          const result = [];
          for (id of Object.keys(this.form.agent_perms.agents || {})) {
            const agent = this.form.agent_perms.agents[id];
            if (!agent.perms.full.locked && !agent.perms.full.state) { result.push(this.all_perms.agent_full = false); } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      });

      return promise;
    }

    /*
  * Checks whether data entered to form was changed or not
  * This is useful for showing modal dialog that notifies user that he has edited the form
    */

    isDirtyState() {
      return false;
      return !Util.equals(this.form, this.origForm);
    }

    /*
     * Saves the form
     */

    saveAll() {

      const is_new = !this.dep.id;

      if (!this.$scope.form_props.$valid) {
        return;
      }

      // @form is used due to the reason that upon clicking on submit button parent_id still has old value

      if (this.depData.hasChildrenAndChangedParent(this.dep, this.form)) {
        this.showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.");
        return;
      }

      this.startSpinner('saving_dep');

      if (this.form.enable_avatar) {
        this.form.avatar = (this.dep.avatar != null ? this.dep.avatar.id : undefined) || null;
      } else {
        this.form.avatar = null;
      }
      const promise = this.depData.saveFormModel(this.dep, this.form);

      promise.success( () => {

        this.origForm = Util.clone(this.form, true);

        this.stopSpinner('saving_dep', true).then( () => {
          return this.Growl.success(this.getRegisteredMessage('saved_dep'));
        });

        if (is_new) {
          return this.$state.go('chat.chat_deps.gocreate');
        }
      });

      promise.error( (info, code) => {

        this.stopSpinner('saving_dep');
        this.applyErrorResponseToView(info);
        if (info != null ? info.error_message : undefined) { return this.Growl.error(info != null ? info.error_message : undefined); }
      });

      return promise;
    }

    /*
  * Watches for changing of parent_id that is in scope
  * This is usable inside view to show some notification information
    */

    initializeScopeWatching() {

      return this.$scope.$watch('EditCtrl.form.parent_id', newVal => {

        newVal = parseInt(newVal);

        if (!newVal) {
          this.$scope.show_parent_warning = false;
          return;
        }

        const parent = this.depData.findListModelById(newVal);

        if (parent && parent.children && !parent.children.length) {
          return this.$scope.show_parent_warning = parent;
        } else {
          return this.$scope.show_parent_warning = false;
        }
      });
    }

    /*
  *
    */

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


    changeAllPerms(group) {
      let id;
      const _perm = this.all_perms[group + '_full'];
      if ('user' === group) {
        for (id of Object.keys(this.form.usergroup_perms || {})) {
          group = this.form.usergroup_perms[id];
          group.full = _perm;
        }
      }
      if ('agent' === group) {
        for (id of Object.keys(this.form.agent_perms.groups || {})) {
          group = this.form.agent_perms.groups[id];
          if (!group.perms.full.locked && ('agent_all_perms' !== group.model.sys_name) && ('agent_all_safe_perms' !== group.model.sys_name)) { group.perms.full.state = _perm; }
        }
        return (() => {
          const result = [];
          for (id of Object.keys(this.form.agent_perms.agents || {})) {
            const agent = this.form.agent_perms.agents[id];
            if (!agent.perms.full.locked) { result.push(agent.perms.full.state = _perm); } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
    }
  }
  Admin_ChatDeps_Ctrl_Edit.initClass();



  return Admin_ChatDeps_Ctrl_Edit.EXPORT_CTRL();
});
