(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditHiddenSpam, _ref;
    Admin_TicketStatuses_Ctrl_EditHiddenSpam = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditHiddenSpam, _super);

      function Admin_TicketStatuses_Ctrl_EditHiddenSpam() {
        _ref = Admin_TicketStatuses_Ctrl_EditHiddenSpam.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenSpam';

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.CTRL_TYPE = 'page';

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.init = function() {
        this.auto_purge_time = 604800;
      };

      Admin_TicketStatuses_Ctrl_EditHiddenSpam.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet("/ticket_statuses/spam").success(function(data) {
          return _this.auto_purge_time = data.spam_info.auto_purge_time;
        });
        return promise;
      };

      return Admin_TicketStatuses_Ctrl_EditHiddenSpam;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditHiddenSpam.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EditHiddenSpam.js.map
*/