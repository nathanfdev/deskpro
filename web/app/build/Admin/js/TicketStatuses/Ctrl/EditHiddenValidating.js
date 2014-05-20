(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketStatuses_Ctrl_EditHiddenValidating;
    Admin_TicketStatuses_Ctrl_EditHiddenValidating = (function(_super) {
      __extends(Admin_TicketStatuses_Ctrl_EditHiddenValidating, _super);

      function Admin_TicketStatuses_Ctrl_EditHiddenValidating() {
        return Admin_TicketStatuses_Ctrl_EditHiddenValidating.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketStatuses_Ctrl_EditHiddenValidating.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenValidating';

      Admin_TicketStatuses_Ctrl_EditHiddenValidating.CTRL_AS = 'TicketStatusEdit';

      Admin_TicketStatuses_Ctrl_EditHiddenValidating.DEPS = [];

      Admin_TicketStatuses_Ctrl_EditHiddenValidating.prototype.init = function() {
        this.$scope.getCount = (function(_this) {
          return function() {
            var _ref;
            return (_ref = _this.$scope.$parent.TicketStatusesList) != null ? _ref.getStatusCount('hidden_validating') : void 0;
          };
        })(this);
      };

      return Admin_TicketStatuses_Ctrl_EditHiddenValidating;

    })(Admin_Ctrl_Base);
    return Admin_TicketStatuses_Ctrl_EditHiddenValidating.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditHiddenValidating.js.map
