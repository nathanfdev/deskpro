(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/CustomFields/FieldFormMapper'], function(BaseListEdit, FieldFormMapper) {
    var TicketFields;
    return TicketFields = (function(_super) {
      __extends(TicketFields, _super);

      function TicketFields() {
        return TicketFields.__super__.constructor.apply(this, arguments);
      }

      TicketFields.$inject = ['Api', '$q'];

      TicketFields.prototype.init = function() {
        return this.field_enabled = {
          category: true,
          priority: true,
          workflow: true,
          product: true
        };
      };

      TicketFields.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendDataGet(['/ticket_fields']).then((function(_this) {
          return function(res) {
            var custom_fields, f, _i, _j, _len, _len1, _ref, _ref1;
            _ref = ['category', 'priority', 'workflow', 'product'];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              f = _ref[_i];
              _this.field_enabled[f] = false;
              if (res.data.api_ticket_fields[f + '_enabled']) {
                _this.field_enabled[f] = true;
              }
            }
            custom_fields = [];
            _ref1 = res.data.api_ticket_fields.custom_fields;
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              f = _ref1[_j];
              custom_fields.push(f);
            }
            return deferred.resolve(custom_fields);
          };
        })(this));
        return deferred.promise;
      };


      /*
        	 * Remove a field
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      TicketFields.prototype.deleteFieldById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/ticket_fields/' + id).then((function(_this) {
          return function() {
            return _this.removeListModelById(id);
          };
        })(this));
        return promise;
      };


      /*
        	 * Get all data needed for the edit field page
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      TicketFields.prototype.loadEditFieldData = function(id) {
        var data, deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet("/ticket_fields/" + id).then((function(_this) {
            return function(result) {
              var data;
              data = {};
              data.field = result.data.field;
              data.field_type = result.data.field.type_name;
              data.form = _this.getFormMapper().getFormFromModel(data.field);
              return deferred.resolve(data);
            };
          })(this));
        } else {
          data = {
            field: {},
            field_type: '0',
            form: this.getFormMapper().getFormFromModel(null)
          };
          deferred.resolve(data);
        }
        return deferred.promise;
      };


      /*
        	 * Get the form mapper
        	 *
        	 * @return {FieldFormMapper}
       */

      TicketFields.prototype.getFormMapper = function() {
        if (this.formMapper) {
          return this.formMapper;
        }
        this.formMapper = new FieldFormMapper();
        return this.formMapper;
      };


      /*
        	 * Saves a form model and applies the form model to the field model
        	 * once finished.
        	 *
        	 * @param {Object} fieldModel The field model
        	 * @param {Object} formModel  The model representing the form
        	 * @return {promise}
       */

      TicketFields.prototype.saveFormModel = function(fieldModel, formModel) {
        var mapper, postData, promise;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(fieldModel.type_name, formModel);
        if (fieldModel.id) {
          promise = this.Api.sendPostJson('/ticket_fields/' + fieldModel.id, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_fields', postData).success(function(data) {
            return fieldModel.id = data.field_id;
          });
        }
        promise.success((function(_this) {
          return function() {
            mapper.applyFormToModel(fieldModel, formModel);
            return _this.mergeDataModel(fieldModel);
          };
        })(this));
        return promise;
      };

      return TicketFields;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=TicketFields.js.map
