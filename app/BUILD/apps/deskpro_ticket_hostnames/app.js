define([
  'deskpro_ticket_hostnames/Ticket/PropsCtrl',
  'deskpro_ticket_hostnames/Ticket/TicketHandler'
], function (PropsCtrl, TicketHandler) {
  return {
    init: function () {
      if (this.getSetting('rdns_ticket_showprops')) {
        this.registerWidget('ticket', '@properties.after', 'Ticket/props.html', PropsCtrl);
      }

      this.register('ticket', TicketHandler);
    }
  }
});