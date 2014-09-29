(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketTriggers_DataService_BaseTriggers;
    return Admin_TicketTriggers_DataService_BaseTriggers = (function(_super) {
      __extends(Admin_TicketTriggers_DataService_BaseTriggers, _super);

      function Admin_TicketTriggers_DataService_BaseTriggers() {
        return Admin_TicketTriggers_DataService_BaseTriggers.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_DataService_BaseTriggers.$inject = ['Api', '$q'];

      Admin_TicketTriggers_DataService_BaseTriggers.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_triggers/' + this.type).success((function(_this) {
          return function(data) {
            var models;
            models = data.triggers;
            _this.department_triggers_enabled = data.department_triggers_enabled || false;
            _this.emailaccount_triggers_enabled = data.emailaccount_triggers_enabled || false;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
        	 * Get all data needed for the edit filter page
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
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
        	 * Save the enabled state of a trigger
        	 *
        	 * @param {Integer} triggerId
        	 * @param {bool} isEnabled
        	 * @return {promise}
       */

      Admin_TicketTriggers_DataService_BaseTriggers.prototype.saveEnabledStateById = function(triggerId, isEnabled) {
        if (isEnabled) {
          return this.Api.sendPost("/ticket_triggers/" + triggerId + "/enable");
        } else {
          return this.Api.sendPost("/ticket_triggers/" + triggerId + "/disable");
        }
      };


      /*
        	 * Deletes a trigger
        	 *
        	 * @param {Integer} triggerId
        	 * @return {promise}
       */

      Admin_TicketTriggers_DataService_BaseTriggers.prototype.deleteTriggerById = function(triggerId) {
        this.removeListModelById(triggerId);
        return this.Api.sendDelete('/ticket_triggers/' + triggerId);
      };


      /*
        	 * Save order of triggers
        	 *
        	 * @param {Array} orders Array of IDs, in order
        	 * @return {promise}
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


      /*
        	 * Save the enabled state of a trigger
        	 *
        	 * @param {Integer} triggerId
        	 * @param {bool} isEnabled
        	 * @return {promise}
       */

      Admin_TicketTriggers_DataService_BaseTriggers.prototype.saveGroupEnabledState = function(type, isEnabled) {
        var promise, verb;
        verb = isEnabled ? 'enable' : 'disable';
        if (type === 'departments' && this.type === 'newticket') {
          promise = this.Api.sendPost("/ticket_triggers/departments/" + verb);
          this.department_triggers_enabled = isEnabled;
        }
        if (type === 'departments' && this.type === 'update') {
          promise = this.Api.sendPost("/ticket_triggers/departments_changed/" + verb);
          this.department_triggers_enabled = isEnabled;
        }
        if (type === 'email_accounts' && this.type === 'newticket') {
          promise = this.Api.sendPost("/ticket_triggers/email_accounts/" + verb);
          this.emailaccount_triggers_enabled = isEnabled;
        }
        return promise;
      };

      return Admin_TicketTriggers_DataService_BaseTriggers;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=BaseTriggers.js.map
