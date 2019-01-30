// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_OrgFields_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_OrgFields_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS    = [];
    }

    init() {
      this.fieldDataService = this.DataService.get('OrgFields');
      this.custom_fields = [];
      this.sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const displayOrders = [];

          $list.find('li').each(function() {
            return displayOrders.push(parseInt($(this).data('id')));
          });

          this.fieldDataService.saveDisplayOrder(displayOrders);
          return this.pingElement('display_orders');
        }
      };

      this.specific_org_custom_fields = [];
      const { data } = this.$state.current;
      this.service = this.DataService.get('CustomFields', data.owner, data.context);
      this.customFieldListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const displayOrders = [];

          $list.find('li').each(function() { return displayOrders.push(parseInt($(this).data('id'))); });

          this.service.saveDisplayOrder(displayOrders);
          return this.pingElement('display_orders');
        }
      };

    }

    initialLoad() {
      const promise = this.fieldDataService.loadList();
      promise.then( list => {
        return this.custom_fields = list;
      });
      this.service.all().then(list => { return this.specific_org_custom_fields = list; });

      return promise;
    }
  }
  Admin_OrgFields_Ctrl_List.initClass();

  return Admin_OrgFields_Ctrl_List.EXPORT_CTRL();
});