(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketTriggers_DataService_BaseTriggers, _ref;
    return Admin_TicketTriggers_DataService_BaseTriggers = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_BaseTriggers, _super);

      function Admin_TicketTriggers_DataService_BaseTriggers() {
        _ref = Admin_TicketTriggers_DataService_BaseTriggers.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketTriggers_DataService_BaseTriggers.$inject = ['Api', '$q'];

      Admin_TicketTriggers_DataService_BaseTriggers.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_triggers/' + this.type).success(function(data) {
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


      Admin_TicketTriggers_DataService_BaseTriggers.prototype.deleteSlaById = function(id) {
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


      Admin_TicketTriggers_DataService_BaseTriggers.prototype.loadEditTriggerData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_triggers/' + id).then(function(result) {
          return deferred.resolve({
            trigger: result.data.trigger
          });
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Save the enabled state of a trigger
        	#
        	# @param {Integer} triggerId
        	# @param {bool} isEnabled
        	# @return {promise}
      */


      Admin_TicketTriggers_DataService_BaseTriggers.prototype.saveEnabledStateById = function(triggerId, isEnabled) {
        if (isEnabled) {
          return this.Api.sendPost("/ticket_triggers/triggerId/enable");
        } else {
          return this.Api.sendPost("/ticket_triggers/triggerId/disable");
        }
      };

      /*
        	# Deletes a trigger
        	#
        	# @param {Integer} triggerId
        	# @return {promise}
      */


      Admin_TicketTriggers_DataService_BaseTriggers.prototype.deleteTriggerById = function(triggerId) {
        return this.Api.sendDelete('/ticket_triggers/' + triggerId);
      };

      /*
        	# Save order of triggers
        	#
        	# @param {Array} orders Array of IDs, in order
        	# @return {promise}
      */


      Admin_TicketTriggers_DataService_BaseTriggers.prototype.saveRunOrder = function(orders) {
        var id, idx, model, promise, _i, _len;
        for (idx = _i = 0, _len = orders.length; _i < _len; idx = ++_i) {
          id = orders[idx];
          model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }
        promise = this.Api.sendPostJson('/ticket_triggers/run_order', {
          run_orders: orders
        });
        return promise;
      };

      return Admin_TicketTriggers_DataService_BaseTriggers;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=BaseTriggers.js.map
*/