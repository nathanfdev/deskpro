(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/TicketTriggers/DataService/BaseTriggers'], function(BaseTriggers) {
    var Admin_TicketTriggers_DataService_TriggersUpdate;
    return Admin_TicketTriggers_DataService_TriggersUpdate = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_TriggersUpdate, _super);

      function Admin_TicketTriggers_DataService_TriggersUpdate() {
        return Admin_TicketTriggers_DataService_TriggersUpdate.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_DataService_TriggersUpdate.$inject = ['Api', '$q'];

      Admin_TicketTriggers_DataService_TriggersUpdate.prototype.init = function() {
        return this.type = 'update';
      };

      return Admin_TicketTriggers_DataService_TriggersUpdate;

    })(BaseTriggers);
  });

}).call(this);

//# sourceMappingURL=TriggersUpdate.js.map
