(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_CustomFields_DataService_CustomFields;
    return Admin_CustomFields_DataService_CustomFields = (function(_super) {
      __extends(Admin_CustomFields_DataService_CustomFields, _super);

      function Admin_CustomFields_DataService_CustomFields() {
        return Admin_CustomFields_DataService_CustomFields.__super__.constructor.apply(this, arguments);
      }

      Admin_CustomFields_DataService_CustomFields.$inject = ['Api', '$q'];

      Admin_CustomFields_DataService_CustomFields.prototype.init = function(owner, context) {
        return this._params = {
          owner: owner || null,
          context: context || null
        };
      };

      Admin_CustomFields_DataService_CustomFields.prototype.url = function() {
        return '/custom_fields';
      };

      Admin_CustomFields_DataService_CustomFields.prototype._doLoadList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet(this.url(), this._params).then(function(res) {
          return deferred.resolve(res.data || []);
        });
        return deferred.promise;
      };


      /*
      		 * Update display orders
      		 *
      		 * @param {Array} Array of IDs in order
      		 * @return {promise}
       */

      Admin_CustomFields_DataService_CustomFields.prototype.saveDisplayOrder = function(display_orders) {
        return this.Api.sendPostJson('/custom_fields/display-order', {
          display_orders: display_orders
        });
      };

      return Admin_CustomFields_DataService_CustomFields;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=CustomFields.js.map
