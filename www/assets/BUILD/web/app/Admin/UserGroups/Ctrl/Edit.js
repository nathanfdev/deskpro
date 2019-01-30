// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_UserGroups_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_UserGroups_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS    = ['$stateParams', '$q'];
    }

    init() {
      this.groupId = parseInt(this.$stateParams.id) || 0;
      this.ugData = this.DataService.get('UserGroups');

      this.service = {
        ticketDeps: this.DataService.get('TicketDeps'),
        chatDeps: this.DataService.get('ChatDeps')
      };

      this.all_perms = {
        perms: {},
        deps_perms: {
          tickets: {full: false},
          chat: {full: false}
        },
        all_tickets_locked: false,
        all_chat_locked: false
      };

      return this.group = null;
    }

    initialLoad() {
      const groupPromise = this.ugData.loadEditUserGroupData(this.$stateParams.id || null);
      const promises = [groupPromise, this.service.ticketDeps.all(true), this.service.chatDeps.all(true)];

      return this.$q.all(promises).then(res => {
        let subdep;
        this.group             = res[0].group;
        this.everyoneGroup     = res[0].everyone_group;
        this.registeredGroup   = res[0].reg_group;
        this.form              = res[0].form;
        this.perm_form         = this.group.perms;
        this.perm_form.options = {};

        if (this.group.sys_name === 'everyone') {
          this.perm_form_everyone = null;
          this.perm_form_reg      = null;
        } else if (this.group.sys_name === 'registered') {
          this.perm_form_everyone = res[0].everyone_group.is_enabled ? res[0].everyone_group.perms : null;
          this.perm_form_reg      = null;
        } else {
          this.perm_form_everyone = res[0].everyone_group.is_enabled ? res[0].everyone_group.perms : null;
          this.perm_form_reg      = res[0].reg_group.is_enabled     ? res[0].reg_group.perms       : null;
        }

        if (__guard__(this.perm_form != null ? this.perm_form.ticket : undefined, x => x.reopen_resolved_createnew) || __guard__(this.perm_form_reg != null ? this.perm_form_reg.ticket : undefined, x1 => x1.reopen_resolved_createnew)) {
          this.perm_form.options.reopen_resolved_createnew = 'new_ticket';
        } else {
          this.perm_form.options.reopen_resolved_createnew = 'reject';
        }

        // deps need to be flattened to show in the table
        this.chatDeps = [];
        for (var dep of Array.from(res[2])) {
          this.chatDeps.push(dep);
          if (dep.children) {
            for (subdep of Array.from(dep.children)) {
              subdep.depth = 1;
              this.chatDeps.push(subdep);
            }
          }
        }


        // deps need to be flattened to show in the table
        this.ticketDeps = [];
        for (dep of Array.from(res[1])) {
          this.ticketDeps.push(dep);
          if (dep.children) {
            for (subdep of Array.from(dep.children)) {
              subdep.depth = 1;
              this.ticketDeps.push(subdep);
            }
          }
        }

        this.assignDepsPerms(this.everyoneGroup);
        this.assignDepsPerms(this.registeredGroup);
        this.assignDepsPerms(this.group);

        if (this.group.deps_perms.tickets.length) {
          if (Object.keys(this.group.deps_perms.tickets).reduce((x, y) => x && this.isLocked(y, 'tickets'))) { this.all_perms.all_tickets_locked = true; }
        }
        if (this.group.deps_perms.chat.length) {
          if (Object.keys(this.group.deps_perms.chat).reduce((x, y) => x && this.isLocked(y, 'chat'))) { this.all_perms.all_chat_locked = true; }
        }

        return this.updateAllPermsState();
      });
    }

    saveForm() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      const is_new = !this.group.id;
      const promise = this.ugData.saveFormModel(this.group, this.form, this.perm_form);

      this.startSpinner('saving');
      return promise.then( () => {
        this.stopSpinner('saving', true).then(() => {
          return this.Growl.success("Saved");
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('crm.groups.gocreate');
        }
      });
    }

    /*
      * Shows the copy settings modal
      */
    showDelete() {
      let inst;
      const deleteGroup = () => {
        const p = this.ugData.removeGroupById(this.groupId);
        p.then(() => {
          return this.$state.go('crm.groups');
        });
        return p;
      };

      const { group } = this;
      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('UserGroups/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.group = group;
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doDelete = function(options) {
            $scope.is_loading = true;
            return deleteGroup().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }


    assignDepsPerms(group) {

      let full, u;
      group.deps_perms = {
        tickets: {},
        chat: {}
      };

      for (var dep of Array.from(this.ticketDeps)) {
        full = false;
        if (dep.permissions != null ? dep.permissions.usergroups : undefined) {
          u = dep.permissions.usergroups.filter(x => x.id === group.id)[0];
          if (group.sys_name === 'everyone') {
            if (u) { full = true; }
          } else if (group.sys_name === 'registered') {
            if (u || (this.everyoneGroup.deps_perms.tickets[dep.id].full === true)) { full = true; }
          } else {
            if (u || (this.everyoneGroup.deps_perms.tickets[dep.id].full === true) || (this.registeredGroup.deps_perms.tickets[dep.id].full === true)) { full = true; }
          }

          u = dep.permissions.usergroups.filter(x => x.sys_name === group.id)[0];
        }

        group.deps_perms.tickets[dep.id] = { full };
      }

      return (() => {
        const result = [];
        for (dep of Array.from(this.chatDeps)) {
          full = false;
          if (dep.permissions != null ? dep.permissions.usergroups : undefined) {
            u = dep.permissions.usergroups.filter(x => x.id === group.id)[0];
            if (group.sys_name === 'everyone') {
              if (u) { full = true; }
            } else if (group.sys_name === 'registered') {
              if (u || (this.everyoneGroup.deps_perms.chat[dep.id].full === true)) { full = true; }
            } else {
              if (u || (this.everyoneGroup.deps_perms.chat[dep.id].full === true) || (this.registeredGroup.deps_perms.chat[dep.id].full === true)) { full = true; }
            }
          }

          result.push(group.deps_perms.chat[dep.id] = { full });
        }
        return result;
      })();
    }

    updateAllPermsState() {
      let enabled;
      if ((this.group == null)) { return; }

      for (var section of Object.keys(this.group.perms || {})) {
        const perms = this.group.perms[section];
        enabled = true;
        for (let perm of Object.keys(perms || {})) {
          if (!perms[perm]) {
            enabled = false;
            break;
          }
        }
        this.all_perms.perms[section] = enabled;
      }

      if (!this.group.deps_perms) { return; }
      return (() => {
        const result = [];
        for (var type of Object.keys(this.all_perms.deps_perms || {})) {
          var sections = this.all_perms.deps_perms[type];
          result.push((() => {
            const result1 = [];
            for (section of Object.keys(sections || {})) {
              enabled = true;
              for (let dep of Object.keys(this.group.deps_perms[type] || {})) {
                if (!this.group.deps_perms[type][dep][section]) {
                  enabled = false;
                }
              }
              result1.push(this.all_perms.deps_perms[type][section] = enabled);
            }
            return result1;
          })());
        }
        return result;
      })();
    }

    changeAllPerms(type) {
      if ((this.group == null)) { return; }

      if (('deps_perms_tickets' === type) && this.group.deps_perms.tickets) {
        return (() => {
          const result = [];
          for (let dep of Object.keys(this.group.deps_perms.tickets || {})) {
            if(!this.isLocked(dep, 'tickets')) {
              result.push(this.group.deps_perms.tickets[dep].full = this.all_perms.deps_perms.tickets.full);
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();

      } else if (('deps_perms_chat' === type) && this.group.deps_perms.chat) {
        return (() => {
          const result1 = [];
          for (let dep of Object.keys(this.group.deps_perms.chat || {})) {
            if(!this.isLocked(dep, 'chat')) {
              result1.push(this.group.deps_perms.chat[dep].full = this.all_perms.deps_perms.chat.full);
            } else {
              result1.push(undefined);
            }
          }
          return result1;
        })();
      }
    }

    isLocked(depId, type) {
      if (this.group.sys_name === 'everyone') { return false;
      } else if (this.group.sys_name === 'registered') { return this.everyoneGroup.deps_perms[type][depId].full === true;
      } else { return (this.everyoneGroup.deps_perms[type][depId].full === true) || (this.registeredGroup.deps_perms[type][depId].full === true); }
    }
  }
  Admin_UserGroups_Ctrl_Edit.initClass();


  return Admin_UserGroups_Ctrl_Edit.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}