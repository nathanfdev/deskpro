(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix, Util) {
    var Admin_TicketDeps_Ctrl_Edit;
    Admin_TicketDeps_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketDeps_Ctrl_Edit, _super);

      function Admin_TicketDeps_Ctrl_Edit() {
        this.selectIcon = __bind(this.selectIcon, this);
        this.setAvatar = __bind(this.setAvatar, this);
        return Admin_TicketDeps_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketDeps_Ctrl_Edit.CTRL_ID = 'Admin_TicketDeps_Ctrl_Edit';

      Admin_TicketDeps_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketDeps_Ctrl_Edit.DEPS = ['$templateCache', 'dpObTypesDefTicketActions', '$location', '$upload', '$http'];

      Admin_TicketDeps_Ctrl_Edit.prototype.init = function() {
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.$scope.actionOptionTypes = [];
        this.$scope.actions_form = {};
        this.$scope.actions_form2 = {};
        this.$scope.icon_image = null;
        this.$scope.$on('icon.selected', (function(_this) {
          return function(e, path) {
            return _this.selectIcon(path);
          };
        })(this));
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

      Admin_TicketDeps_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, types, _i, _len, _results;
        types = ['web', 'web.user'];
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, {
          dynamicOptions: this.customActions
        });
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_i = 0, _len = setActionOptions.length; _i < _len; _i++) {
          opt = setActionOptions[_i];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.initialLoad = function() {
        var get, promise1, promise2, promise3, promises;
        promise1 = this.depData.getEditDepartmentData(this.depId || null).then((function(_this) {
          return function(data) {
            var code, code_all, name, tpl, _i, _len, _ref, _results;
            _this.dep = data.dep;
            _this.form = data.form;
            _this.is_custom_layout = _this.form.use_custom_layout;
            _this.origForm = Util.clone(_this.form, true);
            _this.layout_info = data.layout_info;
            _this.setAvatar(_this.dep.avatar);
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
        get = {
          customActions: '/ticket_triggers/get-custom-actions'
        };
        if (this.depId) {
          get.trigger = "/ticket_triggers/departments/" + this.depId;
          get.trigger2 = "/ticket_triggers/departments_changed/" + this.depId;
        }
        promise2 = this.Api.sendDataGet(get).then((function(_this) {
          return function(result) {
            var action, rowId, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
            _this.customActions = result.data.customActions.action_defs;
            if (((_ref = result.data) != null ? (_ref1 = _ref.trigger) != null ? _ref1.trigger : void 0 : void 0) != null) {
              _this.trigger = result.data.trigger.trigger;
              _this.triggerId = _this.trigger.id;
              if ((_ref2 = _this.trigger.actions) != null ? (_ref3 = _ref2.actions) != null ? _ref3.length : void 0 : void 0) {
                _this.$scope.actions_form = {};
                _ref4 = _this.trigger.actions.actions;
                for (_i = 0, _len = _ref4.length; _i < _len; _i++) {
                  action = _ref4[_i];
                  rowId = _.uniqueId('action');
                  _this.$scope.actions_form[rowId] = action;
                }
              }
            } else {
              _this.trigger = {};
              _this.triggerId = 0;
            }
            if (((_ref5 = result.data) != null ? (_ref6 = _ref5.trigger2) != null ? _ref6.trigger : void 0 : void 0) != null) {
              _this.trigger2 = result.data.trigger2.trigger;
              _this.trigger2Id = _this.trigger2.id;
              if ((_ref7 = _this.trigger2.actions) != null ? (_ref8 = _ref7.actions) != null ? _ref8.length : void 0 : void 0) {
                _this.$scope.actions_form2 = {};
                _ref9 = _this.trigger2.actions.actions;
                _results = [];
                for (_j = 0, _len1 = _ref9.length; _j < _len1; _j++) {
                  action = _ref9[_j];
                  rowId = _.uniqueId('action');
                  _results.push(_this.$scope.actions_form2[rowId] = action);
                }
                return _results;
              }
            } else {
              _this.trigger2 = {};
              return _this.trigger2Id = 0;
            }
          };
        })(this));
        promise3 = this.actionsTypeDef.loadDataOptions();
        promises = [promise1, promise2, promise3];
        return this.$q.all(promises).then((function(_this) {
          return function() {
            var search;
            _this.updateCriteriaOptionTypes();
            search = _this.$location.search();
            if (search && search.tab) {
              return _this.$scope.dp_tab_ids.main = search.tab;
            }
          };
        })(this));
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.isDirtyState = function() {
        return !Util.equals(this.form, this.origForm);
      };


      /**
      		 * Save everything
       */

      Admin_TicketDeps_Ctrl_Edit.prototype.saveAll = function() {
        var deferred2, promise, triggerSaver, _ref;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        if (this.depData.hasChildrenAndChangedParent(this.dep, this.form)) {
          this.showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.");
          return;
        }
        this.startSpinner('saving_dep');
        deferred2 = this.$q.defer();
        triggerSaver = (function(_this) {
          return function() {
            var act, p1, p2, postData, _, _ref, _ref1;
            if (_this.dep.has_children) {
              return;
            }
            postData = {
              actions: []
            };
            if (_this.$scope.actions_form) {
              _ref = _this.$scope.actions_form;
              for (_ in _ref) {
                if (!__hasProp.call(_ref, _)) continue;
                act = _ref[_];
                if (act.type) {
                  postData.actions.push(act);
                }
              }
            }
            p1 = _this.Api.sendPostJson('/ticket_triggers/departments/' + _this.dep.id, postData);
            postData = {
              actions: []
            };
            if (_this.$scope.actions_form2) {
              _ref1 = _this.$scope.actions_form2;
              for (_ in _ref1) {
                if (!__hasProp.call(_ref1, _)) continue;
                act = _ref1[_];
                if (act.type) {
                  postData.actions.push(act);
                }
              }
            }
            p2 = _this.Api.sendPostJson('/ticket_triggers/departments_changed/' + _this.dep.id, postData);
            return _this.$q.all([p1, p2]);
          };
        })(this);
        this.form.avatar = ((_ref = this.dep.avatar) != null ? _ref.id : void 0) || null;
        promise = this.depData.saveFormModel(this.dep, this.form);
        promise.then((function(_this) {
          return function() {
            triggerSaver();
            if (_this.dep.has_children) {
              return;
            }
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

      Admin_TicketDeps_Ctrl_Edit.prototype.setAvatar = function(blob) {
        this.dep.avatar = blob;
        if (blob == null) {
          return this.$scope.icon_image = "/web/app/vendor-src/icons/webdev-seo/png/career.png";
        } else {
          return this.$scope.icon_image = blob.thumbnail_url_50;
        }
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.onFileSelect = function(files) {
        var file;
        this.$scope.uploading = false;
        file = files[0];
        return this.$upload.upload({
          url: this.$http.formatApiUrl('/misc/upload'),
          data: {
            is_image: true
          },
          file: file
        }).success((function(_this) {
          return function(data) {
            _this.$scope.uploading = false;
            return _this.setAvatar(data.blob);
          };
        })(this)).error((function(_this) {
          return function(data) {
            _this.$scope.uploading = false;
            return _this.Growl.error((data != null ? data.error_message : void 0) || 'Error');
          };
        })(this));
      };

      Admin_TicketDeps_Ctrl_Edit.prototype.selectIcon = function(image) {
        if (image == null) {
          setAvatar(null);
        }
        this.$scope.uploading = true;
        return this.Api.sendPostJson('/misc/upload', {
          path: image,
          is_image: true
        }).then((function(_this) {
          return function(data) {
            _this.$scope.uploading = false;
            return _this.setAvatar(data.data.blob);
          };
        })(this), (function(_this) {
          return function() {
            return _this.$scope.uploading = false;
          };
        })(this));
      };

      return Admin_TicketDeps_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketDeps_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
