(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_FeedbackStatuses_Ctrl_Edit, _ref;
    Admin_FeedbackStatuses_Ctrl_Edit = (function(_super) {
      __extends(Admin_FeedbackStatuses_Ctrl_Edit, _super);

      function Admin_FeedbackStatuses_Ctrl_Edit() {
        _ref = Admin_FeedbackStatuses_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_FeedbackStatuses_Ctrl_Edit.CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_Edit';

      Admin_FeedbackStatuses_Ctrl_Edit.CTRL_AS = 'FeedbackStatusesEdit';

      Admin_FeedbackStatuses_Ctrl_Edit.DEPS = ['Api', 'Growl', 'FeedbackStatusesData', '$stateParams', '$modal'];

      Admin_FeedbackStatuses_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_FeedbackStatuses_Ctrl_Edit.prototype.init = function() {
        this.feedback_status = {};
      };

      Admin_FeedbackStatuses_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        if (!this.$stateParams.id) {

        } else {
          data_promise = this.Api.sendGet('/feedback_statuses/' + this.$stateParams.id).then(function(result) {
            return _this.feedback_status = result.data.feedback_status;
          });
          return this.$q.all([data_promise]);
        }
      };

      /*
      			# Saves the current form
      			#
      			# @return {promise}
      */


      Admin_FeedbackStatuses_Ctrl_Edit.prototype.saveFeedbackStatus = function() {
        var is_new,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        this.startSpinner('saving_feedback_status');
        if (this.feedback_status.id) {
          is_new = false;
        } else {
          is_new = true;
        }
        this.stopSpinner('saving_feedback_status', true).then(function() {
          return _this.Growl.success(_this.getRegisteredMessage('saved_feedback_status'));
        });
        this.FeedbackStatusesData.updateModel(this.feedback_status);
        this.skipDirtyState();
        if (is_new) {
          this.$state.go('portal.feedback_statuses.gocreate');
        } else {
          this.$state.go('portal.feedback_statuses');
        }
      };

      return Admin_FeedbackStatuses_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_FeedbackStatuses_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/