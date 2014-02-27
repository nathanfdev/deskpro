(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/TicketTriggers/DataService/BaseTriggers'], function(BaseTriggers) {
    var Admin_TicketTriggers_DataService_TriggersNew;
    return Admin_TicketTriggers_DataService_TriggersNew = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_TriggersNew, _super);

      function Admin_TicketTriggers_DataService_TriggersNew() {
        return Admin_TicketTriggers_DataService_TriggersNew.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_DataService_TriggersNew.$inject = ['Api', '$q'];

      Admin_TicketTriggers_DataService_TriggersNew.prototype.init = function() {
        return this.type = 'newticket';
      };

      return Admin_TicketTriggers_DataService_TriggersNew;

    })(BaseTriggers);
  });

}).call(this);

//# sourceMappingURL=TriggersNew.js.map
