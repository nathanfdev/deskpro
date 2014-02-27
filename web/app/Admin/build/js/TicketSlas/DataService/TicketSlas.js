(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_TicketFilters_DataService_TicketSlas;
    return Admin_TicketFilters_DataService_TicketSlas = (function(_super) {
      __extends(Admin_TicketFilters_DataService_TicketSlas, _super);

      function Admin_TicketFilters_DataService_TicketSlas() {
        return Admin_TicketFilters_DataService_TicketSlas.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFilters_DataService_TicketSlas.$inject = ['Api', '$q'];

      Admin_TicketFilters_DataService_TicketSlas.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_slas').success((function(_this) {
          return function(data) {
            var models;
            models = data.slas;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
        	 * Remove an slas
        	 *
        	 * @param {Integer} id SLA id
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketSlas.prototype.deleteSlaById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/ticket_slas/' + id).then((function(_this) {
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

      Admin_TicketFilters_DataService_TicketSlas.prototype.loadEditSlaData = function(id) {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_slas/' + id).then(function(result) {
          return deferred.resolve({
            sla: result.data.sla
          });
        }, function() {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return Admin_TicketFilters_DataService_TicketSlas;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=TicketSlas.js.map
