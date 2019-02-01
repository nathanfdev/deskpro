define([
  'Admin/Main/DataService/BaseListEdit',
], function(
  BaseListEdit
)  {
  class Admin_CustomFields_DataService_CustomFields extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }



    init(owner, context) {
      return this._params = {owner: owner || null, context: context || null};
    }



    url() {
      return '/custom_fields';
    }



    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api.sendGet(this.url(), this._params).then(res => deferred.resolve(res.data || []));

      return deferred.promise;
    }



    /*
     * Update display orders
     *
     * @param {Array} Array of IDs in order
     * @return {promise}
     */
    saveDisplayOrder(display_orders) {
      return this.Api.sendPostJson('/custom_fields/display-order', {display_orders});
    }
  }
  Admin_CustomFields_DataService_CustomFields.initClass();
  return Admin_CustomFields_DataService_CustomFields;
});

