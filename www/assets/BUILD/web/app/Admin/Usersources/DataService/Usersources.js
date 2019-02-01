// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit
) {
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
      return this.Api.sendPostJson('/usersources/display-order', {display_orders});
    }
  }
  Usersources.initClass();
  return Usersources;
});
