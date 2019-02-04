define([
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
) => {
  class Usersources extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    init() {}

    /*
     * Update display orders
     *
     * @param {Array} Array of IDs in order
     * @return {promise}
     */
    saveDisplayOrder(display_orders) {
      return this.Api.sendPostJson('/usersources/display-order', { display_orders });
    }
  }
  Usersources.initClass();
  return Usersources;
});
