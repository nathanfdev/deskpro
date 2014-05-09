(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackStatuses_Ctrl_Edit;
    Admin_FeedbackStatuses_Ctrl_Edit = (function(_super) {
      __extends(Admin_FeedbackStatuses_Ctrl_Edit, _super);

      function Admin_FeedbackStatuses_Ctrl_Edit() {
        return Admin_FeedbackStatuses_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_FeedbackStatuses_Ctrl_Edit.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_Edit';

      Admin_FeedbackStatuses_Ctrl_Edit.CTRL_AS = 'FeedbackStatusesEdit';

      Admin_FeedbackStatuses_Ctrl_Edit.DEPS = ['Api', 'Growl', 'FeedbackStatusesData', '$stateParams', '$modal'];

      Admin_FeedbackStatuses_Ctrl_Edit.prototype.init = function() {
        this.feedback_status = {};
        this.statusType = this.$stateParams.type;
        if (this.statusType) {
          this.feedback_status.status_type = this.statusType;
        }
      };

      Admin_FeedbackStatuses_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise;
        if (!this.$stateParams.id) {

        } else {
          data_promise = this.Api.sendGet('/feedback_statuses/' + this.$stateParams.id).then((function(_this) {
            return function(result) {
              return _this.feedback_status = result.data.feedback_status;
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

      Admin_FeedbackStatuses_Ctrl_Edit.prototype.saveFeedbackStatus = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        this.startSpinner('saving_feedback_status');
        if (this.feedback_status.id) {
          is_new = false;
          promise = this.Api.sendPostJson('/feedback_statuses/' + this.feedback_status.id, {
            feedback_status: this.feedback_status
          });
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/feedback_statuses', {
            feedback_status: this.feedback_status
          });
        }
        promise.success((function(_this) {
          return function(result) {
            _this.feedback_status.id = result.id;
            _this.stopSpinner('saving_feedback_status', true).then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_feedback_status'));
            });
            _this.FeedbackStatusesData.updateModel(_this.feedback_status);
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('portal.feedback_statuses.gocreate', {
                type: _this.statusType
              });
            } else {
              return _this.$state.go('portal.feedback_statuses');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_feedback_status', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };

      return Admin_FeedbackStatuses_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackStatuses_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
