(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit', 'Admin/CustomFields/FieldFormMapper'], function(BaseListEdit, FieldFormMapper) {
    var OrgFields;
    return OrgFields = (function(_super) {
      __extends(OrgFields, _super);

      function OrgFields() {
        return OrgFields.__super__.constructor.apply(this, arguments);
      }

      OrgFields.$inject = ['Api', '$q'];

      OrgFields.prototype.init = function() {};

      OrgFields.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendDataGet(['/org_fields']).then((function(_this) {
          return function(res) {
            var custom_fields, f, _i, _len, _ref;
            custom_fields = [];
            _ref = res.data.api_org_fields.custom_fields;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              f = _ref[_i];
              custom_fields.push(f);
            }
            return deferred.resolve(custom_fields);
          };
        })(this));
        return deferred.promise;
      };


      /*
      		 * Update display orders
      		 *
      		 * @param {Array} Array of IDs in order
      		 * @return {promise}
       */

      OrgFields.prototype.saveDisplayOrder = function(display_orders) {
        return this.Api.sendPostJson('/org_fields/display-order', {
          display_orders: display_orders
        });
      };


      /*
        	 * Remove a field
        	 *
        	 * @param {Integer} id Filter id
        	 * @return {promise}
       */

      OrgFields.prototype.deleteFieldById = function(id) {
        var promise;
        promise = this.Api.sendDelete('/org_fields/' + id).then((function(_this) {
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

      OrgFields.prototype.loadEditFieldData = function(id) {
        var data, deferred;
        deferred = this.$q.defer();
        if (id) {
          this.Api.sendGet("/org_fields/" + id).then((function(_this) {
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

      OrgFields.prototype.getFormMapper = function() {
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

      OrgFields.prototype.saveFormModel = function(fieldModel, formModel) {
        var mapper, postData, promise;
        mapper = this.getFormMapper();
        postData = mapper.getPostDataFromForm(fieldModel.type_name, formModel);
        if (fieldModel.id) {
          promise = this.Api.sendPostJson('/org_fields/' + fieldModel.id, postData);
        } else {
          promise = this.Api.sendPutJson('/org_fields', postData).success(function(data) {
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

      return OrgFields;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=OrgFields.js.map
