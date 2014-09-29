(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix, Util) {
    var Admin_ChatDeps_Ctrl_Edit;
    Admin_ChatDeps_Ctrl_Edit = (function(_super) {
      __extends(Admin_ChatDeps_Ctrl_Edit, _super);

      function Admin_ChatDeps_Ctrl_Edit() {
        this.selectIcon = __bind(this.selectIcon, this);
        this.setAvatar = __bind(this.setAvatar, this);
        return Admin_ChatDeps_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_ChatDeps_Ctrl_Edit.CTRL_ID = 'Admin_ChatDeps_Ctrl_Edit';

      Admin_ChatDeps_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_ChatDeps_Ctrl_Edit.DEPS = ['$upload', '$http'];


      /*
       	 *
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.init = function() {
        this.depData = this.DataService.get('ChatDeps');
        this.$scope.icon_image = null;
        this.$scope.$on('icon.selected', (function(_this) {
          return function(e, path) {
            return _this.selectIcon(path);
          };
        })(this));
        return this.initializeScopeWatching();
      };


      /*
      		 *
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.resetForm = function() {
        return this.form = Util.clone(this.origForm, true);
      };


      /*
       	 * Loads data that will be used inside the view into scope of controller
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.depData.getEditDepartmentData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.dep = data.dep;
            _this.form = data.form;
            _this.origForm = Util.clone(_this.form, true);
            _this.usergroups = data.usergroups;
            _this.agentgroups = data.agentgroups;
            _this.agents = data.agents;
            _this.dep_parent_list = data.dep_parent_list;
            return _this.setAvatar(_this.dep.avatar);
          };
        })(this));
        return promise;
      };


      /*
       	 * Checks whether data entered to form was changed or not
       * This is useful for showing modal dialog that notifies user that he has edited the form
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.isDirtyState = function() {
        return !Util.equals(this.form, this.origForm);
      };


      /*
      		 * Saves the form
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.saveAll = function() {
        var is_new, promise, _ref;
        is_new = !this.dep.id;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        if (this.depData.hasChildrenAndChangedParent(this.dep, this.form)) {
          this.showAlert("You cannot change parent of this department as it has sub-departments. Move or delete the sub-departments first.");
          return;
        }
        this.startSpinner('saving_dep');
        this.form.avatar = ((_ref = this.dep.avatar) != null ? _ref.id : void 0) || null;
        promise = this.depData.saveFormModel(this.dep, this.form);
        promise.success((function(_this) {
          return function() {
            _this.origForm = Util.clone(_this.form, true);
            _this.stopSpinner('saving_dep', true).then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_dep'));
            });
            if (is_new) {
              return _this.$state.go('chat.chat_deps.gocreate');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_dep');
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };


      /*
       	 * Watches for changing of parent_id that is in scope
       * This is usable inside view to show some notification information
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.initializeScopeWatching = function() {
        return this.$scope.$watch('EditCtrl.form.parent_id', (function(_this) {
          return function(newVal) {
            var parent;
            newVal = parseInt(newVal);
            if (!newVal) {
              _this.$scope.show_parent_warning = false;
              return;
            }
            parent = _this.depData.findListModelById(newVal);
            if (parent && parent.children && !parent.children.length) {
              return _this.$scope.show_parent_warning = parent;
            } else {
              return _this.$scope.show_parent_warning = false;
            }
          };
        })(this));
      };


      /*
       	 *
       */

      Admin_ChatDeps_Ctrl_Edit.prototype.propogatePermission = function(obj, perm) {
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

      Admin_ChatDeps_Ctrl_Edit.prototype.setAvatar = function(blob) {
        this.dep.avatar = blob;
        if (blob == null) {
          return this.$scope.icon_image = "/web/app/vendor-src/icons/webdev-seo/png/career.png";
        } else {
          return this.$scope.icon_image = blob.thumbnail_url_50;
        }
      };

      Admin_ChatDeps_Ctrl_Edit.prototype.onFileSelect = function(files) {
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

      Admin_ChatDeps_Ctrl_Edit.prototype.selectIcon = function(image) {
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

      return Admin_ChatDeps_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_ChatDeps_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
