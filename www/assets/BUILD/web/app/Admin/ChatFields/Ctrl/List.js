define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ChatFields_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChatFields_Ctrl_List';
      this.CTRL_AS = 'ChatFieldsList';
      this.DEPS    = [];
    }

    init() {
      this.fieldDataService = this.DataService.get('ChatFields');
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
    }

    initialLoad() {
      const promise = this.fieldDataService.loadList();
      promise.then( list => {
        return this.custom_fields = list;
      });

      return promise;
    }
  }
  Admin_ChatFields_Ctrl_List.initClass();

  return Admin_ChatFields_Ctrl_List.EXPORT_CTRL();
});