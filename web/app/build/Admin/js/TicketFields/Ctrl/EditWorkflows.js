(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Arrays) {
    var Admin_TicketFields_Ctrl_EditWorkflows;
    Admin_TicketFields_Ctrl_EditWorkflows = (function(_super) {
      __extends(Admin_TicketFields_Ctrl_EditWorkflows, _super);

      function Admin_TicketFields_Ctrl_EditWorkflows() {
        return Admin_TicketFields_Ctrl_EditWorkflows.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFields_Ctrl_EditWorkflows.CTRL_ID = 'Admin_TicketFields_Ctrl_EditWorkflows';

      Admin_TicketFields_Ctrl_EditWorkflows.CTRL_AS = 'TicketWorks';

      Admin_TicketFields_Ctrl_EditWorkflows.DEPS = [];

      Admin_TicketFields_Ctrl_EditWorkflows.prototype.init = function() {
        this.works = [];
        this.default_id = 0;
        this.agent_required = false;
        this.user_required = false;
      };

      Admin_TicketFields_Ctrl_EditWorkflows.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'info': '/ticket_works'
        }).then((function(_this) {
          return function(res) {
            _this.works = res.data.info.workflows;
            _this.default_id = res.data.info.default_id;
            _this.agent_required = res.data.info.agent_required;
            return _this.user_required = res.data.info.user_required;
          };
        })(this));
        return data_promise;
      };

      Admin_TicketFields_Ctrl_EditWorkflows.prototype.save = function() {
        var postData, promise;
        postData = {
          priorities: this.works,
          default_id: this.default_id,
          user_required: this.user_required,
          agent_required: this.agent_required
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/ticket_works', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TicketFields_Ctrl_EditWorkflows;

    })(Admin_Ctrl_Base);
    return Admin_TicketFields_Ctrl_EditWorkflows.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditWorkflows.js.map
