define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketBilling_Ctrl_Fields extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketBilling_Ctrl_Fields';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS    = [];
    }


    init() {
      this.fieldDataService = this.DataService.get('BillingFields');
      this.custom_fields = [];
      this.sortedListOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const displayOrders = [];

          $list.find('li').each(function () {
            return displayOrders.push(parseInt($(this).data('id')));
          });

          this.fieldDataService.saveDisplayOrder(displayOrders);
          return this.pingElement('display_orders');
        }
      };

      const { data } = this.$state.current;
      this.customFieldListOptions = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const displayOrders = [];

          $list.find('li').each(function () { return displayOrders.push(parseInt($(this).data('id'))); });

          this.service.saveDisplayOrder(displayOrders);
          return this.pingElement('display_orders');
        }
      };
    }


    initialLoad() {
      const promise = this.fieldDataService.loadList();
      promise.then(list => this.custom_fields = list);

      return promise;
    }
  }
  Admin_TicketBilling_Ctrl_Fields.initClass();


  return Admin_TicketBilling_Ctrl_Fields.EXPORT_CTRL();
});
