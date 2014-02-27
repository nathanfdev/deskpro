(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketFilters_DataService_TicketFilters;
    return Admin_TicketFilters_DataService_TicketFilters = (function(_super) {
      __extends(Admin_TicketFilters_DataService_TicketFilters, _super);

      function Admin_TicketFilters_DataService_TicketFilters() {
        return Admin_TicketFilters_DataService_TicketFilters.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFilters_DataService_TicketFilters.$inject = ['Api', '$q'];

      Admin_TicketFilters_DataService_TicketFilters.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_filters').success((function(_this) {
          return function(data) {
            var models;
            models = data.filters;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
        	 * Save order of filters
        	 *
        	 * @param {Array} orders Array of IDs, in order
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketFilters.prototype.saveDisplayOrder = function(orders) {
        var id, idx, model, promise, _i, _len;
        for (idx = _i = 0, _len = orders.length; _i < _len; idx = ++_i) {
          id = orders[idx];
          model = this.findListModelById(id);
          if (model) {
            model.display_order = idx;
          }
        }
        promise = this.Api.sendPostJson('/ticket_filters/display_order', {
          display_order: orders
        });
        return promise;
      };


      /*
        	 * Remove a filter
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketFilters.prototype.deleteFilterId = function(id) {
        var promise;
        promise = this.Api.sendDelete('/ticket_filters/' + id).then((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
        	 * Get all data needed for the edit filter page
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketFilters.prototype.loadEditFilterData = function(id) {
        var deferred, types;
        deferred = this.$q.defer();
        types = {};
        if (id) {
          types.filter = '/ticket_filters/' + id;
        }
        types.agents = '/agents';
        types.teams = '/agent_teams';
        this.Api.sendDataGet(types).then(function(res) {
          var data;
          data = {};
          if (res.data.filter) {
            data.filter = res.data.filter.filter;
          }
          data.agents = res.data.agents.agents;
          data.teams = res.data.teams.agent_teams;
          return deferred.resolve(data);
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return Admin_TicketFilters_DataService_TicketFilters;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=TicketFilters.js.map
