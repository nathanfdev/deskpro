(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/TicketEscalations/EscalationEditFormMapper'], function(BaseListEdit, EscalationEditFormMapper) {
    var Admin_TicketFilters_DataService_TicketEscalations;
    return Admin_TicketFilters_DataService_TicketEscalations = (function(_super) {
      __extends(Admin_TicketFilters_DataService_TicketEscalations, _super);

      function Admin_TicketFilters_DataService_TicketEscalations() {
        return Admin_TicketFilters_DataService_TicketEscalations.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketFilters_DataService_TicketEscalations.$inject = ['Api', '$q'];

      Admin_TicketFilters_DataService_TicketEscalations.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet('/ticket_escalations').success((function(_this) {
          return function(data) {
            var models;
            models = data.escalations;
            return deferred.resolve(models);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };


      /*
        	 * Save order of escalations
        	 *
        	 * @param {Array} orders Array of IDs, in order
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketEscalations.prototype.saveRunOrder = function(orders) {
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
        	 * Remove a filter
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketEscalations.prototype.deleteEscalationById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/ticket_escalations/' + id).then((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
        	 * Get the form mapper
        	 *
        	 * @return {EscalationEditFormMapper}
       */

      Admin_TicketFilters_DataService_TicketEscalations.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new EscalationEditFormMapper();
        return this.formMapper;
      };


      /*
        	 * Get all data needed for the edit filter page
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketEscalations.prototype.loadEditEscalationData = function(id) {
        var data, deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet('/ticket_escalations/' + id).then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.escalation = result.data.escalation;
              data.form = _this.getFormMapper().getFormFromModel(data.escalation);
              return deferred.resolve(data);
            };
          })(this), function() {
            return deferred.reject();
          });
        } else {
          data = {};
          data.escalation = {
            id: null,
            title: ''
          };
          data.form = this.getFormMapper().getFormFromModel(data.escalation);
          deferred.resolve(data);
        }
        return deferred.promise;
      };


      /*
        	 * Saves a form model and applies the form model to the macro model
        	 * once finished.
        	 *
        	 * @param {Object} escModel The esc model
        	 * @param {Object} formModel  The model representing the form
        	 * @return {promise}
       */

      Admin_TicketFilters_DataService_TicketEscalations.prototype.saveFormModel = function(escModel, formModel) {
        var mapper, postData, promise;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(formModel);
        if (escModel.id) {
          promise = this.Api.sendPostJson('/ticket_escalations/' + escModel.id, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_escalations', postData).success(function(data) {
            return escModel.id = data.escalation_id;
          });
        }
        promise.success((function(_this) {
          return function() {
            mapper.applyFormToModel(escModel, formModel);
            return _this.mergeDataModel(escModel);
          };
        })(this));
        return promise;
      };

      return Admin_TicketFilters_DataService_TicketEscalations;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=TicketEscalations.js.map
