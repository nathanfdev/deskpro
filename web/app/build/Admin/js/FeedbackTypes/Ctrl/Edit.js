(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackTypes_Ctrl_Edit;
    Admin_FeedbackTypes_Ctrl_Edit = (function(_super) {
      __extends(Admin_FeedbackTypes_Ctrl_Edit, _super);

      function Admin_FeedbackTypes_Ctrl_Edit() {
        return Admin_FeedbackTypes_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackTypes_Ctrl_Edit.CTRL_ID = 'Admin_FeedbackTypes_Ctrl_Edit';

      Admin_FeedbackTypes_Ctrl_Edit.CTRL_AS = 'FeedbackTypesEdit';

      Admin_FeedbackTypes_Ctrl_Edit.DEPS = ['Api', 'Growl', 'FeedbackTypesData', '$stateParams', '$modal'];

      Admin_FeedbackTypes_Ctrl_Edit.prototype.init = function() {
        this.feedback_type = {};
        this.usergroups = [];
        this.selected_usergroups = {};
      };

      Admin_FeedbackTypes_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise;
        if (!this.$stateParams.id) {

        } else {
          data_promise = this.Api.sendDataGet({
            feedback_type: '/feedback_types/' + this.$stateParams.id,
            usergroups: '/user_groups'
          }).then((function(_this) {
            return function(result) {
              var id, ids, _i, _len, _results;
              _this.feedback_type = result.data.feedback_type.feedback_type;
              _this.usergroups = result.data.usergroups.groups;
              ids = _.pluck(_this.feedback_type.usergroups, 'id');
              _results = [];
              for (_i = 0, _len = ids.length; _i < _len; _i++) {
                id = ids[_i];
                _results.push(_this.selected_usergroups[id] = true);
              }
              return _results;
            };
          })(this));
          return this.$q.all([data_promise]);
        }
      };


      /*
      			 * Saves the current form
      			 *
      			 * @return {promise}
       */

      Admin_FeedbackTypes_Ctrl_Edit.prototype.saveFeedbackType = function() {
        var is_new, key, promise, usergroup, value, _ref;
        this.feedback_type.usergroups = [];
        _ref = this.selected_usergroups;
        for (key in _ref) {
          if (!__hasProp.call(_ref, key)) continue;
          value = _ref[key];
          if (value) {
            usergroup = _.findWhere(this.usergroups, {
              id: parseInt(key)
            });
            if (usergroup) {
              this.feedback_type.usergroups.push(usergroup.id);
            }
          }
        }
        if (!this.$scope.form_props.$valid) {
          return;
        }
        this.startSpinner('saving_feedback_type');
        if (this.feedback_type.id) {
          is_new = false;
          promise = this.Api.sendPostJson('/feedback_types/' + this.feedback_type.id, {
            feedback_type: this.feedback_type
          });
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/feedback_types', {
            feedback_type: this.feedback_type
          });
        }
        promise.success((function(_this) {
          return function(result) {
            _this.feedback_type.id = result.id;
            _this.stopSpinner('saving_feedback_type', true).then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_feedback_type'));
            });
            _this.FeedbackTypesData.updateModel(_this.feedback_type);
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('portal.feedback_types.gocreate');
            } else {
              return _this.$state.go('portal.feedback_types');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_feedback_type', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };

      return Admin_FeedbackTypes_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackTypes_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
