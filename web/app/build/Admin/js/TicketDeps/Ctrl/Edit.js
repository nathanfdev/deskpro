(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix, Util) {
    var Admin_TicketDeps_Ctrl_Edit;
    Admin_TicketDeps_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketDeps_Ctrl_Edit, _super);

      function Admin_TicketDeps_Ctrl_Edit() {
        return Admin_TicketDeps_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketDeps_Ctrl_Edit.CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit';

      Admin_TicketDeps_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketDeps_Ctrl_Edit.DEPS = ['$templateCache'];

      Admin_TicketDeps_Ctrl_Edit.prototype.init = function() {
        window.DEP_CTRL = this;
        this.depId = parseInt(this.$stateParams.id);
        this.depData = this.DataService.get('TicketDeps');
        this.$scope.$watch('EditCtrl.form.parent_id', (function(_this) {
          return function(newVal) {
            var parent;
            newVal = parseInt(newVal);
            if (!newVal) {
              _this.$scope.show_parent_warning = false;
              return;
            }
            parent = _this.depData.findListModelById(newVal);
            if (parent && !parent.children.length) {
              return _this.$scope.show_parent_warning = parent;
            } else {
              return _this.$scope.show_parent_warning = false;
            }
          };
        })(this));
        this.$scope.embed_code_type = 'department';
        return this.$scope.embedEditorLoaded = function(editor) {
          return $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor');
        };
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.resetForm = function() {
        return this.form = Util.clone(this.origForm, true);
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.depData.getEditDepartmentData(this.depId || null).then((function(_this) {
          return function(data) {
            var code, code_all, name, tpl, _i, _len, _ref, _results;
            _this.dep = data.dep;
            _this.form = data.form;
            _this.is_custom_layout = _this.form.use_custom_layout;
            _this.origForm = Util.clone(_this.form, true);
            _this.layout_info = data.layout_info;
            if (_this.depId) {
              _this.layout_info["default"] = _this.layout_info["default"].filter(function(x) {
                return x.id !== _this.depId;
              });
              _this.layout_info.custom = _this.layout_info.custom.filter(function(x) {
                return x.id !== _this.depId;
              });
            }
            _this.usergroups = data.usergroups;
            _this.agentgroups = data.agentgroups;
            _this.agents = data.agents;
            _this.email_accounts = data.email_accounts;
            _this.dep_parent_list = data.dep_parent_list;
            _ref = ['link', 'win', 'embed', 'phpapi'];
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              name = _ref[_i];
              tpl = _this.getTemplatePath("TicketDeps/code-" + name + ".html");
              code = _this.$templateCache.get(tpl).replace(/%DEPID%/g, _this.dep.id);
              code_all = _this.$templateCache.get(tpl).replace(/%DEPID%/g, 0);
              _this.$scope['code_' + name] = code;
              _results.push(_this.$scope['code_all_' + name] = code_all);
            }
            return _results;
          };
        })(this));
        return promise;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.isDirtyState = function() {
        return !Util.equals(this.form, this.origForm);
      };


      /**
      		 * Save everything
       */

      Admin_TicketDeps_Ctrl_Edit.prototype.saveAll = function() {
        var deferred2, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        if (this.depData.hasChildrenAndChangedParent(this.dep, this.form)) {
          this.showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.");
          return;
        }
        this.startSpinner('saving_dep');
        deferred2 = this.$q.defer();
        promise = this.depData.saveFormModel(this.dep, this.form);
        promise.then((function(_this) {
          return function() {
            if (_this.form.use_custom_layout) {
              _this.Api.sendPostJson("/ticket_layouts/" + _this.dep.id, {
                layout: _this.form.custom_layout
              }).then(function() {
                return deferred2.resolve();
              });
            } else {
              _this.Api.sendPostJson("/ticket_layouts/default", {
                layout: _this.form.default_layout
              }).then(function() {
                return deferred2.resolve();
              });
              _this.Api.sendDelete("/ticket_layouts/" + _this.dep.id);
            }
            return _this.is_custom_layout = _this.form.use_custom_layout;
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_dep');
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        deferred2.promise.then((function(_this) {
          return function() {
            _this.origForm = Util.clone(_this.form, true);
            return _this.stopSpinner('saving_dep').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_dep'), function() {
                return _this.$state.go('tickets.ticket_deps.edit', {
                  id: _this.dep.id
                });
              });
            });
          };
        })(this));
        return deferred2.promise;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.propogatePermission = function(obj, perm) {
        if (this._propogatePermission_running) {
          return;
        }
        this._propogatePermission_running = true;
        if (obj.type === 'group') {
          this.form.agent_perms.setGroupPerm(obj.model.id, perm, '&');
        } else {
          this.form.agent_perms.setAgentPerm(obj.model.id, perm, '&');
        }
        return this._propogatePermission_running = false;
      };


      /*
      		 * Open the email editor
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

//# sourceMappingURL=Edit.js.map
