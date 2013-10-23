(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix'], function(Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix) {
    var Admin_TicketDeps_Ctrl_Edit, _ref;
    Admin_TicketDeps_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketDeps_Ctrl_Edit, _super);

      function Admin_TicketDeps_Ctrl_Edit() {
        _ref = Admin_TicketDeps_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketDeps_Ctrl_Edit.CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit';

      Admin_TicketDeps_Ctrl_Edit.CTRL_AS = 'TicketDepsEdit';

      Admin_TicketDeps_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketDeps_Ctrl_Edit.DEPS = ['em', '$scope', 'DepartmentData', 'Api', '$stateParams', '$q', '$state', '$templateCache', 'Growl'];

      Admin_TicketDeps_Ctrl_Edit.prototype.init = function() {
        var _this = this;
        this.addManagedListener(this.DepartmentData.deps, 'changed', function() {
          _this.initDeplistData(_this.DepartmentData.deps);
          return _this.ngApply();
        });
        this.$scope.$watch('TicketDepsEdit.dep.parent_id', function(newVal) {
          var parent, _ref1;
          newVal = parseInt(newVal);
          if (!newVal) {
            _this.$scope.show_parent_warning = false;
            return;
          }
          parent = _this.DepartmentData.deps.get(newVal);
          if (parent && !((_ref1 = parent._child_ids) != null ? _ref1.length : void 0)) {
            return _this.$scope.show_parent_warning = parent;
          } else {
            return _this.$scope.show_parent_warning = false;
          }
        });
        return this.trigger = {
          user_send_newuserticket_response: {
            enabled: false,
            template_name: ""
          },
          user_send_newagentreply_response: {
            enabled: false,
            template_name: ""
          },
          user_send_newuserreply_response: {
            enabled: false,
            template_name: ""
          },
          set_from_name: {
            enabled: false,
            preset: ""
          }
        };
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.resetForm = function() {
        this.restoreState();
        return this.saveState('dep', 'agent_perms', 'usergroups');
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.initialLoad = function() {
        return this.loadDepartment();
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.checkDirtyState = function() {
        var _ref1;
        if (!((_ref1 = this.dep) != null ? _ref1.id : void 0)) {
          return false;
        }
        if (this.dep.getChangedFields().length) {
          return true;
        }
        return false;
      };

      /**
      		# Load (or reload) the page values
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.loadDepartment = function() {
        var promise, waiting,
          _this = this;
        waiting = [this.DepartmentData.loadDepList(), this.$stateParams.id ? this.Api.sendDataGet(['/ticket_deps/' + this.$stateParams.id, '/agents', '/agentgroups', '/usergroups', '/ticket_accounts']) : this.Api.sendDataGet(['/agents', '/agentgroups', '/usergroups', '/ticket_accounts'])];
        promise = this.$q.all(waiting).then(function(d) {
          var data_results, dep, dep_data, departments;
          departments = d[0], data_results = d[1];
          if (_this.$stateParams.id) {
            dep_data = data_results.data.api_ticket_deps_get;
          } else {
            dep_data = {
              department: {},
              perms_usergroup_ids: [],
              perms_agentgroup_ids: [],
              perms_agent_ids: []
            };
          }
          dep = dep_data.department;
          if (!dep.parent_id) {
            dep.parent_id = 0;
          }
          if (!dep.email_gateway_id) {
            dep.email_gateway_id = 0;
          }
          _this.dep = _this.em.createUnmanagedEntity('department', 'id', dep);
          _this.dep._enable_user_title = !!_this.dep.user_title;
          _this.initDeplistData(departments);
          _this.initEmailAccountsData(data_results.data.api_ticket_accounts.ticket_accounts);
          _this.initData({
            usergroups: dep_data.perms_usergroup_ids,
            agentgroups: dep_data.perms_agentgroup_ids,
            agents: dep_data.perms_agent_ids
          }, data_results.data.api_agents_list.agents, data_results.data.api_agentgroups_list.agentgroups, data_results.data.api_usergroups_list.usergroups);
          if (_this.dep.email_gateway_id === 0 && _this.email_accounts.length) {
            _this.dep.email_gateway_id = _.first(_this.email_accounts).id;
          }
          _this.saveState('dep', 'agent_perms', 'usergroups');
          if (_this.dep.id) {
            return _this.resolveWaitEntityPromise();
          }
        });
        return promise;
      };

      /**
      		# Init data from loadDepartment, getting it ready for use
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.initData = function(department_perms, agents, agentgroups, usergroups) {
        var agent, code, group, matrix, name, p, tpl, ugroup, ugroup_map, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref1, _ref2, _results;
        matrix = new Admin_Main_Model_DepAgentPermMatrix();
        for (_i = 0, _len = agentgroups.length; _i < _len; _i++) {
          group = agentgroups[_i];
          matrix.addGroup(group, []);
        }
        for (_j = 0, _len1 = agents.length; _j < _len1; _j++) {
          agent = agents[_j];
          matrix.addAgent(agent, []);
        }
        matrix.initPerms(department_perms.agentgroups, department_perms.agents);
        this.agent_perms = matrix;
        if (department_perms.usergroups) {
          ugroup_map = {};
          _ref1 = department_perms.usergroups;
          for (_k = 0, _len2 = _ref1.length; _k < _len2; _k++) {
            p = _ref1[_k];
            ugroup_map[p.usergroup_id] = p;
          }
          for (_l = 0, _len3 = usergroups.length; _l < _len3; _l++) {
            ugroup = usergroups[_l];
            if (ugroup_map[ugroup.id]) {
              ugroup.use = true;
            }
          }
        }
        this.usergroups = usergroups;
        _ref2 = ['link', 'win', 'embed'];
        _results = [];
        for (_m = 0, _len4 = _ref2.length; _m < _len4; _m++) {
          name = _ref2[_m];
          tpl = this.getTemplatePath("TicketDeps/code-" + name + ".html");
          code = this.$templateCache.get(tpl).replace(/%DEPID%/g, this.dep.id);
          _results.push(this.$scope['code_' + name] = code);
        }
        return _results;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.initDeplistData = function(departments) {
        var dep, _i, _len, _ref1, _results;
        this.departments = departments.values();
        if (!this.dep) {
          return;
        }
        this.dep_parent_list = departments.values();
        this.dep_parent_list = [
          {
            id: 0,
            title: 'No Parent'
          }
        ];
        _ref1 = this.departments;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          dep = _ref1[_i];
          if (this.dep.id !== dep.id && !dep.parent_id) {
            _results.push(this.dep_parent_list.push(dep));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.initEmailAccountsData = function(accounts) {
        return this.email_accounts = accounts;
      };

      /**
      		# Save everything
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.saveAll = function() {
        var full_title, is_new, model, parent, postData, promise, props,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        if (!this.dep._enable_user_title) {
          this.dep.user_title = '';
        }
        props = this.getPropsData();
        props.move_tickets_to = 'self';
        postData = {
          department: props,
          permissions: this.getPermsData()
        };
        this.startSpinner('saving_dep');
        if (this.dep.id) {
          is_new = false;
          promise = this.Api.sendPostJson('/ticket_deps/' + this.dep.id, postData);
        } else {
          is_new = true;
          promise = this.Api.sendPostJson('/ticket_deps/create', postData);
        }
        if (this.dep.parent_id && this.dep.parent_id !== "0") {
          parent = this.DepartmentData.deps.get(this.dep.parent_id);
          full_title = parent.title + ' > ' + this.dep.title;
        } else {
          full_title = this.dep.title;
        }
        promise.then(function() {
          return _this.stopSpinner('saving_dep').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_dep'), function() {
              return _this.$state.go('tickets.ticket_deps.edit', {
                id: _this.dep.id
              });
            });
          });
        });
        promise.error(function(info, code) {
          _this.stopSpinner('saving_dep');
          return _this.applyErrorResponseToView(info);
        });
        if (this.em.hasById('department', this.dep.id)) {
          model = this.em.getById('department', this.dep.id);
          model.title = this.dep.title;
          model._full_title = full_title;
          model.user_title = this.dep.user_title;
          model.parent_id = this.dep.parent_id;
          this.DepartmentData.resetHierarchy();
        } else {
          promise.success(function(result) {
            _this.dep.id = result.id;
            model = _this.em.createEntity('department', 'id', _this.dep.getData());
            model._full_title = full_title;
            if (!model.parent_id || model.parent_id === "0") {
              model.parent_id = null;
            }
            _this.DepartmentData.addToList(model);
            _this.DepartmentData.resetHierarchy();
            _this.initDeplistData(_this.DepartmentData.deps);
            _this.skipDirtyState();
            if (is_new) {
              if (_this.dep.id) {
                _this.resolveWaitEntityPromise();
              }
              return _this.$state.go('tickets.ticket_deps.gocreate');
            } else {
              return _this.$state.go('tickets.ticket_deps');
            }
          });
        }
        this.dep.setCheckpoint();
        this.depPerms = this.getPermsData();
        return promise;
      };

      /**
      		# Gets property data
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.getPropsData = function() {
        var data;
        data = this.dep.getData();
        data.email_gateway = this.dep.email_gateway_id;
        data.parent = this.dep.parent_id;
        return data;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.propogatePermission = function(obj, perm) {
        if (this._propogatePermission_running) {
          return;
        }
        this._propogatePermission_running = true;
        if (obj.type === 'group') {
          this.agent_perms.setGroupPerm(obj.model.id, perm, '&');
        } else {
          this.agent_perms.setAgentPerm(obj.model.id, perm, '&');
        }
        return this._propogatePermission_running = false;
      };

      /*
      		# Gets permission data that can be posted for saving
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.getPermsData = function(type) {
        var agentObj, groupObj, perms, usergroup, _i, _j, _k, _len, _len1, _len2, _ref1, _ref2, _ref3;
        if (type == null) {
          type = 'all';
        }
        perms = [];
        if (type === 'all' || type === 'agents') {
          _ref1 = this.agent_perms.agents;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            agentObj = _ref1[_i];
            if (agentObj.perms.full.state) {
              perms.push({
                person_id: agentObj.model.id,
                name: 'full',
                value: 1
              });
            } else if (agentObj.perms.assign.state) {
              perms.push({
                person_id: agentObj.model.id,
                name: 'assign',
                value: 1
              });
            }
          }
        }
        if (type === 'all' || type === 'agentgroups') {
          _ref2 = this.agent_perms.groups;
          for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
            groupObj = _ref2[_j];
            if (groupObj.perms.full.state) {
              perms.push({
                usergroup_id: groupObj.model.id,
                name: 'full',
                value: 1
              });
            } else if (groupObj.perms.assign.state) {
              perms.push({
                usergroup_id: groupObj.model.id,
                name: 'assign',
                value: 1
              });
            }
          }
        }
        if (type === 'all' || type === 'usergroups') {
          _ref3 = this.usergroups;
          for (_k = 0, _len2 = _ref3.length; _k < _len2; _k++) {
            usergroup = _ref3[_k];
            if (usergroup.use) {
              perms.push({
                usergroup_id: usergroup.id,
                name: 'use',
                value: 1
              });
            }
          }
        }
        return perms;
      };

      /*
      		# Open the email editor
      */


      Admin_TicketDeps_Ctrl_Edit.prototype.showEmailEditor = function(template_name, custom_name) {
        var modalInstance;
        modalInstance = this.$modal.open({
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/Templates/modal-email-editor.html',
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve: {
            templateName: function() {
              return custom_name;
            },
            variantOf: function() {
              return template_name;
            }
          }
        });
        return modalInstance;
      };

      return Admin_TicketDeps_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/