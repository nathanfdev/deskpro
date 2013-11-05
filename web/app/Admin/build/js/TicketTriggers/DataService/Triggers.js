(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketTriggers_DataService_Triggers, _ref;
    return Admin_TicketTriggers_DataService_Triggers = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_Triggers, _super);

      function Admin_TicketTriggers_DataService_Triggers() {
        _ref = Admin_TicketTriggers_DataService_Triggers.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketTriggers_DataService_Triggers.$inject = ['Api', '$q'];

      Admin_TicketTriggers_DataService_Triggers.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_triggers').success(function(data) {
          var models;
          models = data.triggers;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Remove an slas
        	#
        	# @param {Integer} id SLA id
        	# @return {promise}
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.deleteSlaById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/ticket_slas/' + id).then(function() {
          return _this.removeListModelById(id);
        });
        return promise;
      };

      /*
        	# Get all data needed for the edit filter page
        	#
        	# @param {Integer} id Filter id
        	# @return {promise}
      */


      Admin_TicketTriggers_DataService_Triggers.prototype.loadEditTriggerData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_triggers/' + id).then(function(result) {
          return deferred.resolve({
            trigger: result.data.filter
          });
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return Admin_TicketTriggers_DataService_Triggers;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=Triggers.js.map
*/