(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketFilters_DataService_TicketMacros, _ref;
    return Admin_TicketFilters_DataService_TicketMacros = (function(_super) {
      __extends(Admin_TicketFilters_DataService_TicketMacros, _super);

      function Admin_TicketFilters_DataService_TicketMacros() {
        _ref = Admin_TicketFilters_DataService_TicketMacros.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_DataService_TicketMacros.$inject = ['Api', '$q'];

      Admin_TicketFilters_DataService_TicketMacros.prototype._doLoadList = function() {
        var deferred,
          _this = this;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_escalations').success(function(data) {
          var models;
          models = data.escalations;
          return deferred.resolve(models);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      /*
        	# Save order of escalations
        	#
        	# @param {Array} orders Array of IDs, in order
        	# @return {promise}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.saveRunOrder = function(orders) {
        var id, idx, model, promise, _i, _len;
        for (idx = _i = 0, _len = orders.length; _i < _len; idx = ++_i) {
          id = orders[idx];
          model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }
        promise = this.Api.sendPostJson('/ticket_escalations/run_order', {
          display_order: orders
        });
        return promise;
      };

      /*
        	# Remove a filter
        	#
        	# @param {Integer} id Filter id
        	# @return {promise}
      */


      Admin_TicketFilters_DataService_TicketMacros.prototype.deleteEscalationById = function(id) {
        var promise,
          _this = this;
        promise = this.Api.sendDelete('/ticket_escalations/' + id).then(function() {
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


      Admin_TicketFilters_DataService_TicketMacros.prototype.loadEditEscalationData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_escalations/' + id).then(function(result) {
          return deferred.resolve({
            filter: result.data.filter
          });
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return Admin_TicketFilters_DataService_TicketMacros;

    })(BaseListEdit);
  });

}).call(this);

/*
//@ sourceMappingURL=TicketMacros.js.map
*/